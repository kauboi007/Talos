UNWIND [
  ['Tambaram', 'CHK-TAMBARAM'],
  ['Avadi', 'CHK-AVADI'],
  ['St Thomas Mount', 'CHK-ST-THOMAS-MOUNT'],
  ['Wellington', 'CHK-WELLINGTON'],
  ['Sulur', 'CHK-SULUR'],
  ['INS_Rajali', 'CHK-INS-RAJALI'],
  ['Ennore', 'CHK-ENNORE'],
  ['Ramanathapuram', 'CHK-RAMANATHAPURAM'],
  ['Thanjavur', 'CHK-THANJAVUR'],
  ['Chengalpet', 'CHK-CHENGALPET']
] AS row
MATCH (c:checkpoint {name: row[0]})
SET c.checkpoint_code = row[1];

CREATE CONSTRAINT soldier_service_id_unique IF NOT EXISTS
FOR (s:soldier)
REQUIRE s.service_id IS UNIQUE;

CREATE CONSTRAINT checkpoint_code_unique IF NOT EXISTS
FOR (c:checkpoint)
REQUIRE c.checkpoint_code IS UNIQUE;

UNWIND [
  {code:'CHK-TAMBARAM', name:'Tambaram', status:'open', type:'depot', lat:12.92, lng:80.13},
  {code:'CHK-AVADI', name:'Avadi', status:'open', type:'base', lat:13.11, lng:80.10},
  {code:'CHK-ST-THOMAS-MOUNT', name:'St Thomas Mount', status:'open', type:'HQ', lat:13.01, lng:80.20},
  {code:'CHK-WELLINGTON', name:'Wellington', status:'open', type:'base', lat:11.35, lng:76.79},
  {code:'CHK-SULUR', name:'Sulur', status:'open', type:'outpost', lat:11.02, lng:77.16},
  {code:'CHK-INS-RAJALI', name:'INS_Rajali', status:'open', type:'base', lat:13.09, lng:79.67},
  {code:'CHK-ENNORE', name:'Ennore', status:'open', type:'outpost', lat:13.23, lng:80.32},
  {code:'CHK-RAMANATHAPURAM', name:'Ramanathapuram', status:'open', type:'base', lat:9.37, lng:78.83},
  {code:'CHK-THANJAVUR', name:'Thanjavur', status:'open', type:'depot', lat:10.78, lng:79.10},
  {code:'CHK-CHENGALPET', name:'Chengalpet', status:'open', type:'HQ', lat:12.69, lng:79.98}
] AS row
MERGE (c:checkpoint {checkpoint_code: row.code})
SET c.name = row.name,
    c.status = row.status,
    c.type = row.type,
    c.lat = row.lat,
    c.lng = row.lng;

UNWIND [
  {id:'TAL-OF-0001', code:'CHK-TAMBARAM'},
  {id:'TAL-OF-0002', code:'CHK-AVADI'},
  {id:'TAL-OF-0003', code:'CHK-CHENGALPET'},
  {id:'TAL-OF-0004', code:'CHK-THANJAVUR'},
  {id:'TAL-OF-0005', code:'CHK-ST-THOMAS-MOUNT'},
  {id:'TAL-OF-0006', code:'CHK-SULUR'},
  {id:'TAL-OF-0007', code:'CHK-ST-THOMAS-MOUNT'},
  {id:'TAL-OF-0008', code:'CHK-ENNORE'},
  {id:'TAL-OF-0009', code:'CHK-AVADI'},
  {id:'TAL-OF-0010', code:'CHK-AVADI'},
  {id:'TAL-OF-0011', code:'CHK-AVADI'},
  {id:'TAL-OF-0012', code:'CHK-AVADI'},
  {id:'TAL-OF-0013', code:'CHK-AVADI'},
  {id:'TAL-OF-0014', code:'CHK-AVADI'},
  {id:'TAL-OF-0015', code:'CHK-AVADI'},
  {id:'TAL-OF-0016', code:'CHK-AVADI'},
  {id:'TAL-OF-0017', code:'CHK-AVADI'},
  {id:'TAL-OF-0018', code:'CHK-AVADI'},
  {id:'TAL-OF-0019', code:'CHK-AVADI'},
  {id:'TAL-OF-0020', code:'CHK-AVADI'},
  {id:'TAL-OF-0021', code:'CHK-AVADI'},
  {id:'TAL-OF-0022', code:'CHK-AVADI'},
  {id:'TAL-OF-0023', code:'CHK-AVADI'},
  {id:'TAL-OF-0024', code:'CHK-AVADI'},
  {id:'TAL-OF-0025', code:'CHK-AVADI'},
  {id:'TAL-OF-0026', code:'CHK-AVADI'},
  {id:'TAL-OF-0027', code:'CHK-AVADI'},
  {id:'TAL-OF-0028', code:'CHK-WELLINGTON'},
  {id:'TAL-OF-0029', code:'CHK-WELLINGTON'},
  {id:'TAL-OF-0030', code:'CHK-WELLINGTON'},
  {id:'TAL-OF-0031', code:'CHK-WELLINGTON'},
  {id:'TAL-OF-0032', code:'CHK-WELLINGTON'},
  {id:'TAL-OF-0033', code:'CHK-WELLINGTON'},
  {id:'TAL-OF-0034', code:'CHK-WELLINGTON'},
  {id:'TAL-OF-0035', code:'CHK-WELLINGTON'},
  {id:'TAL-OF-0036', code:'CHK-WELLINGTON'},
  {id:'TAL-OF-0037', code:'CHK-WELLINGTON'},
  {id:'TAL-OF-0038', code:'CHK-WELLINGTON'},
  {id:'TAL-OF-0039', code:'CHK-WELLINGTON'},
  {id:'TAL-OF-0040', code:'CHK-WELLINGTON'},
  {id:'TAL-OF-0041', code:'CHK-WELLINGTON'},
  {id:'TAL-OF-0042', code:'CHK-WELLINGTON'},
  {id:'TAL-OF-0043', code:'CHK-WELLINGTON'},
  {id:'TAL-OF-0044', code:'CHK-WELLINGTON'},
  {id:'TAL-OF-0045', code:'CHK-WELLINGTON'},
  {id:'TAL-OF-0046', code:'CHK-WELLINGTON'},
  {id:'TAL-OF-0047', code:'CHK-WELLINGTON'},
  {id:'TAL-OF-0048', code:'CHK-INS-RAJALI'},
  {id:'TAL-OF-0049', code:'CHK-INS-RAJALI'},
  {id:'TAL-OF-0050', code:'CHK-INS-RAJALI'},
  {id:'TAL-OF-0051', code:'CHK-INS-RAJALI'},
  {id:'TAL-OF-0052', code:'CHK-INS-RAJALI'},
  {id:'TAL-OF-0053', code:'CHK-INS-RAJALI'},
  {id:'TAL-OF-0054', code:'CHK-INS-RAJALI'},
  {id:'TAL-OF-0055', code:'CHK-INS-RAJALI'},
  {id:'TAL-OF-0056', code:'CHK-INS-RAJALI'},
  {id:'TAL-OF-0057', code:'CHK-INS-RAJALI'},
  {id:'TAL-OF-0058', code:'CHK-INS-RAJALI'},
  {id:'TAL-OF-0059', code:'CHK-INS-RAJALI'},
  {id:'TAL-OF-0060', code:'CHK-INS-RAJALI'},
  {id:'TAL-OF-0061', code:'CHK-INS-RAJALI'},
  {id:'TAL-OF-0062', code:'CHK-INS-RAJALI'},
  {id:'TAL-OF-0063', code:'CHK-INS-RAJALI'},
  {id:'TAL-OF-0064', code:'CHK-INS-RAJALI'},
  {id:'TAL-OF-0065', code:'CHK-INS-RAJALI'},
  {id:'TAL-OF-0066', code:'CHK-INS-RAJALI'},
  {id:'TAL-OF-0067', code:'CHK-INS-RAJALI'},
  {id:'TAL-OF-0068', code:'CHK-RAMANATHAPURAM'},
  {id:'TAL-OF-0069', code:'CHK-RAMANATHAPURAM'},
  {id:'TAL-OF-0070', code:'CHK-RAMANATHAPURAM'},
  {id:'TAL-OF-0071', code:'CHK-RAMANATHAPURAM'},
  {id:'TAL-OF-0072', code:'CHK-RAMANATHAPURAM'},
  {id:'TAL-OF-0073', code:'CHK-RAMANATHAPURAM'},
  {id:'TAL-OF-0074', code:'CHK-RAMANATHAPURAM'},
  {id:'TAL-OF-0075', code:'CHK-RAMANATHAPURAM'},
  {id:'TAL-OF-0076', code:'CHK-RAMANATHAPURAM'},
  {id:'TAL-OF-0077', code:'CHK-RAMANATHAPURAM'},
  {id:'TAL-OF-0078', code:'CHK-RAMANATHAPURAM'},
  {id:'TAL-OF-0079', code:'CHK-RAMANATHAPURAM'},
  {id:'TAL-OF-0080', code:'CHK-RAMANATHAPURAM'},
  {id:'TAL-OF-0081', code:'CHK-RAMANATHAPURAM'},
  {id:'TAL-OF-0082', code:'CHK-RAMANATHAPURAM'},
  {id:'TAL-OF-0083', code:'CHK-RAMANATHAPURAM'},
  {id:'TAL-OF-0084', code:'CHK-RAMANATHAPURAM'},
  {id:'TAL-OF-0085', code:'CHK-RAMANATHAPURAM'},
  {id:'TAL-OF-0086', code:'CHK-RAMANATHAPURAM'},
  {id:'TAL-OF-0087', code:'CHK-RAMANATHAPURAM'},
  {id:'TAL-OF-0088', code:'CHK-ST-THOMAS-MOUNT'},
  {id:'TAL-OF-0089', code:'CHK-ST-THOMAS-MOUNT'},
  {id:'TAL-OF-0090', code:'CHK-ST-THOMAS-MOUNT'},
  {id:'TAL-OF-0091', code:'CHK-ST-THOMAS-MOUNT'},
  {id:'TAL-OF-0092', code:'CHK-ST-THOMAS-MOUNT'},
  {id:'TAL-OF-0093', code:'CHK-ST-THOMAS-MOUNT'},
  {id:'TAL-OF-0094', code:'CHK-ST-THOMAS-MOUNT'},
  {id:'TAL-OF-0095', code:'CHK-ST-THOMAS-MOUNT'},
  {id:'TAL-OF-0096', code:'CHK-ST-THOMAS-MOUNT'},
  {id:'TAL-OF-0097', code:'CHK-ST-THOMAS-MOUNT'},
  {id:'TAL-OF-0098', code:'CHK-ST-THOMAS-MOUNT'},
  {id:'TAL-OF-0099', code:'CHK-ST-THOMAS-MOUNT'},
  {id:'TAL-OF-0100', code:'CHK-ST-THOMAS-MOUNT'},
  {id:'TAL-OF-0101', code:'CHK-ST-THOMAS-MOUNT'},
  {id:'TAL-OF-0102', code:'CHK-ST-THOMAS-MOUNT'},
  {id:'TAL-OF-0103', code:'CHK-ST-THOMAS-MOUNT'},
  {id:'TAL-OF-0104', code:'CHK-ST-THOMAS-MOUNT'},
  {id:'TAL-OF-0105', code:'CHK-ST-THOMAS-MOUNT'},
  {id:'TAL-OF-0106', code:'CHK-CHENGALPET'},
  {id:'TAL-OF-0107', code:'CHK-CHENGALPET'},
  {id:'TAL-OF-0108', code:'CHK-CHENGALPET'},
  {id:'TAL-OF-0109', code:'CHK-CHENGALPET'},
  {id:'TAL-OF-0110', code:'CHK-CHENGALPET'},
  {id:'TAL-OF-0111', code:'CHK-CHENGALPET'},
  {id:'TAL-OF-0112', code:'CHK-CHENGALPET'},
  {id:'TAL-OF-0113', code:'CHK-CHENGALPET'},
  {id:'TAL-OF-0114', code:'CHK-CHENGALPET'},
  {id:'TAL-OF-0115', code:'CHK-CHENGALPET'},
  {id:'TAL-OF-0116', code:'CHK-CHENGALPET'},
  {id:'TAL-OF-0117', code:'CHK-CHENGALPET'},
  {id:'TAL-OF-0118', code:'CHK-CHENGALPET'},
  {id:'TAL-OF-0119', code:'CHK-CHENGALPET'},
  {id:'TAL-OF-0120', code:'CHK-CHENGALPET'},
  {id:'TAL-OF-0121', code:'CHK-CHENGALPET'},
  {id:'TAL-OF-0122', code:'CHK-CHENGALPET'},
  {id:'TAL-OF-0123', code:'CHK-CHENGALPET'},
  {id:'TAL-OF-0124', code:'CHK-CHENGALPET'}
] AS row
MERGE (s:soldier {service_id: row.id})
WITH s, row
MATCH (c:checkpoint {checkpoint_code: row.code})
MERGE (s)-[:stationed_at]->(c);

UNWIND [
  ['CHK-TAMBARAM', 'CHK-AVADI', 20],
  ['CHK-TAMBARAM', 'CHK-ST-THOMAS-MOUNT', 15],
  ['CHK-TAMBARAM', 'CHK-ENNORE', 35],
  ['CHK-AVADI', 'CHK-INS-RAJALI', 65],
  ['CHK-CHENGALPET', 'CHK-THANJAVUR', 280],
  ['CHK-THANJAVUR', 'CHK-RAMANATHAPURAM', 180],
  ['CHK-CHENGALPET', 'CHK-SULUR', 500],
  ['CHK-SULUR', 'CHK-WELLINGTON', 70],
  ['CHK-INS-RAJALI', 'CHK-SULUR', 350]
] AS row
MATCH (a:checkpoint {checkpoint_code: row[0]})
MATCH (b:checkpoint {checkpoint_code: row[1]})
MERGE (a)-[r:route_to]->(b)
SET r.distance = row[2];

UNWIND [
  {name:'Alpha', type:'kill', head_count:4, priority:1, status:'started'},
  {name:'Delta', type:'medic', head_count:2, priority:2, status:'started'},
  {name:'Bravo', type:'logistics', head_count:6, priority:2, status:'started'},
  {name:'Charlie', type:'kill', head_count:5, priority:1, status:'started'}
] AS row
MERGE (t:troop {name: row.name})
SET t.type = row.type,
    t.head_count = row.head_count,
    t.priority = row.priority,
    t.status = row.status;

UNWIND [
  {name:'a1', type:'attack', severity:1},
  {name:'b1', type:'block', severity:3}
] AS row
MERGE (te:threatevent {name: row.name})
SET te.type = row.type,
    te.timestamp = datetime(),
    te.severity = row.severity;

UNWIND [
  ['TAL-OF-0002', 'TAL-OF-0003'],
  ['TAL-OF-0003', 'TAL-OF-0001'],
  ['TAL-OF-0001', 'TAL-OF-0004'],
  ['TAL-OF-0006', 'TAL-OF-0004'],
  ['TAL-OF-0004', 'TAL-OF-0005'],
  ['TAL-OF-0007', 'TAL-OF-0001'],
  ['TAL-OF-0008', 'TAL-OF-0004']
] AS row
MATCH (a:soldier {service_id: row[0]})
MATCH (b:soldier {service_id: row[1]})
MERGE (a)-[:reports_to]->(b);

UNWIND [
  ['TAL-OF-0002', 'Alpha'],
  ['TAL-OF-0003', 'Delta'],
  ['TAL-OF-0007', 'Bravo'],
  ['TAL-OF-0008', 'Charlie']
] AS row
MATCH (s:soldier {service_id: row[0]})
MATCH (t:troop {name: row[1]})
MERGE (s)-[:belongs_to]->(t);

UNWIND [
  ['Delta', 'TAL-OF-0001'],
  ['Alpha', 'TAL-OF-0005']
] AS row
MATCH (t:troop {name: row[0]})
MATCH (s:soldier {service_id: row[1]})
MERGE (t)-[:escorts]->(s);

UNWIND [
  ['a1', 'CHK-CHENGALPET'],
  ['b1', 'CHK-THANJAVUR']
] AS row
MATCH (te:threatevent {name: row[0]})
MATCH (c:checkpoint {checkpoint_code: row[1]})
MERGE (te)-[:affects]->(c);

UNWIND [
  ['Alpha', 'CHK-TAMBARAM', 'starts_at'],
  ['Alpha', 'CHK-INS-RAJALI', 'ends_at'],
  ['Bravo', 'CHK-ST-THOMAS-MOUNT', 'starts_at'],
  ['Bravo', 'CHK-SULUR', 'ends_at'],
  ['Charlie', 'CHK-ENNORE', 'starts_at'],
  ['Charlie', 'CHK-WELLINGTON', 'ends_at'],
  ['Delta', 'CHK-CHENGALPET', 'starts_at'],
  ['Delta', 'CHK-THANJAVUR', 'ends_at']
] AS row
MATCH (t:troop {name: row[0]})
MATCH (c:checkpoint {checkpoint_code: row[1]})
FOREACH (_ IN CASE WHEN row[2] = 'starts_at' THEN [1] ELSE [] END |
  MERGE (t)-[:starts_at]->(c)
)
FOREACH (_ IN CASE WHEN row[2] = 'ends_at' THEN [1] ELSE [] END |
  MERGE (t)-[:ends_at]->(c)
);

MATCH (t:troop)
WHERE t.name IN ['Alpha', 'Bravo', 'Charlie', 'Delta']
WITH t
ORDER BY t.name
WITH collect(t) AS troops

MATCH (s:soldier)
WITH troops, s
ORDER BY s.service_id
WITH troops, collect(s) AS soldiers

UNWIND range(0, size(soldiers) - 1) AS i
WITH troops, soldiers[i] AS s, i

OPTIONAL MATCH (s)-[existing:belongs_to]->(:troop)
WITH troops, s, i, existing
WHERE existing IS NULL

WITH troops, s, i,
     troops[i % size(troops)] AS targetTroop

MERGE (s)-[:belongs_to]->(targetTroop);
