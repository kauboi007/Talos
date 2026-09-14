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

def find_conflict(path_a,path_b):
    i=0
    j=0
    n=len(path_a)
    m=len(path_b)
    node2=0
    ts2=0
    node1=0
    ts1=0
    while(i<n and j<m):
        node1,ts1=path_a[i]
        node2,ts2=path_b[j]
        if(ts1==ts2):
            if(node1!=node2):
                i+=1
                j+=1
            else:
                ep1=path_a[-1][0] #end point of path a
                ep2=path_b[-1][0] #end point of path b
                sp1=path_a[0][0] #starting point of path a
                sp2=path_b[0][0] #starting point of path b
                #check if node is starting or ending point so that to avoid false flag
                if((node1==ep1 and node2==ep2) or(node1==sp1 and node2==sp2 and ts1==ts2==0)):
                    i+=1
                    j+=1
                else:
                    return (node1,node2,ts1)      
    while(i<n): #rest of the nodes in path a
        node1,ts1=path_a[i]
        if(ts1>ts2):
            if(node1!=node2):
                i+=1
            else:
                ep1=path_a[-1][0]
                ep2=path_b[-1][0]
                sp1=path_a[0][0]
                sp2=path_b[0][0]
                if((node1==ep1 and node2==ep2)):
                    i+=1
                else:
                    return (node1,node2,ts1)
    while(j<m): #rest of the nodes in path b
        node2,ts2=path_b[j]
        if(ts1<ts2):
            if(node1!=node2):
                j+=1
            else:
                ep1=path_a[-1][0]
                ep2=path_b[-1][0]
                sp1=path_a[0][0]
                sp2=path_b[0][0]
                if((node1==ep1 and node2==ep2)):
                    j+=1
                else:
                    return (node1,node2,ts2)



