<script setup lang="ts">
import { ref, onMounted, onUnmounted } from 'vue';
import type { TelemetryNode } from '../../types';
import Icon from '../common/Icon.vue';

interface Props {
  nodes: TelemetryNode[];
}

const props = defineProps<Props>();

const dynamicLatencies = ref<Record<string, number>>({});

props.nodes.forEach((n) => {
  dynamicLatencies.value[n.id] = n.latencyMs;
});

let intervalId: any = null;

onMounted(() => {
  // Simulate live low latency fluctuations
  intervalId = setInterval(() => {
    props.nodes.forEach((n) => {
      const delta = (Math.random() - 0.5) * 2;
      const val = Math.max(1, Math.round(n.latencyMs + delta));
      dynamicLatencies.value[n.id] = val;
    });
  }, 3500);
});

onUnmounted(() => {
  if (intervalId) clearInterval(intervalId);
});
</script>

<template>
  <div class="bg-primary-container border border-outline-variant rounded p-4 md:p-6">
    <div class="flex justify-between items-center mb-4 border-b border-outline-variant pb-2">
      <h3 class="text-xl font-semibold text-on-surface">System Telemetry</h3>
      <Icon name="dns" size="20" class="text-on-surface-variant" />
    </div>

    <div class="space-y-4">
      <div
        v-for="node in props.nodes"
        :key="node.id"
        class="flex items-center justify-between hover:bg-surface-variant/20 p-1.5 rounded transition-colors"
      >
        <div class="flex items-center gap-3">
          <div class="w-10 h-10 rounded bg-surface-container-high flex items-center justify-center border border-outline-variant">
            <Icon :name="node.icon" size="20" class="text-secondary" />
          </div>
          <div>
            <div class="text-sm font-semibold text-on-surface">{{ node.name }}</div>
            <div class="font-mono-data text-xs text-on-surface-variant">{{ node.role }}</div>
          </div>
        </div>

        <div class="text-right">
          <div class="font-mono-data text-xs text-[#4ade80] font-bold">
            {{ dynamicLatencies[node.id] ?? node.latencyMs }}ms
          </div>
          <div class="font-mono-data text-[10px] text-on-surface-variant uppercase tracking-wider">
            Latency
          </div>
        </div>
      </div>
    </div>
  </div>
</template>
