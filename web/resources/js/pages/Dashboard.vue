<script setup lang="ts">
import { Link } from "@inertiajs/vue3";

import type {
  MetricSummary,
  DeploymentRecord,
  TelemetryNode,
  ThreatEvent,
} from "@/types";

import {
  initialMetrics,
  deploymentRecords as defaultDeploymentRecords,
  telemetryNodes as defaultTelemetryNodes,
  threatEvents as defaultThreatEvents,
} from "@/data/mockData";

import MetricCardsGrid from "@/components/dashboard/MetricCardsGrid.vue";
import DeploymentStatusTable from "@/components/dashboard/DeploymentStatusTable.vue";
import SystemTelemetryWidget from "@/components/dashboard/SystemTelemetryWidget.vue";
import RecentThreatEventsWidget from "@/components/dashboard/RecentThreatEventsWidget.vue";

interface Props {
  metrics?: MetricSummary;
  deploymentRecords?: DeploymentRecord[];
  telemetryNodes?: TelemetryNode[];
  threatEvents?: ThreatEvent[];
}

const props = withDefaults(defineProps<Props>(), {
  metrics: () => initialMetrics,
  deploymentRecords: () => defaultDeploymentRecords,
  telemetryNodes: () => defaultTelemetryNodes,
  threatEvents: () => defaultThreatEvents,
});
</script>

<template>
  <div class="p-4 md:p-6 space-y-6">
    <!-- Top 4 Metrics Row -->
    <MetricCardsGrid :metrics="props.metrics" />

    <!-- Bento Grid Layout -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
      <!-- Main Content -->
      <div class="lg:col-span-2 space-y-6">
        <DeploymentStatusTable :records="props.deploymentRecords" />

        <Link href="/personnel" class="text-sm text-primary hover:underline">
          View All Personnel
        </Link>
      </div>

      <!-- Right Sidebar -->
      <div class="space-y-6">
        <SystemTelemetryWidget :nodes="props.telemetryNodes" />

        <RecentThreatEventsWidget :events="props.threatEvents" />
      </div>
    </div>
  </div>
</template>
