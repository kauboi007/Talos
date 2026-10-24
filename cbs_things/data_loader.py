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
    routes=(conn.run_query("match (c:checkpoint)-[r:route_to]-(s:checkpoint) return c.name as n,coalesce(r.distance_km,r.distance) as d,s.name as cn"))
    threats=get_threats(conn)
    for j in routes:
            if j['n'] not in sub_graph:
                sub_graph[j['n']]=[]
            sub_graph[j['n']].append((j['cn'],(j['d']+threats.get(j['cn'],0))))
    return sub_graph

#threat stuff
risk_factor=2.5
def get_threats(conn):
    x=conn.run_query("match (n:checkpoint)<-[r:affects]-(t:threatevent) return n.name as cname,t.name as tname,t.type as type , t.severity as severity")
    th={}
    for i in x:
        s=i['severity'] or 0
        th[i['cname']]=th.get(i['cname'],0)+s**2+risk_factor #random funtion just to make the threat cost significant
    return th
