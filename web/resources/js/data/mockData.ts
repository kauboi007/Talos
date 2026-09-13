import type {
  Soldier,
  DeploymentRecord,
  ThreatEvent,
  TelemetryNode,
  ActiveCorridor,
  MetricSummary,
} from '../types';

export const initialMetrics: MetricSummary = {
  activeMissions: {
    count: 14,
    delta: '+2 from 0800',
  },
  troopDeployment: {
    count: 842,
    label: 'Active Personnel',
  },
  threatLevel: {
    level: 'LOW',
    sectorDetail: 'Sector 4 Clear',
  },
  checkpointStatus: {
    percentage: 98,
    label: 'Operational',
  },
};

export const deploymentRecords: DeploymentRecord[] = [
  {
    id: 'dep-1',
    sector: 'Alpha-N',
    unitDesignation: 'Vanguard 1st Btn',
    soldierRecords: 240,
    officers: 12,
    status: 'ACTIVE',
  },
  {
    id: 'dep-2',
    sector: 'Bravo-S',
    unitDesignation: 'Recon Sqn 4',
    soldierRecords: 85,
    officers: 4,
    status: 'EN ROUTE',
  },
  {
    id: 'dep-3',
    sector: 'Charlie-E',
    unitDesignation: 'Engineering Corps',
    soldierRecords: 150,
    officers: 8,
    status: 'ACTIVE',
  },
  {
    id: 'dep-4',
    sector: 'Delta-W',
    unitDesignation: 'Logistics Hub',
    soldierRecords: 320,
    officers: 15,
    status: 'ACTIVE',
  },
];

export const telemetryNodes: TelemetryNode[] = [
  {
    id: 'neo4j',
    name: 'Neo4j',
    role: 'Graph Engine',
    latencyMs: 24,
    status: 'optimal',
    icon: 'hub',
  },
  {
    id: 'mongodb',
    name: 'MongoDB',
    role: 'Document Store',
    latencyMs: 18,
    status: 'optimal',
    icon: 'storage',
  },
  {
    id: 'redis',
    name: 'Redis',
    role: 'Cache Layer',
    latencyMs: 2,
    status: 'optimal',
    icon: 'memory',
  },
];

export const threatEvents: ThreatEvent[] = [
  {
    id: 'threat-1',
    timeZulu: '10:42:15 ZULU',
    severity: 'ELEVATED',
    description: 'Unidentified drone activity detected near perimeter Gamma.',
  },
  {
    id: 'threat-2',
    timeZulu: '09:15:00 ZULU',
    severity: 'RESOLVED',
    description: 'Comms relay disruption in Sector 2. Signal restored.',
  },
  {
    id: 'threat-3',
    timeZulu: '07:30:22 ZULU',
    severity: 'ROUTINE',
    description: 'Scheduled perimeter sweep completed by Unit 4. No anomalies.',
  },
];

export const soldiersList: Soldier[] = [
  {
    id: 'TX-4429',
    name: 'Vance, K.',
    rank: 'CPL',
    rankFull: 'Corporal (E-4)',
    unit: 'Echo-4',
    specialization: 'Reconnaissance',
    deployStatus: 'DEFER',
    lastCp: '0400h',
    avatarUrl:
      'https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?w=300&auto=format&fit=crop&q=80',
    assignedUnit: 'Echo-4 Pathfinder',
    rationsLevel: 'Nominal (92%)',
    loadoutWgt: '34.2 kg',
    hierarchy: [
      {
        id: 'h-1',
        name: 'MAJ. Thorne, G.',
        title: 'Battalion Commander',
        role: 'superior',
        icon: 'star',
      },
      {
        id: 'h-2',
        name: 'CPL. Vance, K.',
        title: 'Current Selection',
        role: 'current',
        icon: 'person',
      },
      {
        id: 'h-3',
        name: 'Echo-4 Scout Team (2)',
        title: 'Direct Reports',
        role: 'subordinate',
        icon: 'group',
      },
    ],
  },
  {
    id: 'TX-8812',
    name: 'Reyes, J.',
    rank: 'SGT',
    rankFull: 'Sergeant (E-5)',
    unit: 'Alpha-1',
    specialization: 'Heavy Weapons',
    deployStatus: 'ACTIVE',
    lastCp: '0915h',
    avatarUrl:
      'https://lh3.googleusercontent.com/aida-public/AB6AXuBukXC5HXpylHpaJ1OHCQAfZerf_2cvTC7a1-5dzBlyGTTZ1gE-_7BN2ZSTHv6NPoPSfRSMa7vLvyJbWPUnhnhnwvucWbll3xloaOlB1gXRaRzDinq5XyObKey3cU396GynWCVRENKi8QHJMq-pRGnPoQHvV91hn6TWpFNyr1uWw0h6bMY5i6ygaHmomubqYRtmu3dzSsKlAttFY0E8AYybLl_-L1g_lrngqVz9gBJJZGM8in2qaEwgPA',
    assignedUnit: 'Alpha-1 Vanguard',
    rationsLevel: 'Nominal (88%)',
    loadoutWgt: '42.5 kg',
    hierarchy: [
      {
        id: 'h-1',
        name: 'CPT. Vance, L.',
        title: 'Commanding Officer',
        role: 'superior',
        icon: 'star',
      },
      {
        id: 'h-2',
        name: 'SGT. Reyes, J.',
        title: 'Current Selection',
        role: 'current',
        icon: 'person',
      },
      {
        id: 'h-3',
        name: 'Alpha-1 Squad (4)',
        title: 'Direct Reports',
        role: 'subordinate',
        icon: 'group',
      },
    ],
  },
  {
    id: 'TX-1099',
    name: 'Chen, M.',
    rank: 'PFC',
    rankFull: 'Private First Class (E-3)',
    unit: 'Bravo-2',
    specialization: 'Field Medic',
    deployStatus: 'ACTIVE',
    lastCp: '0845h',
    avatarUrl:
      'https://images.unsplash.com/photo-1534528741775-53994a69daeb?w=300&auto=format&fit=crop&q=80',
    assignedUnit: 'Bravo-2 Combat Medics',
    rationsLevel: 'Optimal (96%)',
    loadoutWgt: '28.0 kg',
    hierarchy: [
      {
        id: 'h-1',
        name: 'LT. Sterling, P.',
        title: 'Medical Officer',
        role: 'superior',
        icon: 'star',
      },
      {
        id: 'h-2',
        name: 'PFC. Chen, M.',
        title: 'Current Selection',
        role: 'current',
        icon: 'person',
      },
      {
        id: 'h-3',
        name: 'Triage Detail (2)',
        title: 'Direct Reports',
        role: 'subordinate',
        icon: 'group',
      },
    ],
  },
  {
    id: 'TX-0045',
    name: "O'Connor, S.",
    rank: 'LT',
    rankFull: 'Lieutenant (O-2)',
    unit: 'Command',
    specialization: 'Tactical C2',
    deployStatus: 'STANDBY',
    lastCp: '--',
    avatarUrl:
      'https://images.unsplash.com/photo-1500648767791-00dcc994a43e?w=300&auto=format&fit=crop&q=80',
    assignedUnit: 'Tactical Command Hub',
    rationsLevel: 'Nominal (100%)',
    loadoutWgt: '18.4 kg',
    hierarchy: [
      {
        id: 'h-1',
        name: 'COL. Mercer, R.',
        title: 'Regional Sector Director',
        role: 'superior',
        icon: 'star',
      },
      {
        id: 'h-2',
        name: "LT. O'Connor, S.",
        title: 'Current Selection',
        role: 'current',
        icon: 'person',
      },
      {
        id: 'h-3',
        name: 'Operations Relay (6)',
        title: 'Direct Reports',
        role: 'subordinate',
        icon: 'group',
      },
    ],
  },
];

export const activeCorridors: ActiveCorridor[] = [
  {
    id: 'corridor-1',
    code: 'OP-ECHO-NIGHT',
    status: 'EN-ROUTE',
    description: 'Convoy Delta passing CHK-PT-01',
    source: 'Keerambur (SRC-ALPHA)',
    destination: 'T.Kalathur (CHK-PT-01)',
  },
  {
    id: 'corridor-2',
    code: 'OP-SHADOW-FALL',
    status: 'HALTED',
    description: 'Awaiting clearance at Sector 7',
    source: 'Omandur (Sector 7)',
    destination: 'Naduvalur Forward Base',
  },
  {
    id: 'corridor-3',
    code: 'OP-IRON-AEGIS',
    status: 'ACTIVE',
    description: 'Continuous surveillance arc along Perimeter Gamma',
    source: 'Thuraiyur Node',
    destination: 'Puthanampatti Outpost',
  },
];
