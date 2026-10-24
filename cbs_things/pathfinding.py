#changed the CBS to be continous time intervals instead of  time ticks
#conflict now means 2 troops are in teh same node in a time interval


import math
import heapq

waitcost_per_hour = 0.05 


def haversine(lat1, lng1, lat2, lng2):
    r = 6371.0  # Earth radius in kilometers
    lat1, lng1, lat2, lng2 = map(math.radians, [lat1, lng1, lat2, lng2])
    dlat = lat2 - lat1
    dlng = lng2 - lng1
    a = math.sin(dlat / 2) ** 2 + math.cos(lat1) * math.cos(lat2) * math.sin(dlng / 2) ** 2
    c = 2 * math.atan2(math.sqrt(a), math.sqrt(1 - a))
    return r * c


def heuristic_time(node, goal, coords, speed_kmh):
    lat1, lng1 = coords[node]
    lat2, lng2 = coords[goal]
    distance = haversine(lat1, lng1, lat2, lng2)
    return distance / speed_kmh if speed_kmh > 0 else distance


def is_interval_constrained(node, arr_time, dep_time, constraints):
    for c_node, start_t, end_t in constraints:
        if c_node == node:
            if max(arr_time, start_t) < min(dep_time, end_t):
                return True
    return False


def astar(start, goal, graph, coords, speed_kmh=50.0, constraints=None):
    if constraints is None:
        constraints = set()
    open_set = [(0.0, start, 0.0)] 
    g_score = {(start, 0.0): 0.0}
    came_from = {}
    while open_set:
        _, current, arr_t = heapq.heappop(open_set)
        if current == goal:
            path = []
            curr_state = (current, arr_t)
            while curr_state in came_from:
                prev_state, dep_t = came_from[curr_state]
                path.append((curr_state[0], curr_state[1], dep_t))
                curr_state = prev_state
            path.append((start, 0.0, path[-1][1] if path else 0.0))
            path.reverse()
            return path, arr_t
        for neighbor, dist_km in graph.get(current, []):
            travel_time = dist_km / speed_kmh if speed_kmh > 0 else dist_km
            dep_t = arr_t
            next_arr_t = dep_t + travel_time
            if is_interval_constrained(neighbor, arr_t, next_arr_t, constraints):
                continue
            tentative_g = g_score.get((current, arr_t), float("inf")) + travel_time
            state_key = (neighbor, next_arr_t)
            if tentative_g < g_score.get(state_key, float("inf")):
                g_score[state_key] = tentative_g
                f_score = tentative_g + heuristic_time(neighbor, goal, coords, speed_kmh)
                came_from[state_key] = ((current, arr_t), dep_t)
                heapq.heappush(open_set, (f_score, neighbor, next_arr_t))
        wait_step = 0.1  
        wait_arr_t = arr_t + wait_step
        if not is_interval_constrained(current, arr_t, wait_arr_t, constraints):
            tentative_g = g_score.get((current, arr_t), float("inf")) + wait_step + waitcost_per_hour
            state_key = (current, wait_arr_t)
            if tentative_g < g_score.get(state_key, float("inf")):
                g_score[state_key] = tentative_g
                f_score = tentative_g + heuristic_time(current, goal, coords, speed_kmh)
                came_from[state_key] = ((current, arr_t), wait_arr_t)
                heapq.heappush(open_set, (f_score, current, wait_arr_t))
    return None, float("inf")


def find_conflict(path_a, path_b):
    for node_a, arr_a, dep_a in path_a:
        for node_b, arr_b, dep_b in path_b:
            if node_a == node_b:
                overlap_start = max(arr_a, arr_b)
                overlap_end = min(dep_a, dep_b)
                if overlap_start < overlap_end and overlap_end > 0.001:
                    return (node_a, overlap_start, overlap_end)
    return None


def assess_threat(path, start, end, graph, coords, threats, speed_kmh=50.0):
    exposure = 0
    for i in range(1, len(path)):
        node = path[i][0]
        if node != path[i - 1][0]:
            exposure += threats.get(node, 0)
    if exposure == 0:
        return 0, False
    if end in threats:
        return exposure, True
    blocked = set(threats) - {start, end}
    safe_graph = {
        n: [(m, c) for m, c in nbrs if m not in blocked]
        for n, nbrs in graph.items()
        if n not in blocked
    }
    safe_path, pcost = astar(start, end, safe_graph, coords, speed_kmh=speed_kmh)
    return exposure, safe_path is None