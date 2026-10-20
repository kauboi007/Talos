from neo4j import GraphDatabase


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