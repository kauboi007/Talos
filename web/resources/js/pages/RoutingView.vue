<script setup lang="ts">
import { ref } from 'vue';
import type { ActiveCorridor } from '../types';
import TacticalMapCanvas from '../components/routing/TacticalMapCanvas.vue';
import RouteCalculatorPanel from '../components/routing/RouteCalculatorPanel.vue';
import Icon from '../components/common/Icon.vue';

interface Props {
  corridors: ActiveCorridor[];
}

const props = defineProps<Props>();

const isCalculating = ref(false);
const activeMessage = ref('');

function onCalculateRoute(payload: { source: string; target: string }) {
  isCalculating.value = true;
  activeMessage.value = `Computing conflict-free arc between ${payload.source} and ${payload.target}...`;

  setTimeout(() => {
    isCalculating.value = false;
    activeMessage.value = 'CORRIDOR SECURED: Optimal trajectory calculated via CHK-PT-01.';
  }, 1400);
}

function onCorridorClicked(c: ActiveCorridor) {
  activeMessage.value = `Corridor inspected: ${c.code} (${c.status}) - ${c.description}`;
}
</script>

<template>
  <div class="h-[calc(100vh-64px)] flex flex-col lg:flex-row bg-surface-dim overflow-hidden">
    <!-- Tactical Map Area -->
    <div class="flex-1 flex flex-col relative">
      <TacticalMapCanvas
        :is-calculating="isCalculating"
        @waypoint-selected="activeMessage = `Tactical Point Selected: ${$event}`"
      />

      <!-- Notification overlay bar on map -->
      <div
        v-if="activeMessage"
        class="bg-surface-container border-t border-outline-variant px-4 py-2 text-xs font-mono-data text-tertiary flex items-center justify-between"
      >
        <span class="flex items-center gap-2">
          <span class="w-2 h-2 rounded-full bg-tertiary animate-pulse"></span>
          {{ activeMessage }}
        </span>
        <button @click="activeMessage = ''" class="text-outline hover:text-on-surface">
          <Icon name="close" size="14" />
        </button>
      </div>
    </div>

    <!-- Sidebar UI - Routing Panel -->
    <RouteCalculatorPanel
      :corridors="props.corridors"
      @calculate-route="onCalculateRoute"
      @corridor-clicked="onCorridorClicked"
    />
  </div>
</template>
