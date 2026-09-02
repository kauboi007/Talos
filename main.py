from db import neo4jconnection
from data_loader import get_coords,get_subgraph
from pathfinding import astar,find_conflict


#connection
conn=neo4jconnection("neo4j://127.0.0.1:7687","neo4j","talossys")

#get coords
coords=get_coords(conn)

#get subgraph
subgraph=get_subgraph(conn)

path1,cost1=astar('tambaram','avadi',subgraph,coords)
path2,cost2=astar('tambaram','ennore',subgraph,coords)

find_conflict(path1,path2)
