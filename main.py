from db import neo4jconnection
from data_loader import get_coords,get_subgraph
from pathfinding import astar


#connection
conn=neo4jconnection("neo4j://127.0.0.1:7687","neo4j","talossys")

#get coords
coords=get_coords(conn)

#get subgraph
subgraph=get_subgraph(conn)

path,cost=astar('tambaram','ennore',subgraph,coords)
print(path,cost)
