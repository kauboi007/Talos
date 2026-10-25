#code formatted by gemini - changes were reviewed and pushed

import os
import time
import uuid
from contextlib import asynccontextmanager
from fastapi import Body, FastAPI, HTTPException, Response
from neo4j import GraphDatabase
from neo4j.exceptions import ConstraintError
from pydantic import BaseModel
from redis import Redis

from high_level_cbs import cbs
from pathfinding import assess_threat
from data_loader import get_subgraph, get_threats, get_coords

URI = os.getenv("NEO4J_URI", "neo4j://localhost:7687")
USER = os.getenv("NEO4J_USER", "neo4j")
PASSWORD = os.getenv("NEO4J_PASSWORD", "talossys")
driver = GraphDatabase.driver(URI, auth=(USER, PASSWORD))


REDIS_HOST = os.getenv("REDIS_HOST", "localhost")
REDIS_PORT = int(os.getenv("REDIS_PORT", "6379"))
REDIS_PASSWORD = os.getenv("REDIS_PASSWORD") or None
REDIS_DB = int(os.getenv("REDIS_DB", "0"))
REDIS_REQUEST_STREAM = os.getenv("CBS_REQUEST_STREAM", "talos:cbs:requests")
REDIS_RESPONSE_STREAM = os.getenv("CBS_RESPONSE_STREAM", "talos:cbs:responses")
REDIS_TIMEOUT = float(os.getenv("CBS_REDIS_TIMEOUT", "10"))
redis_client = Redis(
    host=REDIS_HOST,
    port=REDIS_PORT,
    password=REDIS_PASSWORD,
    db=REDIS_DB,
    decode_responses=True,
)

RESOURCES = { #redid regarding the seed.cql file
    "officers": {
        "label": "soldier",
        "id_field": "service_id",
        "fields": ["service_id", "status"],
        "required": ["service_id"],
        "rels": {
            "reports_to_id": ("reports_to", "soldier", "out"),
            "stationed_at_id": ("stationed_at", "checkpoint", "out"),
            "troop_id": ("belongs_to", "troop", "out"),
        },
    },
    "troops": {
        "label": "troop",
        "id_field": "name",
        "fields": ["name", "status", "type", "head_count", "priority"],
        "required": ["name"],
        "rels": {
            "start_id": ("starts_at", "checkpoint", "out"),
            "end_id": ("ends_at", "checkpoint", "out"),
            "escort_id": ("escorts", "soldier", "out"),
        },
    },
    "checkpoints": {
        "label": "checkpoint",
        "id_field": "checkpoint_code",
        "fields": ["name", "checkpoint_code", "status", "lat", "lng", "type"],
        "required": ["checkpoint_code", "name", "lat", "lng"],
        "rels": {},
        "extra": "threat_count: count{match(n)<-[:affects]-(:threatevent)}",
    },
    "threat-events": {
        "label": "threatevent",
        "id_field": "name",
        "fields": ["name", "type", "severity"],
        "required": ["name", "type", "severity"],
        "rels": {"affects_id": ("affects", "checkpoint", "out")},
        "stamp": True,
        "extra": "timestamp: toString(n.timestamp)",
    },
}


@asynccontextmanager
async def lifespan(app: FastAPI):
    with driver.session() as s:
        for res in RESOURCES.values():
            try:
                s.run(
                    f"CREATE CONSTRAINT {res['label']}_{res['id_field']}_unique IF NOT EXISTS "
                    f"FOR (n:{res['label']}) REQUIRE n.{res['id_field']} IS UNIQUE"
                )
            except Exception:
                pass
    yield
    driver.close()


app = FastAPI(title="talos", lifespan=lifespan)


def write(fn):
    try:
        with driver.session() as s:
            return s.execute_write(fn)
    except ConstraintError:
        raise HTTPException(409, "Duplicate value")


def read(fn):
    with driver.session() as s:
        return s.execute_read(fn)


def projection(cfg):
    parts = [".*"]
    for key, (rel, tl, d) in cfg["rels"].items():
        pat = f"(n)-[:{rel}]->(x:{tl})" if d == "out" else f"(n)<-[:{rel}]-(x:{tl})"
        target_id_field = next(
            resource["id_field"]
            for resource in RESOURCES.values()
            if resource["label"] == tl
        )
        parts.append(f"{key}: [{pat} | x.{target_id_field}][0]")
    if cfg.get("extra"):
        parts.append(cfg["extra"])
    return "n{" + ", ".join(parts) + "}"


def set_rels(tx, cfg, node_id, body):
    label = cfg["label"]
    id_field = cfg["id_field"]
    for key, (rel, tl, d) in cfg["rels"].items():
        if key not in body:
            continue
        left, right = ("-", "->") if d == "out" else ("<-", "-")
        tx.run(
            f"MATCH (n:{label} {{{id_field}:$id}}){left}[r:{rel}]{right}(:{tl}) DELETE r",
            id=node_id,
        )
        tid = body[key]
        if tid is None:
            continue
        target_id_field = next(
            resource["id_field"]
            for resource in RESOURCES.values()
            if resource["label"] == tl
        )
        rec = tx.run(
            f"MATCH (n:{label} {{{id_field}:$id}}), (t:{tl} {{{target_id_field}:$tid}}) "
            f"CREATE (n){left}[:{rel}]{right}(t) RETURN t.{target_id_field} AS tid",
            id=node_id,
            tid=tid,
        ).single()
        if rec is None:
            raise HTTPException(404, f"{key}: no {tl} with id {tid}")


def fetch(tx, cfg, id_field, node_id):
    rec = tx.run(
        f"MATCH (n:{cfg['label']} {{{id_field}:$id}}) RETURN {projection(cfg)} AS item",
        id=node_id,
    ).single()
    return rec["item"] if rec else None


def fetch_troop_speed_from_laravel(vehicle_id):
    correlation_id = str(uuid.uuid4())
    try:
        latest_response = redis_client.xrevrange(
            REDIS_RESPONSE_STREAM,
            count=1,
        )
        response_cursor = latest_response[0][0] if latest_response else "0-0"
        redis_client.xadd(
            REDIS_REQUEST_STREAM,
            {
                "correlation_id": correlation_id,
                "vehicle_id": vehicle_id,
            },
        )
        deadline = time.monotonic() + REDIS_TIMEOUT
        stream_id = response_cursor

        while time.monotonic() < deadline:
            remaining_ms = max(1, int((deadline - time.monotonic()) * 1000))
            result = redis_client.xread(
                {REDIS_RESPONSE_STREAM: stream_id},
                count=20,
                block=min(remaining_ms, 1000),
            )

            for _, entries in result:
                for response_id, fields in entries:
                    stream_id = response_id
                    if fields.get("correlation_id") != correlation_id:
                        continue
                    if fields.get("error"):
                        raise HTTPException(503, fields["error"])
                    speed = float(fields["speed_kmh"])
                    if speed <= 0:
                        raise HTTPException(503, "Laravel returned an invalid vehicle speed.")
                    return speed
        raise HTTPException(503, "Timed out waiting for Laravel vehicle speed.")
    except HTTPException:
        raise
    except Exception as exception:
        raise HTTPException(503, f"Vehicle speed service unavailable: {exception}") from exception


def fetch_all_troop_speeds_from_laravel():
    speeds = {}
    for troop in read(
        lambda tx: tx.run(
            "MATCH (t:troop) RETURN t.name AS troop_id, t.vehicle_id AS vehicle_id"
        ).data()
    ):
        vehicle_id = troop.get("vehicle_id")
        if vehicle_id:
            speeds[troop["troop_id"]] = fetch_troop_speed_from_laravel(vehicle_id)
    return speeds

#CRUD

@app.get("/health")
def health():
    try:
        read(lambda tx: tx.run("RETURN 1").single())
        return {"status": "ok", "neo4j": "reachable"}
    except Exception as e:
        raise HTTPException(503, f"neo4j unreachable: {e}")

def register(res, cfg):
    label = cfg["label"]
    base = f"/api/{res}"

    @app.get(base, name=f"list_{res}")
    def list_all():
        q = f"MATCH (n:{label}) RETURN {projection(cfg)} AS item"
        return [r["item"] for r in read(lambda tx: tx.run(q).data())]

    @app.get(base + "/{item_id}", name=f"get_{res}")
    def get_one(item_id: str):
        item = read(lambda tx: fetch(tx, cfg, cfg.get('id_field', 'id'), item_id))
        if item is None:
            raise HTTPException(404, f"{label} not found")
        return item

    @app.post(base, status_code=201, name=f"create_{res}")
    def create(body: dict = Body(...)):
        missing = [f for f in cfg["required"] if f not in body]
        if missing:
            raise HTTPException(422, f"Missing required fields: {missing}")
        props = {k: body[k] for k in cfg["fields"] if k in body}
        props[cfg["id_field"]] = body.get(cfg["id_field"], str(uuid.uuid4()))

        def work(tx):
            tx.run(f"CREATE (n:{label} $props)", props=props)
            if cfg.get("stamp"):
                tx.run(
                    f"MATCH (n:{label} {{{cfg['id_field']}:$id}}) SET n.timestamp = datetime()",
                    id=props[cfg["id_field"]],
                )
            set_rels(tx, cfg, props[cfg["id_field"]], body)
            return fetch(tx, cfg, cfg["id_field"], props[cfg["id_field"]])

        return write(work)

    @app.patch(base + "/{item_id}", name=f"update_{res}")
    def update(item_id: str, body: dict = Body(...)):
        props = {k: body[k] for k in cfg["fields"] if k in body}

        def work(tx):
            if fetch(tx, cfg, cfg["id_field"], item_id) is None:
                raise HTTPException(404, f"{label} not found")
            if props:
                tx.run(
                    f"MATCH (n:{label} {{{cfg['id_field']}:$id}}) SET n += $props",
                    id=item_id,
                    props=props,
                )
            set_rels(tx, cfg, item_id, body)
            return fetch(tx, cfg, cfg["id_field"], item_id)

        return write(work)

    @app.delete(base + "/{item_id}", status_code=204, name=f"delete_{res}")
    def delete(item_id: str, force: bool = False):
        def work(tx):
            rec = tx.run(
                f"MATCH (n:{label} {{{cfg['id_field']}:$id}}) RETURN size([(n)--() | 1]) AS deg",
                id=item_id,
            ).single()
            if rec is None:
                raise HTTPException(404, f"{label} not found")
            if rec["deg"] > 0 and not force:
                raise HTTPException(
                    409, f"{label} has {rec['deg']} relationship(s). Use ?force=true to detach and delete."
                )
            tx.run(f"MATCH (n:{label} {{{cfg['id_field']}:$id}}) DETACH DELETE n", id=item_id)

        write(work)
        return Response(status_code=204)


for res, cfg in RESOURCES.items():
    register(res, cfg)

# Route APIs
ROUTE_RET = "r{.*, id: elementId(r), from_id: a.checkpoint_code, to_id: b.checkpoint_code} AS item"

@app.get("/api/routes")
def list_routes():
    q = f"MATCH (a:checkpoint)-[r:route_to]->(b:checkpoint) RETURN {ROUTE_RET}"
    return [r["item"] for r in read(lambda tx: tx.run(q).data())]


@app.post("/api/routes", status_code=201)
def create_route(body: dict = Body(...)):
    missing = [f for f in ("from_id", "to_id", "distance") if f not in body]
    if missing:
        raise HTTPException(422, f"Missing required fields: {missing}")

    def work(tx):
        rec = tx.run(
            "MATCH (a:checkpoint {checkpoint_code:$f}), (b:checkpoint {checkpoint_code:$t}) "
            f"CREATE (a)-[r:route_to {{distance:$d}}]->(b) RETURN {ROUTE_RET}",
            f=body["from_id"], t=body["to_id"], d=body["distance"],
        ).single()
        if rec is None:
            raise HTTPException(404, "from_id or to_id checkpoint not found")
        return rec["item"]

    return write(work)


@app.patch("/api/routes/{route_id}")
def update_route(route_id: str, body: dict = Body(...)):
    props = {k: body[k] for k in ("distance",) if k in body}

    def work(tx):
        rec = tx.run(
            "MATCH (a:checkpoint)-[r:route_to]->(b:checkpoint) WHERE elementId(r)=$rid "
            f"SET r += $props RETURN {ROUTE_RET}",
            rid=route_id, props=props,
        ).single()
        if rec is None:
            raise HTTPException(404, "route not found")
        return rec["item"]

    return write(work)


@app.delete("/api/routes/{route_id}", status_code=204)
def delete_route(route_id: str):
    def work(tx):
        rec = tx.run(
            "MATCH ()-[r:route_to]->() WHERE elementId(r)=$rid DELETE r RETURN count(*) AS c",
            rid=route_id,
        ).single()
        if rec["c"] == 0:
            raise HTTPException(404, "route not found")

    write(work)
    return Response(status_code=204)


# Plan API
class TroopMission(BaseModel):
    troop_id: str
    start: str  # Checkpoint name or code
    end: str    # Checkpoint name or code


class PlanRequest(BaseModel):
    troops: list[TroopMission]


class _Conn:
    def run_query(self, q):
        return read(lambda tx: tx.run(q).data())


@app.post("/api/plan")
def plan(req: PlanRequest):
    if not req.troops:
        raise HTTPException(422, "no troops in request")

    conn = _Conn()
    graph = get_subgraph(conn)
    coords = get_coords(conn)
    threats = get_threats(conn)

    known_troops = {
        r["name"]: r["name"]
        for r in read(lambda tx: tx.run("MATCH (t:troop) RETURN t.name AS name").data())
    }
    checkpoint_names = read(
        lambda tx: tx.run(
            "MATCH (c:checkpoint) RETURN c.name AS name, c.checkpoint_code AS code"
        ).data()
    )
    checkpoint_aliases = {
        alias: row["name"]
        for row in checkpoint_names
        for alias in (row.get("name"), row.get("code"))
        if alias
    }

    ids = [t.troop_id for t in req.troops]
    if len(ids) != len(set(ids)):
        raise HTTPException(422, "duplicate troop_id in request")

    all_speeds = fetch_all_troop_speeds_from_laravel()

    speeds = {}
    for t in req.troops:
        if t.troop_id not in known_troops:
            raise HTTPException(404, f"troop '{t.troop_id}' not found")
        start = checkpoint_aliases.get(t.start, t.start)
        end = checkpoint_aliases.get(t.end, t.end)
        for cp in (start, end):
            if cp not in graph or cp not in coords:
                raise HTTPException(422, f"checkpoint '{cp}' unknown or unrouted (troop {t.troop_id})")
        speeds[t.troop_id] = float(all_speeds.get(t.troop_id, 50.0))
        t.start = start
        t.end = end

    troops = {t.troop_id: (t.start, t.end) for t in req.troops}

    try:
        paths = cbs(troops, speeds, graph, coords)
    except ValueError as e:
        return {"feasible": False, "reason": str(e), "plans": {}}

    if paths is None:
        return {"feasible": False, "reason": "no collision free plan found", "plans": {}}
    plans = {}
    for t in req.troops:
        path = paths[t.troop_id]
        sp = speeds[t.troop_id]
        exposure, unavoidable = assess_threat(path, t.start, t.end, graph, coords, threats, speed_kmh=sp)
        plans[t.troop_id] = {
            "troop_name": known_troops[t.troop_id],
            "speed_kmh": sp,
            "path": [
                {
                    "checkpoint": n,
                    "arrival_hr": round(arr, 3),
                    "departure_hr": round(dep, 3),
                }
                for n, arr, dep in path
            ],
            "total_time_hr": round(path[-1][1], 3),
            "threat_exposure": exposure,
            "unavoidable_threat": unavoidable,
        }
    return {"feasible": True, "plans": plans}
