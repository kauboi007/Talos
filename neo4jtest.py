from neo4j import GraphDatabase

class neo4jconnection:
    def __init__(self,url,user,pwd):
        self.driver=GraphDatabase.driver(url,auth=(user,pwd))
    def close(self):
        self.driver.close()
    def run_query(self,query,parameters=None):
        with self.driver.session() as session:
            res=session.run(query,parameters or {})
            return [r.data() for r in res]

conn=neo4jconnection("neo4j://127.0.0.1:7687","neo4j","talossys")
res=conn.run_query("create(:officer {id:1})")
print(res)