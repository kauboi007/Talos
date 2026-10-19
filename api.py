import uuid
from contextlib import asynccontextmanager
from fastapi import Body, FastAPI, HTTPException, Response
from neo4j import GraphDatabase
from neo4j.exceptions import ConstraintError

URI = "neo4j://127.0.0.1:7687"
AUTH = ("neo4j","talossys")
driver = GraphDatabase.driver(URI, auth=AUTH)

RESOURCES = { #this part is vibecoded:gave claude the schema of neo4j
    "officers": {
        "label": "officer",
        "fields": ["name", "rank", "rank_val", "status"],
        "required": ["name", "rank"],
        "rels": {
            "reports_to_id": ("reports_to", "officer", "out"),
            "stationed_at_id": ("stationed_at", "checkpoint", "out"),
            "troop_id": ("belongs_to", "troop", "out"),
        },
    },
    "troops": {
        "label": "troop",
        "fields": ["name", "status", "type", "head_count", "priority"],
        "required": ["name"],
        "rels": {
            "start_id": ("starts_at", "checkpoint", "out"),
            "end_id": ("ends_at", "checkpoint", "out"),
        },
    },
    "checkpoints": {
        "label": "checkpoint",
        "fields": ["name", "status", "lat", "lng", "type"],
        "required": ["name", "lat", "lng"],
        "rels": {},
        "extra": "threat_count: count{match(n)<-[:affects]-(:threatevent)}",
    },
    "threat-events": {
        "label": "threatevent",
        "fields": ["name", "type", "severity"],
        "required": ["name", "type", "severity"],
        "rels": {"affects_id": ("affects", "checkpoint", "out")},
        "extra": "timestamp: toString(n.timestamp)",
    },
}


@asynccontextmanager #i was first using on_startup and on_shutdown but then i saw suggested  lifespan is better -kauboi007
async def lifespan(app: FastAPI):
    with driver.session() as s:
        for res in RESOURCES.values():
            try:
                s.run(
                    f"CREATE CONSTRAINT {res['label']}_id IF NOT EXISTS "
                    f"FOR (n:{res['label']}) REQUIRE n.id IS UNIQUE"
                )
            except Exception:
                pass #change to print if we want to see the error else keep it this way but its not explicit -kauboi007
        try:
            s.run(
                "CREATE CONSTRAINT checkpoint_name IF NOT EXISTS "
                "FOR (n:checkpoint) REQUIRE n.name IS UNIQUE"
            )
        except Exception:
            pass  #same as above comment -kauboi007
    yield
    driver.close()

app = FastAPI(title="talos", lifespan=lifespan)


#helper fns
def write(fn):
    try:
        with driver.session() as s:
            return s.execute_write(fn)
    except ConstraintError:
        raise HTTPException(409, "Duplicate value")

def read(fn):
    with driver.session() as s:
        return s.execute_read(fn)

def projection(cfg): #vibecoded - this lets you query the linked ids in 1 query instead of one query per link
    parts = [".*"]
    for key, (rel, tl, d) in cfg["rels"].items():
        pat = f"(n)-[:{rel}]->(x:{tl})" if d == "out" else f"(n)<-[:{rel}]-(x:{tl})"
        parts.append(f"{key}: [{pat} | x.id][0]")
    if cfg.get("extra"):
        parts.append(cfg["extra"])
    return "n{" + ", ".join(parts) + "}"


def set_rels(tx, cfg, node_id, body): #vibecoded
    label = cfg["label"]
    for key, (rel, tl, d) in cfg["rels"].items():
        if key not in body:
            continue
        left, right = ("-", "->") if d == "out" else ("<-", "-")
        tx.run(
            f"MATCH (n:{label} {{id:$id}}){left}[r:{rel}]{right}(:{tl}) DELETE r",
            id=node_id,
        )
        tid = body[key]
        if tid is None:  # null = unlink
            continue
        rec = tx.run(
            f"MATCH (n:{label} {{id:$id}}), (t:{tl} {{id:$tid}}) "
            f"CREATE (n){left}[:{rel}]{right}(t) RETURN t.id AS tid",
            id=node_id,
            tid=tid,
        ).single()
        if rec is None:
            raise HTTPException(404, f"{key}: no {tl} with id {tid}")


def fetch(tx, cfg, node_id): #fetching the  node by id 
    rec = tx.run(
        f"MATCH (n:{cfg['label']} {{id:$id}}) RETURN {projection(cfg)} AS item",
        id=node_id,
    ).single()
    return rec["item"] if rec else None


#health check just checks if neo4j is reachable
@app.get("/health")
def health():
    try:
        read(lambda tx: tx.run("RETURN 1").single())
        return {"status": "ok", "neo4j": "reachable"}
    except Exception as e:
        raise HTTPException(503, f"neo4j unreachable: {e}")


#crud apis

def register(res, cfg): #claude suggested that wrapping the endpoints inside of a fn is better instead of raw for loops so its has better scope resolution -kauboi007
    label = cfg["label"]
    base = f"/api/{res}"

    @app.get(base, name=f"list_{res}")
    def list_all():
        q = f"MATCH (n:{label}) RETURN {projection(cfg)} AS item"
        return [r["item"] for r in read(lambda tx: tx.run(q).data())]

    @app.get(base + "/{item_id}", name=f"get_{res}")
    def get_one(item_id: str):
        item = read(lambda tx: fetch(tx, cfg, item_id))
        if item is None:
            raise HTTPException(404, f"{label} not found")
        return item

    @app.post(base, status_code=201, name=f"create_{res}")
    def create(body: dict = Body(...)):
        missing = [f for f in cfg["required"] if f not in body]
        if missing:
            raise HTTPException(422, f"Missing required fields: {missing}")
        props = {k: body[k] for k in cfg["fields"] if k in body}
        props["id"] = str(uuid.uuid4())

        def work(tx):
            tx.run(f"CREATE (n:{label} $props)", props=props)
            set_rels(tx, cfg, props["id"], body)
            return fetch(tx, cfg, props["id"])

        return write(work)

    @app.patch(base + "/{item_id}", name=f"update_{res}") #vibecoded
    def update(item_id: str, body: dict = Body(...)):
        props = {k: body[k] for k in cfg["fields"] if k in body}

        def work(tx):
            if fetch(tx, cfg, item_id) is None:
                raise HTTPException(404, f"{label} not found")
            if props:
                tx.run(f"MATCH (n:{label} {{id:$id}}) SET n += $props", id=item_id, props=props)
            set_rels(tx, cfg, item_id, body)
            return fetch(tx, cfg, item_id)

        return write(work)

    @app.delete(base + "/{item_id}", status_code=204, name=f"delete_{res}")
    def delete(item_id: str, force: bool = False):
        def work(tx):
            rec = tx.run(
                f"MATCH (n:{label} {{id:$id}}) RETURN size([(n)--() | 1]) AS deg",
                id=item_id,
            ).single()
            if rec is None:
                raise HTTPException(404, f"{label} not found")
            if rec["deg"] > 0 and not force:
                raise HTTPException(
                    409, f"{label} has {rec['deg']} relationship(s). Use ?force=true to detach and delete."
                )
            tx.run(f"MATCH (n:{label} {{id:$id}}) DETACH DELETE n", id=item_id)

        write(work)
        return Response(status_code=204)


for res, cfg in RESOURCES.items():
    register(res, cfg)


#route apis
ROUTE_RET = "r{.*, id: elementId(r), from_id: a.id, to_id: b.id} AS item" #it uses the inbuilt id as identifier , note that claude said this id can change after a restore or dump so little fragile -kauboi007 #vibecoded


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
            "MATCH (a:checkpoint {id:$f}), (b:checkpoint {id:$t}) "
            f"CREATE (a)-[r:route_to {{distance:$d}}]->(b) RETURN {ROUTE_RET}",
            f=body["from_id"], t=body["to_id"], d=body["distance"],
        ).single()
        if rec is None:
            raise HTTPException(404, "from_id or to_id checkpoint not found")
        return rec["item"]

    return write(work)


@app.patch("/api/routes/{route_id}") #update route parameters , claude said patch is better than put -kauboi007
def update_route(route_id: str, body: dict = Body(...)): #this section is vibecoded
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
