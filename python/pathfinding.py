import math
import heapq

#haversine function
def haversine(lat1,lng1,lat2,lng2): 
    r=6371
    lat1,lng1,lat2,lng2=map(math.radians,[lat1,lng1,lat2,lng2])
    dlat=lat2-lat1
    dlng=lng2-lng1
    a=math.sin(dlat/2)**2+math.cos(lat1)*math.cos(lat2)*math.sin(dlng/2)**2
    c=2*math.atan2(math.sqrt(a),math.sqrt(1-a))
    return r*c

#heuristic fn
def heuristic(node,goal,coords):
    lat1,lng1=coords[node]
    lat2,lng2=coords[goal]
    return haversine(lat1,lng1,lat2,lng2)

#a star algo
import math
import heapq

def haversine(lat1,lng1,lat2,lng2): 
    r=6371
    lat1,lng1,lat2,lng2=map(math.radians,[lat1,lng1,lat2,lng2])
    dlat=lat2-lat1
    dlng=lng2-lng1
    a=math.sin(dlat/2)**2+math.cos(lat1)*math.cos(lat2)*math.sin(dlng/2)**2
    c=2*math.atan2(math.sqrt(a),math.sqrt(1-a))
    return r*c

def heuristic(node,goal,coords):
    lat1,lng1=coords[node]
    lat2,lng2=coords[goal]
    return haversine(lat1,lng1,lat2,lng2)

def astar(start, goal, graph, coords, constraints=None):
    if constraints is None:
        constraints = set()
    open_set = [(0, start, 0)]       
    g_score = {(start, 0): 0}
    came_from = {}
    while open_set:
        current_f, current, current_ts = heapq.heappop(open_set)
        if current == goal:
            path = [(current, current_ts)]
            state = (current, current_ts)
            while state in came_from:
                state = came_from[state]
                path.append(state)
            path.reverse()
            return path, g_score[(current, current_ts)]

        for neighbor, cost in graph[current]:
            next_ts = current_ts + 1             
            if (neighbor, next_ts) in constraints: 
                continue
            tentative_g = g_score[(current, current_ts)] + cost
            if (neighbor, next_ts) not in g_score or tentative_g < g_score[(neighbor, next_ts)]:
                g_score[(neighbor, next_ts)] = tentative_g
                f_score = tentative_g + heuristic(neighbor, goal, coords)
                came_from[(neighbor, next_ts)] = (current, current_ts) 
                heapq.heappush(open_set, (f_score, neighbor, next_ts))
    return None, float('inf')