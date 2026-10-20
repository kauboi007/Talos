from pathfinding import astar,find_conflict
import heapq

class treenode:
    id=0
    def __init__(self,paths=None,cost=0,constraints=None,per_troop_costs=None):
        self.paths=paths if paths is not None else {}
        self.constraints = constraints if constraints is not None else {}
        self.per_troop_costs = per_troop_costs if per_troop_costs is not None else {}
        self.cost=cost
        self.id=treenode.id
        self.per_troop_costs=per_troop_costs
        treenode.id+=1

    def __lt__(self, other):
        return (self.cost,self.id)<(other.cost,other.id)

def build_root(troops,graph,coords):
    paths={}
    cost=0
    constraints={}
    per_troop_costs={}
    for i in troops.keys():
        paths[i],tcost=astar(troops[i][0],troops[i][1],graph,coords)
        if(paths[i]==None):
            raise ValueError(f"path for troop {i} returned None")
        cost+=tcost
        constraints[i]=set()
        per_troop_costs[i]=tcost
    return treenode(paths,cost,constraints,per_troop_costs)

def find_first_conflict(node):
    paths=node.paths
    keys=list(node.paths.keys())
    n=len(paths)
    for i in range(0,n):
        for j in range(i+1,n):
            x=find_conflict(paths[keys[i]],paths[keys[j]])
            if(x!=None):
                return x+(keys[i],keys[j])
    return None

def make_child(parent,troop_id,ban,graph,coords,start,goal):
    local_cons=dict(parent.constraints)
    local_cons[troop_id]=set(parent.constraints[troop_id])
    local_cons[troop_id].add(ban)

    local_paths=dict(parent.paths)
    local_cpn=dict(parent.per_troop_costs)
    y,tcost=astar(start,goal,graph,coords,local_cons[troop_id])
    local_paths[troop_id]=y
    if(local_paths[troop_id]==None):
        return None #deadend no child node must be created
    local_cpn[troop_id]=tcost
    cost=sum(local_cpn.values())
    return treenode(local_paths,cost,local_cons,local_cpn)

def cbs(troops,graph,coords,limit=5000):
    #build root , push cost id node into heap
    #while heap is not empty pop run find first conflict if its none return the paths
    #else make 2 children :)
    root_node=build_root(troops,graph,coords)
    heap=[root_node]
    currexp=0
    while heap:
        currexp+=1
        if currexp>limit:
            return None
        node=heapq.heappop(heap)
        x=find_first_conflict(node)
        if(x is None):
            return node.paths
        tmpnode=make_child(node,x[-2],(x[0],x[2]),graph,coords,troops[x[-2]][0],troops[x[-2]][1])
        if(tmpnode is not None):
            heapq.heappush(heap,tmpnode)
        tmpnode=make_child(node,x[-1],(x[1],x[2]),graph,coords,troops[x[-1]][0],troops[x[-1]][1])
        if(tmpnode is not None):
            heapq.heappush(heap,tmpnode)
    return None
        
        