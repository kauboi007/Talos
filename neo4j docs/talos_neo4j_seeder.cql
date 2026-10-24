MATCH (c:checkpoint)
WHERE c.name IN ['tambaram', 'avadi', 'st_thomas_mount', 'wellington', 'sulur', 'ins_rajali', 'ennore', 'ramanathapuram', 'thanjavur', 'chengalpet']
SET c.checkpoint_code = CASE c.name
    WHEN 'tambaram' THEN 'CHK-TAMBARAM'
    WHEN 'avadi' THEN 'CHK-AVADI'
    WHEN 'st_thomas_mount' THEN 'CHK-ST-THOMAS-MOUNT'
    WHEN 'wellington' THEN 'CHK-WELLINGTON'
    WHEN 'sulur' THEN 'CHK-SULUR'
    WHEN 'ins_rajali' THEN 'CHK-INS-RAJALI'
    WHEN 'ennore' THEN 'CHK-ENNORE'
    WHEN 'ramanathapuram' THEN 'CHK-RAMANATHAPURAM'
    WHEN 'thanjavur' THEN 'CHK-THANJAVUR'
    WHEN 'chengalpet' THEN 'CHK-CHENGALPET'
END,
c.name = CASE c.name
    WHEN 'tambaram' THEN 'Tambaram'
    WHEN 'avadi' THEN 'Avadi'
    WHEN 'st_thomas_mount' THEN 'St. Thomas Mount'
    WHEN 'wellington' THEN 'Wellington'
    WHEN 'sulur' THEN 'Sulur'
    WHEN 'ins_rajali' THEN 'INS Rajali'
    WHEN 'ennore' THEN 'Ennore'
    WHEN 'ramanathapuram' THEN 'Ramanathapuram'
    WHEN 'thanjavur' THEN 'Thanjavur'
    WHEN 'chengalpet' THEN 'Chengalpet'
END;

CREATE CONSTRAINT soldier_service_id_unique IF NOT EXISTS
FOR (s:soldier)
REQUIRE s.service_id IS UNIQUE;

CREATE CONSTRAINT checkpoint_code_unique IF NOT EXISTS
FOR (c:checkpoint)
REQUIRE c.checkpoint_code IS UNIQUE;

UNWIND [
    {code:'CHK-TAMBARAM',name:'Tambaram',type:'depot',lat:12.9249,lng:80.1000},
    {code:'CHK-AVADI',name:'Avadi',type:'base',lat:13.1147,lng:80.1098},
    {code:'CHK-ST-THOMAS-MOUNT',name:'St. Thomas Mount',type:'HQ',lat:12.9941,lng:80.1890},
    {code:'CHK-WELLINGTON',name:'Wellington',type:'base',lat:11.3655,lng:76.7844},
    {code:'CHK-SULUR',name:'Sulur',type:'outpost',lat:11.0250,lng:77.1250},
    {code:'CHK-INS-RAJALI',name:'INS Rajali',type:'base',lat:13.1643,lng:80.2350},
    {code:'CHK-ENNORE',name:'Ennore',type:'outpost',lat:13.2146,lng:80.3210},
    {code:'CHK-RAMANATHAPURAM',name:'Ramanathapuram',type:'base',lat:9.3639,lng:78.8395},
    {code:'CHK-THANJAVUR',name:'Thanjavur',type:'depot',lat:10.7870,lng:79.1378},
    {code:'CHK-CHENGALPET',name:'Chengalpet',type:'HQ',lat:12.6819,lng:79.9888}
] AS row
MERGE (c:checkpoint {checkpoint_code:row.code})
SET c.name = row.name,
    c.type = row.type,
    c.lat = row.lat,
    c.lng = row.lng;

UNWIND range(1,124) AS n
MERGE (:soldier {service_id:'TAL-OF-' + right('0000' + toString(n),4)});

UNWIND [
    ['TAL-OF-0001','CHK-TAMBARAM'],
    ['TAL-OF-0002','CHK-AVADI'],
    ['TAL-OF-0003','CHK-CHENGALPET'],
    ['TAL-OF-0004','CHK-THANJAVUR'],
    ['TAL-OF-0005','CHK-ST-THOMAS-MOUNT'],
    ['TAL-OF-0006','CHK-SULUR'],
    ['TAL-OF-0007','CHK-ST-THOMAS-MOUNT'],
    ['TAL-OF-0008','CHK-ENNORE']
] + [
    ['TAL-OF-0009','CHK-AVADI'],
    ['TAL-OF-0010','CHK-AVADI'],
    ['TAL-OF-0011','CHK-AVADI'],
    ['TAL-OF-0012','CHK-AVADI'],
    ['TAL-OF-0013','CHK-AVADI'],
    ['TAL-OF-0014','CHK-AVADI'],
    ['TAL-OF-0015','CHK-AVADI'],
    ['TAL-OF-0016','CHK-AVADI'],
    ['TAL-OF-0017','CHK-AVADI'],
    ['TAL-OF-0018','CHK-AVADI'],
    ['TAL-OF-0019','CHK-AVADI'],
    ['TAL-OF-0020','CHK-AVADI'],
    ['TAL-OF-0021','CHK-AVADI'],
    ['TAL-OF-0022','CHK-AVADI'],
    ['TAL-OF-0023','CHK-AVADI'],
    ['TAL-OF-0024','CHK-AVADI'],
    ['TAL-OF-0025','CHK-AVADI'],
    ['TAL-OF-0026','CHK-AVADI'],
    ['TAL-OF-0027','CHK-AVADI']
] AS row
MATCH (s:soldier {service_id:row[0]})
MATCH (c:checkpoint {checkpoint_code:row[1]})
MERGE (s)-[:stationed_at]->(c);

UNWIND range(28,47) AS n
MATCH (s:soldier {service_id:'TAL-OF-' + right('0000' + toString(n),4)})
MATCH (c:checkpoint {checkpoint_code:'CHK-WELLINGTON'})
MERGE (s)-[:stationed_at]->(c);

UNWIND range(48,67) AS n
MATCH (s:soldier {service_id:'TAL-OF-' + right('0000' + toString(n),4)})
MATCH (c:checkpoint {checkpoint_code:'CHK-INS-RAJALI'})
MERGE (s)-[:stationed_at]->(c);

UNWIND range(68,87) AS n
MATCH (s:soldier {service_id:'TAL-OF-' + right('0000' + toString(n),4)})
MATCH (c:checkpoint {checkpoint_code:'CHK-RAMANATHAPURAM'})
MERGE (s)-[:stationed_at]->(c);

UNWIND [5,7] + range(88,105) AS n
MATCH (s:soldier {service_id:'TAL-OF-' + right('0000' + toString(n),4)})
MATCH (c:checkpoint {checkpoint_code:'CHK-ST-THOMAS-MOUNT'})
MERGE (s)-[:stationed_at]->(c);

UNWIND [3] + range(106,124) AS n
MATCH (s:soldier {service_id:'TAL-OF-' + right('0000' + toString(n),4)})
MATCH (c:checkpoint {checkpoint_code:'CHK-CHENGALPET'})
MERGE (s)-[:stationed_at]->(c);

UNWIND [
    ['CHK-TAMBARAM','CHK-AVADI',20],
    ['CHK-TAMBARAM','CHK-ST-THOMAS-MOUNT',15],
    ['CHK-TAMBARAM','CHK-ENNORE',35],
    ['CHK-AVADI','CHK-INS-RAJALI',65],
    ['CHK-CHENGALPET','CHK-THANJAVUR',280],
    ['CHK-THANJAVUR','CHK-RAMANATHAPURAM',180],
    ['CHK-CHENGALPET','CHK-SULUR',500],
    ['CHK-SULUR','CHK-WELLINGTON',70],
    ['CHK-INS-RAJALI','CHK-SULUR',350]
] AS row
MATCH (a:checkpoint {checkpoint_code:row[0]})
MATCH (b:checkpoint {checkpoint_code:row[1]})
MERGE (a)-[r:route_to]->(b)
SET r.distance_km = row[2];

UNWIND [
    {name:'Alpha',type:'kill',head_count:4,priority:1,status:'started',vehicle_id:'TAL-A-0001'},
    {name:'Delta',type:'medic',head_count:2,priority:2,status:'started',vehicle_id:'TAL-AM-0001'},
    {name:'Bravo',type:'logistics',head_count:6,priority:2,status:'started',vehicle_id:'TAL-T-0001'},
    {name:'Charlie',type:'kill',head_count:5,priority:1,status:'started',vehicle_id:'TAL-M-0001'}
] AS row
MERGE (t:troop {name:row.name})
SET t.type = row.type,
    t.head_count = row.head_count,
    t.priority = row.priority,
    t.status = row.status,
    t.vehicle_id = row.vehicle_id;

UNWIND [
    ['TAL-OF-0002','Alpha'],
    ['TAL-OF-0003','Delta'],
    ['TAL-OF-0007','Bravo'],
    ['TAL-OF-0008','Charlie']
] AS row
MATCH (s:soldier {service_id:row[0]})
MATCH (t:troop {name:row[1]})
MERGE (s)-[:belongs_to]->(t);

MATCH (s:soldier)-[r:belongs_to]->(:troop)
WITH s, collect(r) AS relationships
WHERE size(relationships) > 1
FOREACH (r IN relationships[1..] | DELETE r);

MATCH (t:troop)
WHERE t.name IN ['Alpha','Bravo','Charlie','Delta']
WITH t
ORDER BY t.name
WITH collect(t) AS troops
MATCH (s:soldier)
WITH troops,s
ORDER BY s.service_id
WITH troops,collect(s) AS soldiers
UNWIND range(0,size(soldiers)-1) AS i
WITH troops,soldiers[i] AS s,i
OPTIONAL MATCH (s)-[existing:belongs_to]->(:troop)
WITH troops,s,i,existing
WHERE existing IS NULL
WITH troops,s,i,troops[i % size(troops)] AS targetTroop
MERGE (s)-[:belongs_to]->(targetTroop);

UNWIND [
    ['Delta','TAL-OF-0001'],
    ['Alpha','TAL-OF-0005']
] AS row
MATCH (t:troop {name:row[0]})
MATCH (s:soldier {service_id:row[1]})
MERGE (t)-[:escorts]->(s);

UNWIND [
    ['Alpha','CHK-TAMBARAM','CHK-INS-RAJALI'],
    ['Bravo','CHK-ST-THOMAS-MOUNT','CHK-SULUR'],
    ['Charlie','CHK-ENNORE','CHK-WELLINGTON'],
    ['Delta','CHK-CHENGALPET','CHK-THANJAVUR']
] AS row
MATCH (t:troop {name:row[0]})
MATCH (start:checkpoint {checkpoint_code:row[1]})
MATCH (end:checkpoint {checkpoint_code:row[2]})
MERGE (t)-[:starts_at]->(start)
MERGE (t)-[:ends_at]->(end);

UNWIND [
    {name:'a1',type:'attack',severity:1,checkpoint:'CHK-CHENGALPET'},
    {name:'b1',type:'block',severity:3,checkpoint:'CHK-THANJAVUR'}
] AS row
MERGE (e:threatevent {name:row.name})
SET e.type = row.type,
    e.severity = row.severity,
    e.timestamp = datetime()
WITH e,row
MATCH (c:checkpoint {checkpoint_code:row.checkpoint})
MERGE (e)-[:affects]->(c);

MATCH (:soldier)-[r:reports_to]->(:soldier)
DELETE r;

UNWIND [
    {id:'TAL-OF-0001',rank:'Colonel'},
    {id:'TAL-OF-0002',rank:'Lieutenant'},
    {id:'TAL-OF-0003',rank:'Captain'},
    {id:'TAL-OF-0004',rank:'Major'},
    {id:'TAL-OF-0005',rank:'General'},
    {id:'TAL-OF-0006',rank:'Colonel'},
    {id:'TAL-OF-0007',rank:'Lieutenant'},
    {id:'TAL-OF-0008',rank:'Captain'}
] AS row
MATCH (s:soldier {service_id:row.id})
SET s._hierarchy_rank = row.rank;

MATCH (s:soldier)
WHERE s._hierarchy_rank IS NOT NULL
WITH s
MATCH (s)-[:stationed_at]->(c:checkpoint)
MATCH (superior:soldier)-[:stationed_at]->(c)
WHERE CASE superior._hierarchy_rank
    WHEN 'General' THEN 0
    WHEN 'Colonel' THEN 1
    WHEN 'Major' THEN 2
    WHEN 'Captain' THEN 3
    WHEN 'Lieutenant' THEN 4
    ELSE 99
END
<
CASE s._hierarchy_rank
    WHEN 'General' THEN 0
    WHEN 'Colonel' THEN 1
    WHEN 'Major' THEN 2
    WHEN 'Captain' THEN 3
    WHEN 'Lieutenant' THEN 4
    ELSE 99
END
WITH s,superior
ORDER BY s.service_id, superior.service_id
WITH s,collect(superior)[0] AS superior
MERGE (s)-[:reports_to]->(superior);

MATCH (s:soldier)
REMOVE s._hierarchy_rank;
