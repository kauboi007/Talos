## Quick reference
 
base url for api - http://127.0.0.1:8000

| Method | Path | Purpose | Success |
|---|---|---|---|
| GET | `/health` | Is Neo4j reachable | 200 |
| GET | `/api/{resource}` | List all | 200 |
| GET | `/api/{resource}/{id}` | Get one | 200 |
| POST | `/api/{resource}` | Create | 201 |
| PATCH | `/api/{resource}/{id}` | Update | 200 |
| DELETE | `/api/{resource}/{id}` | Delete | 204 |
| GET | `/api/routes` | List routes | 200 |
| POST | `/api/routes` | Create route | 201 |
| PATCH | `/api/routes/{route_id}` | Update route distance | 200 |
| DELETE | `/api/routes/{route_id}` | Delete route | 204 |
| POST | `/api/plan` | Plan collision-free paths for troops | 200 |
 
`{resource}` is one of: `officers`, `troops`, `checkpoints`, `threat-events`.
 
---
 
## Health
 
### `GET /health`
 
Response `200`:
```json
{ "status": "ok", "neo4j": "reachable" }
```
Response `503` if Neo4j is down: `{ "detail": "neo4j unreachable: ..." }`
 
---
 
## CRUD resources
 
Every resource has the same five endpoints (see the table above). What differs is the fields.
 
Every response object contains the node's properties, its generated `id` (UUID), and the ids of its linked nodes (keys ending in `_id`, `null` if not linked).
 
### Resource fields
 
| Resource | Path | Fields | Required on create | Link fields (send the other node's `id`) | Extra in response |
|---|---|---|---|---|---|
| Officer | `/api/officers` | `name`, `rank`, `rank_val`, `status` | `name`, `rank` | `reports_to_id` (officer), `stationed_at_id` (checkpoint), `troop_id` (troop) | none |
| Troop | `/api/troops` | `name`, `status`, `type`, `head_count`, `priority` | `name` | `start_id`, `end_id` (checkpoints) | none |
| Checkpoint | `/api/checkpoints` | `name`, `status`, `lat`, `lng`, `type` | `name`, `lat`, `lng` | none | `threat_count` (number of threat events affecting it) |
| Threat event | `/api/threat-events` | `name`, `type`, `severity` | `name`, `type`, `severity` | `affects_id` (checkpoint) | `timestamp` (string, read-only, not settable through the API) |
 
Checkpoint `name` must be unique.
 
### List: `GET /api/{resource}`
Response `200`: array of objects.
 
### Get one: `GET /api/{resource}/{id}`
Response `200`: one object. `404` if the id does not exist.
 
### Create: `POST /api/{resource}`
Request body: the fields from the table, plus any link fields. Unknown keys are ignored.
 
Example, `POST /api/checkpoints`:
```json
{ "name": "avadi", "lat": 13.11, "lng": 80.1, "type": "base", "status": "active" }
```
Response `201`:
```json
{
  "id": "b3c1...-uuid",
  "name": "avadi",
  "lat": 13.11,
  "lng": 80.1,
  "type": "base",
  "status": "active",
  "threat_count": 0
}
```
 
### Update: `PATCH /api/{resource}/{id}`
Send only the fields to change. Link fields replace the existing link, and `null` removes it.
 
Example, `PATCH /api/troops/{id}`:
```json
{ "status": "deployed", "start_id": "<checkpoint id>", "end_id": null }
```
Response `200`: the updated object.
 
### Delete: `DELETE /api/{resource}/{id}`
Response `204` with no body.
If the node still has relationships, it returns `409` unless you add `?force=true`, which detaches and deletes it.
 
### CRUD errors
 
| Status | Meaning |
|---|---|
| 404 | Item not found, or a link id (for example `start_id`) points to a node that does not exist |
| 409 | Duplicate value (for example a checkpoint name already exists), or delete blocked by relationships |
| 422 | Missing required fields, for example `{"detail": "Missing required fields: ['name', 'rank']"}` |
 
---
 
## Routes (roads between checkpoints)
 
A route is a directed `route_to` link from one checkpoint to another with a `distance`. The planner treats every route as usable in both directions.
 
Route objects:
```json
{ "id": "5:abc...:123", "distance": 65, "from_id": "<checkpoint id>", "to_id": "<checkpoint id>" }
```
Note that `from_id` and `to_id` are checkpoint **ids** (UUIDs), not names. The route `id` is Neo4j's internal element id and may change after a database dump and restore.
 
| Method | Path | Body | Response |
|---|---|---|---|
| GET | `/api/routes` | none | `200`: array of route objects |
| POST | `/api/routes` | `{ "from_id": "...", "to_id": "...", "distance": 65 }` (all required) | `201`: route object. `404` if either checkpoint is not found, `422` if a field is missing |
| PATCH | `/api/routes/{route_id}` | `{ "distance": 70 }` | `200`: updated route object. `404` if not found |
| DELETE | `/api/routes/{route_id}` | none | `204`. `404` if not found |
 
Two routes between the same pair of checkpoints are allowed (different distances). The planner uses the cheaper one.
 
---
 
## Plan: `POST /api/plan`
 
Plans collision-free paths for several troops at once.
 
Important differences from the CRUD endpoints:
- `troop_id` is the troop's **id** (UUID).
- `start` and `end` are checkpoint **names** (for example `avadi`), not ids.
- The start and end belong to the mission, so they are supplied in each request and are not read from the database.
### Request
 
```json
{
  "troops": [
    { "troop_id": "7892d46c-eddc-47fd-8159-15581757e01f", "start": "st_thomas_mount", "end": "ramanathapuram" },
    { "troop_id": "080c070a-2a84-4ceb-aa81-57727078d7ce", "start": "chengalpet", "end": "wellington" }
  ]
}
```
 
| Field | Type | Notes |
|---|---|---|
| `troops` | array | At least one entry |
| `troops[].troop_id` | string | Must be an existing troop id, no duplicates in one request |
| `troops[].start` | string | Checkpoint name, must have at least one route |
| `troops[].end` | string | Checkpoint name, must have at least one route |
 
### Response: plan found (`200`)
 
```json
{
  "feasible": true,
  "plans": {
    "7892d46c-eddc-47fd-8159-15581757e01f": {
      "troop_name": "alpha",
      "path": [
        { "checkpoint": "st_thomas_mount", "tick": 0 },
        { "checkpoint": "st_thomas_mount", "tick": 1 },
        { "checkpoint": "sulur", "tick": 2 },
        { "checkpoint": "ramanathapuram", "tick": 3 }
      ],
      "ticks": 3,
      "threat_exposure": 0,
      "unavoidable_threat": false
    },
    "080c070a-2a84-4ceb-aa81-57727078d7ce": {
      "troop_name": "bravo",
      "path": [
        { "checkpoint": "chengalpet", "tick": 0 },
        { "checkpoint": "sulur", "tick": 1 },
        { "checkpoint": "wellington", "tick": 2 }
      ],
      "ticks": 2,
      "threat_exposure": 0,
      "unavoidable_threat": false
    }
  }
}
```
 
`plans` is keyed by `troop_id`.
 
| Field | Meaning |
|---|---|
| `troop_name` | The troop's name, for display |
| `path` | Ordered steps. Each step is a checkpoint and a tick. |
| `ticks` | Tick at which the troop arrives (the last step's tick) |
| `threat_exposure` | Sum of threat penalties for every threatened checkpoint the troop enters. Relative score, not a raw severity. 0 means a clean route. |
| `unavoidable_threat` | `true` if the route passes through a threat and no threat-free route exists (or the destination itself is threatened). **Show this to the commander as a warning.** |
 
A repeated checkpoint on consecutive ticks (like `st_thomas_mount` at ticks 0 and 1 above) means the troop **waits** there for one tick to let another troop pass.
 
### Response: no plan (`200`)
 
This is a valid answer, not a server error. Show the reason to the user.
 
```json
{ "feasible": false, "reason": "no collision free plan found", "plans": {} }
```
`reason` is also set when a troop has no route at all (for example `path for troop <id> returned None`).
 
### Errors
 
| Status | When |
|---|---|
| 404 | A `troop_id` does not exist: `{"detail": "troop <id> not found"}` |
| 422 | Empty `troops` list, duplicate `troop_id`, unknown start or end checkpoint (`checkpoint 'x' unknown or has no routes`), or a missing field in the body |
 
---
 
## Notes for integrators
 
- **Ticks are steps, not real time.** Each move between checkpoints takes one tick, whatever the road length. Waiting also takes one tick.
- **Threats.** Threat events raise the cost of entering a checkpoint, so planned routes avoid threatened checkpoints when a reasonable alternative exists.
- **Timeouts.** Set a client timeout (30 seconds suggested). The planner stops searching after a fixed number of expansions and then returns `feasible: false`.
- **Known limitations.** Time is discretized (see above). Opposite-direction travel on the same road is allowed. Distances in the seed data are placeholders.
