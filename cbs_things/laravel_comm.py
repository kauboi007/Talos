import json
import os
import time
import uuid

from fastapi import HTTPException
from redis import Redis


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


def _read_response(correlation_id, stream_id):
    deadline = time.monotonic() + REDIS_TIMEOUT

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
                return fields

    raise HTTPException(503, "Timed out waiting for Laravel response.")


def _request_laravel(fields):
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
                **fields,
            },
        )
        return _read_response(correlation_id, response_cursor)
    except HTTPException:
        raise
    except Exception as exception:
        raise HTTPException(503, f"Laravel service unavailable: {exception}") from exception


def fetch_troop_speed_from_laravel(vehicle_id):
    try:
        fields = _request_laravel({
            "request_type": "vehicle_speed",
            "vehicle_id": vehicle_id,
        })
        speed = float(fields["speed_kmh"])
        if speed <= 0:
            raise HTTPException(503, "Laravel returned an invalid vehicle speed.")
        return speed
    except HTTPException:
        raise
    except Exception as exception:
        raise HTTPException(503, f"Vehicle speed service unavailable: {exception}") from exception


def get_persisted_missions():
    fields = _request_laravel({"request_type": "persisted_missions"})
    try:
        missions = json.loads(fields.get("missions", "[]"))
    except (TypeError, json.JSONDecodeError) as exception:
        raise HTTPException(503, "Laravel returned invalid persisted mission data.") from exception

    if not isinstance(missions, list):
        raise HTTPException(503, "Laravel returned invalid persisted mission data.")
    if not all(isinstance(mission, dict) for mission in missions):
        raise HTTPException(503, "Laravel returned invalid persisted mission entries.")

    return missions


def fetch_all_troop_speeds_from_laravel(read_neo4j):
    speeds = {}
    for troop in read_neo4j(
        "MATCH (t:troop) RETURN t.name AS troop_id, t.vehicle_id AS vehicle_id"
    ):
        vehicle_id = troop.get("vehicle_id")
        if vehicle_id:
            speeds[troop["troop_id"]] = fetch_troop_speed_from_laravel(vehicle_id)
    return speeds
