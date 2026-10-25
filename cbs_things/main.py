from db import neo4jconnection
from data_loader import get_coords,get_subgraph,get_threats
from pathfinding import astar,find_conflict
from high_level_cbs import cbs

#connection
conn=neo4jconnection("neo4j://127.0.0.1:7687","neo4j","talossys")

#get coords
coords=get_coords(conn)

#get subgraph
subgraph=get_subgraph(conn)

troops = {"t1": ("st_thomas_mount", "ennore"), "t2": ("chengalpet", "perambur")}

print(cbs(troops,subgraph,coords))

#testing code only