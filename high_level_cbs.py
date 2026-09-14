class treenode:
    id=0
    def __init__(self,paths=None,cost=0,constraints=None):
        self.paths=paths if paths is not None else {}
        self.constraints = constraints if constraints is not None else {}
        self.cost=cost
        self.id=treenode.id
        treenode.id+=1

