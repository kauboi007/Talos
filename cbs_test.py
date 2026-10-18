from pathfinding import haversine
from high_level_cbs import cbs

coords = {
    "A": (13.000, 79.990), "C": (13.000, 80.010),
    "B": (13.010, 80.000), "D": (12.990, 80.000),
    "M": (13.000, 80.000), "P": (12.995, 80.000),
}
edges = [("A","M"), ("M","C"), ("B","M"), ("M","D"), ("A","P"), ("P","C")]

graph = {n: [] for n in coords}
for u, v in edges:
    d = haversine(*coords[u], *coords[v])
    graph[u].append((v, d))
    graph[v].append((u, d))

troops = {"t1": ("A", "C"), "t2": ("B", "D")}
print(cbs(troops, graph, coords))