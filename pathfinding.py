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
def astar(start, goal, graph,coords):
    open_set = [(0, start)]
    g_score = {start: 0}
    came_from = {}
    while open_set:
        current_f, current = heapq.heappop(open_set)
        if current == goal:
            path = [current]
            while current in came_from:
                current = came_from[current]
                path.append(current)
            path.reverse()
            return path, g_score[goal]
        for neighbor, cost in graph[current]:
            tentative_g = g_score[current] + cost

            if neighbor not in g_score or tentative_g < g_score[neighbor]:
                g_score[neighbor] = tentative_g
                f_score = tentative_g + heuristic(neighbor, goal,coords)
                came_from[neighbor] = current
                heapq.heappush(open_set, (f_score, neighbor))
    return None, float('inf')