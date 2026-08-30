#coords pulling
def get_coords(conn):
    coords={}
    temp=(conn.run_query("match (c:checkpoint) return c.name,c.lat,c.lng"))
    for i in temp:  
        n=i['c.name']
        lt=i['c.lat']
        ln=i['c.lng']
        coords[n]=(lt,ln)
    return coords

#subgraph pulling
def get_subgraph(conn):
    sub_graph={}
    routes=(conn.run_query("match (c:checkpoint)-[r:route_to]-(s) return c.name as n,r.distance as d,s.name as cn"))
    for j in routes:
            if j['n'] not in sub_graph:
                sub_graph[j['n']]=[]
            sub_graph[j['n']].append((j['cn'],j['d']))
    return sub_graph