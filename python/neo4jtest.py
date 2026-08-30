from neo4j import GraphDatabase
import math

def haversine(lat1,lng1,lat2,lng2):
    r=6371
    lat1,lng1,lat2,lng2=map(math.radians,[lat1,lng1,lat2,lng2])
    dlat=lat2-lat1
    dlng=lng2-lng1
    a=math.sin(dlat/2)**2+math.cos(lat1)*math.cos(lat2)*math.sin(dlng/2)**2
    c=2*math.atan2(math.sqrt(a),math.sqrt(1-a))
    return r*c


#init class
class neo4jconnection:
    def __init__(self,url,user,pwd):
        self.driver=GraphDatabase.driver(url,auth=(user,pwd))
    def close(self):
        self.driver.close()
    def run_query(self,query,parameters=None):
        with self.driver.session() as session:
            res=session.run(query,parameters or {})
            return [r.data() for r in res]

#connection
conn=neo4jconnection("neo4j://127.0.0.1:7687","neo4j","talossys")

#coords pulling
coords={}
temp=(conn.run_query("match (c:checkpoint) return c.name,c.lat,c.lng"))
for i in temp:
    n=i['c.name']
    lt=i['c.lat']
    ln=i['c.lng']
    coords[n]=(lt,ln)
         

#subgraph pulling
sub_graph={}
routes=(conn.run_query("match (c:checkpoint)-[r:route_to]-(s) return c.name as n,r.distance as d,s.name as cn"))
for j in routes:
        if j['n'] not in sub_graph:
            sub_graph[j['n']]=[]

        sub_graph[j['n']].append((j['cn'],j['d']))

print(coords)
