<script setup lang="ts">
import { ref } from 'vue';

const logs = ref([
  { id: '1', time: '10:55:02 ZULU', service: 'AUTH_GATEWAY', message: 'Operator TX-8812 cipher handshake verified. Token SEC_V9 issued.', status: 'OK' },
  { id: '2', time: '10:48:33 ZULU', service: 'NEO4J_GRAPH', message: 'Cluster transaction #9948 completed: SGT Reyes linked to Alpha-1 squad.', status: 'OK' },
  { id: '3', time: '10:42:15 ZULU', service: 'PERIMETER_RADAR', message: 'ELEVATED ALERT: Unidentified micro-UAV radar ping near Sector Gamma.', status: 'WARN' },
  { id: '4', time: '09:20:10 ZULU', service: 'CBS_PATHFINDER', message: 'Pathfinding cycle 14 resolved: Convoy Delta routed through CHK-PT-01.', status: 'OK' },
  { id: '5', time: '09:15:00 ZULU', service: 'TELEMETRY_COMMS', message: 'Comms relay disruption in Sector 2 detected and automatically switched to auxiliary link.', status: 'RESOLVED' },
  { id: '6', time: '07:30:22 ZULU', service: 'PATROL_SWEEP', message: 'Routine sweep completed by Unit 4. Zero perimeter breaches.', status: 'OK' },
]);
</script>

<template>
  <div class="p-4 md:p-6 space-y-6">
    <div class="flex justify-between items-center border-b border-outline-variant pb-4">
      <div>
        <h2 class="text-2xl font-bold text-on-surface">Tactical System Logs</h2>
        <p class="text-sm text-on-surface-variant mt-1 font-mono-data">
          Audit Trail: Live streaming from SEC-OPS node 7A
        </p>
      </div>
      <div class="flex gap-2">
        <span class="inline-flex items-center gap-2 px-3 py-1 bg-[#0d2a1b] border border-[#1e5631] text-[#4ade80] rounded font-mono-data text-xs">
          <span class="w-2 h-2 rounded-full bg-[#4ade80] animate-pulse"></span>
          REALTIME STREAMING
        </span>
      </div>
    </div>

    <div class="bg-surface-container border border-outline-variant rounded p-4 font-mono-data text-xs space-y-2">
      <div
        v-for="log in logs"
        :key="log.id"
        class="p-2.5 border-b border-outline-variant/60 hover:bg-surface-variant/30 transition-colors flex flex-col sm:flex-row sm:items-center justify-between gap-2"
      >
        <div class="flex items-center gap-3">
          <span class="text-tertiary font-bold">{{ log.time }}</span>
          <span class="px-1.5 py-0.5 bg-surface-container-highest border border-outline-variant text-[11px] text-primary">
            {{ log.service }}
          </span>
          <span class="text-on-surface">{{ log.message }}</span>
        </div>
        <span
          :class="[
            'text-[10px] font-bold px-2 py-0.5 rounded uppercase w-fit',
            log.status === 'OK' ? 'bg-[#0d2a1b] text-[#4ade80]' : log.status === 'WARN' ? 'bg-[#251400] text-tertiary' : 'bg-surface text-outline',
          ]"
        >
          {{ log.status }}
        </span>
      </div>
    </div>
  </div>
</template>
