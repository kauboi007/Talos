import math
import heapq

waitcost_per_hour = 0.05 


def haversine(lat1, lng1, lat2, lng2):
    r = 6371.0 
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


def astar(start, goal, graph, coords, speed_kmh=50.0, constraints=None, max_iterations=50000):
    if constraints is None:
        constraints = set()

    open_set = [(0.0, start, 0.0)]
    g_score = {(start, 0.0): 0.0}
    came_from = {}

    iterations = 0
    while open_set:
        #wait check 0
        iterations += 1
        if iterations > max_iterations:
            return None, float("inf")
        _, current, arr_t = heapq.heappop(open_set)

        if current == goal:
            states = []
            curr_state = (current, arr_t)
            curr_dep = arr_t

            while curr_state in came_from:
                prev_node, prev_arr, dep_from_prev = came_from[curr_state]
                states.append((curr_state[0], curr_state[1], curr_dep))
                curr_state = (prev_node, prev_arr)
                curr_dep = dep_from_prev

            states.append((start, 0.0, curr_dep))
            states.reverse()
            collapsed_path = []
            for node, arr, dep in states:
                if collapsed_path and collapsed_path[-1][0] == node:
                    collapsed_path[-1] = (node, collapsed_path[-1][1], dep)
                else:
                    collapsed_path.append((node, arr, dep))

            return collapsed_path, arr_t

        for neighbor, dist_km in graph.get(current, []):
            travel_time = dist_km / speed_kmh if speed_kmh > 0 else dist_km
            candidate_deps = {arr_t}
            if constraints:
                for c_node, c_start, c_end in constraints:
                    if c_node == neighbor:
                        dep_needed = c_end - travel_time
                        if dep_needed > arr_t:
                            candidate_deps.add(dep_needed)
                    if c_node == current:
                        if c_end > arr_t:
                            candidate_deps.add(c_end)

            for dep_t in sorted(candidate_deps):
                wait_time = dep_t - arr_t
                if wait_time < 0:
                    continue

                #wait check 1
                if wait_time > 1e-9 and is_interval_constrained(current, arr_t, dep_t, constraints):
                    continue
                next_arr_t = dep_t + travel_time

                if is_interval_constrained(neighbor, next_arr_t, next_arr_t + 1e-6, constraints):
                    continue
                edge_cost = travel_time + wait_time * (1.0 + waitcost_per_hour)
                tentative_g = g_score.get((current, arr_t), float("inf")) + edge_cost
                state_key = (neighbor, next_arr_t)

                if tentative_g < g_score.get(state_key, float("inf")):
                    g_score[state_key] = tentative_g
                    f_score = tentative_g + heuristic_time(neighbor, goal, coords, speed_kmh)
                    came_from[state_key] = (current, arr_t, dep_t)
                    heapq.heappush(open_set, (f_score, neighbor, next_arr_t))

    return None, float("inf")


def find_conflict(path_a, path_b):
    for idx_a, (node_a, arr_a, dep_a) in enumerate(path_a):
        eff_dep_a = float("inf") if idx_a == len(path_a) - 1 else dep_a

        for idx_b, (node_b, arr_b, dep_b) in enumerate(path_b):
            eff_dep_b = float("inf") if idx_b == len(path_b) - 1 else dep_b

            if node_a == node_b:
                overlap_start = max(arr_a, arr_b)
                overlap_end = min(eff_dep_a, eff_dep_b)

                if idx_a == len(path_a) - 1 and idx_b == len(path_b) - 1:
                    continue

                if overlap_start < overlap_end and (overlap_end - overlap_start) > 1e-4:
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