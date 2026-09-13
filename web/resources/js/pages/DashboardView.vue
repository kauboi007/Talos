<script setup lang="ts">
import type { MetricSummary, DeploymentRecord, TelemetryNode, ThreatEvent } from '../types';
import MetricCardsGrid from '../components/dashboard/MetricCardsGrid.vue';
import DeploymentStatusTable from '../components/dashboard/DeploymentStatusTable.vue';
import SystemTelemetryWidget from '../components/dashboard/SystemTelemetryWidget.vue';
import RecentThreatEventsWidget from '../components/dashboard/RecentThreatEventsWidget.vue';

interface Props {
  metrics: MetricSummary;
  deploymentRecords: DeploymentRecord[];
  telemetryNodes: TelemetryNode[];
  threatEvents: ThreatEvent[];
}

const props = defineProps<Props>();

const emit = defineEmits<{
  (e: 'navigate', view: string): void;
  (e: 'sectorSelected', record: DeploymentRecord): void;
}>();
</script>

<template>
  <div class="p-4 md:p-6 space-y-6">
    <!-- Top 4 Metrics Row -->
    <MetricCardsGrid :metrics="props.metrics" />

    <!-- Bento Grid Layout: Deployment on left (2 cols), Telemetry & Threats on right (1 col) -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
      <!-- Main Content Left (2 Columns on LG) -->
      <div class="lg:col-span-2 space-y-6">
        <DeploymentStatusTable
          :records="props.deploymentRecords"
          @select-sector="emit('sectorSelected', $event)"
          @view-all="emit('navigate', 'personnel')"
        />
      </div>

      <!-- Right Sidebar Content -->
      <div class="space-y-6">
        <SystemTelemetryWidget :nodes="props.telemetryNodes" />
        <RecentThreatEventsWidget :events="props.threatEvents" />
      </div>
    </div>
  </div>
</template>
