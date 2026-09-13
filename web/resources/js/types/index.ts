export * from './auth';

export interface MetricSummary {
    activeMissions: {
        count: number;
        delta: string;
    };

    troopDeployment: {
        count: number;
        label: string;
    };

    threatLevel: {
        level: string;
        sectorDetail: string;
    };

    checkpointStatus: {
        percentage: number;
        label: string;
    };
}

export interface DeploymentRecord {
    id: string;
    sector: string;
    unitDesignation: string;
    soldierRecords: number;
    officers: number;
    status: string;
}

export interface TelemetryNode {
    id: string;
    name: string;
    role: string;
    latencyMs: number;
    status: string;
    icon: string;
}

export interface ThreatEvent {
    id: string;
    timeZulu: string;
    severity: string;
    description: string;
}

export interface HierarchyNode {
    id: string;
    name: string;
    title: string;
    role: string;
    icon: string;
}

export interface Soldier {
    id: string;
    name: string;
    rank: string;
    rankFull: string;
    unit: string;
    specialization: string;
    deployStatus: 'ACTIVE' | 'DEFER' | 'STANDBY';
    lastCp: string;
    avatarUrl: string;
    assignedUnit: string;
    rationsLevel: string;
    loadoutWgt: string;
    hierarchy: HierarchyNode[];
}

export interface ActiveCorridor {
    id: string;
    code: string;
    status: string;
    description: string;
    source: string;
    destination: string;
}