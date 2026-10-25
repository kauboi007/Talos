import heapq
from pathfinding import astar, find_conflict


class TreeNode:
    id_counter = 0
    def __init__(self, paths=None, cost=0.0, constraints=None, per_troop_costs=None):
        self.paths = paths if paths is not None else {}
        self.constraints = constraints if constraints is not None else {}
        self.per_troop_costs = per_troop_costs if per_troop_costs is not None else {}
        self.cost = cost
        self.id = TreeNode.id_counter
        TreeNode.id_counter += 1
    def __lt__(self, other):
        return (self.cost, self.id) < (other.cost, other.id)


def build_root(troops, speeds, graph, coords, reserved=()):
    paths = {}
    cost = 0.0
    constraints = {}
    per_troop_costs = {}
    for t_id, (start, end) in troops.items():
        sp = speeds.get(t_id, 50.0)
        base = set(reserved)
        p, tcost = astar(start, end, graph, coords, speed_kmh=sp, constraints=set(base))
        if p is None:
            raise ValueError(f"Path for troop '{t_id}' returned None")
        paths[t_id] = p
        cost += tcost
        constraints[t_id] = base
        per_troop_costs[t_id] = tcost
    return TreeNode(paths, cost, constraints, per_troop_costs)


def find_first_conflict(node):
    paths = node.paths
    keys = list(paths.keys())
    n = len(keys)
    for i in range(n):
        for j in range(i + 1, n):
            c = find_conflict(paths[keys[i]], paths[keys[j]])
            if c is not None:
                return c + (keys[i], keys[j])
    return None


def make_child(parent, troop_id, ban, speeds, graph, coords, start, goal):
    local_cons = {k: set(v) for k, v in parent.constraints.items()}
    local_cons[troop_id].add(ban)
    local_paths = dict(parent.paths)
    local_cpn = dict(parent.per_troop_costs)
    sp = speeds.get(troop_id, 50.0)
    path, tcost = astar(start, goal, graph, coords, speed_kmh=sp, constraints=local_cons[troop_id])
    if path is None:
        return None 
    local_paths[troop_id] = path
    local_cpn[troop_id] = tcost
    cost = sum(local_cpn.values())
    return TreeNode(local_paths, cost, local_cons, local_cpn)


def cbs(troops, speeds, graph, coords, limit=5000,reserved=None):
    root_node = build_root(troops, speeds, graph, coords,reserved or [])
    heap = [root_node]
    currexp = 0
    while heap:
        currexp += 1
        if currexp > limit:
            return None
        node = heapq.heappop(heap)
        conflict = find_first_conflict(node)
        if conflict is None:
            return node.paths
        c_node, start_t, end_t, troop_a, troop_b = conflict
        ban = (c_node, start_t, end_t)

        child_a = make_child(node, troop_a, ban, speeds, graph, coords, troops[troop_a][0], troops[troop_a][1])
        if child_a is not None:
            heapq.heappush(heap, child_a)

        child_b = make_child(node, troop_b, ban, speeds, graph, coords, troops[troop_b][0], troops[troop_b][1])
        if child_b is not None:
            heapq.heappush(heap, child_b)
    return None