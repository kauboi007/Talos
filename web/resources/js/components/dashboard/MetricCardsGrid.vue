<script setup lang="ts">
import type { MetricSummary } from '../../types';
import Icon from '../common/Icon.vue';

interface Props {
  metrics: MetricSummary;
}

const props = defineProps<Props>();
</script>

<template>
  <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
    <!-- Card 1: Active Missions -->
    <div class="bg-primary-container border border-outline-variant rounded p-4 flex flex-col justify-between hover:bg-surface-variant/40 transition-colors">
      <div class="flex justify-between items-start mb-2">
        <span class="text-xs font-bold uppercase tracking-wider text-on-surface-variant">ACTIVE MISSIONS</span>
        <Icon name="explore" size="20" class="text-tertiary" />
      </div>
      <div class="flex items-end gap-2">
        <span class="text-4xl lg:text-5xl font-bold text-on-surface leading-none">
          {{ props.metrics.activeMissions.count }}
        </span>
        <span class="font-mono-data text-xs text-tertiary mb-1">
          {{ props.metrics.activeMissions.delta }}
        </span>
      </div>
    </div>

    <!-- Card 2: Troop Deployment -->
    <div class="bg-primary-container border border-outline-variant rounded p-4 flex flex-col justify-between hover:bg-surface-variant/40 transition-colors">
      <div class="flex justify-between items-start mb-2">
        <span class="text-xs font-bold uppercase tracking-wider text-on-surface-variant">TROOP DEPLOYMENT</span>
        <Icon name="military_tech" size="20" class="text-secondary" />
      </div>
      <div class="flex items-end gap-2">
        <span class="text-4xl lg:text-5xl font-bold text-on-surface leading-none">
          {{ props.metrics.troopDeployment.count }}
        </span>
        <span class="font-mono-data text-xs text-on-surface-variant mb-1">
          {{ props.metrics.troopDeployment.label }}
        </span>
      </div>
    </div>

    <!-- Card 3: Threat Level -->
    <div class="bg-primary-container border border-outline-variant rounded p-4 flex flex-col justify-between hover:bg-surface-variant/40 transition-colors">
      <div class="flex justify-between items-start mb-2">
        <span class="text-xs font-bold uppercase tracking-wider text-on-surface-variant">THREAT LEVEL</span>
        <Icon name="security" size="20" class="text-[#4ade80]" />
      </div>
      <div class="flex items-end gap-2">
        <span class="text-4xl lg:text-5xl font-bold text-[#4ade80] leading-none">
          {{ props.metrics.threatLevel.level }}
        </span>
        <span class="font-mono-data text-xs text-on-surface-variant mb-1">
          {{ props.metrics.threatLevel.sectorDetail }}
        </span>
      </div>
    </div>

    <!-- Card 4: Checkpoint Status -->
    <div class="bg-primary-container border border-outline-variant rounded p-4 flex flex-col justify-between hover:bg-surface-variant/40 transition-colors">
      <div class="flex justify-between items-start mb-2">
        <span class="text-xs font-bold uppercase tracking-wider text-on-surface-variant">CHECKPOINT STATUS</span>
        <Icon name="radar" size="20" class="text-secondary" />
      </div>
      <div class="flex items-end gap-2">
        <span class="text-4xl lg:text-5xl font-bold text-on-surface leading-none">
          {{ props.metrics.checkpointStatus.percentage }}%
        </span>
        <span class="font-mono-data text-xs text-on-surface-variant mb-1">
          {{ props.metrics.checkpointStatus.label }}
        </span>
      </div>
    </div>
  </div>
</template>
