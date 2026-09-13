<script setup lang="ts">
import { ref } from 'vue';
import type { ActiveCorridor } from '../../types';
import ActiveCorridorsList from './ActiveCorridorsList.vue';
import Icon from '../common/Icon.vue';

interface Props {
  corridors: ActiveCorridor[];
}

const props = defineProps<Props>();

const emit = defineEmits<{
  (e: 'calculateRoute', routeData: { source: string; target: string }): void;
  (e: 'corridorClicked', corridor: ActiveCorridor): void;
}>();

const sourceCoords = ref('13.0827° N, 80.2707° E');
const destinationTarget = ref('Sector 4 / T.Kalathur Forward');
const isPathfinding = ref(false);
const cbsStatus = ref('PROCESSING');
const redisAllocated = ref(42);

function handleInitPathfinding() {
  isPathfinding.value = true;
  cbsStatus.value = 'COMPUTING';

  setTimeout(() => {
    isPathfinding.value = false;
    cbsStatus.value = 'ACTIVE_LOCKED';
    redisAllocated.value = 45;
    emit('calculateRoute', {
      source: sourceCoords.value,
      target: destinationTarget.value,
    });
  }, 1200);
}
</script>

<template>
  <aside class="w-full lg:w-[360px] bg-surface-container border-l border-outline-variant flex flex-col h-full z-10 shadow-[-4px_0_15px_rgba(0,0,0,0.5)]">
    <!-- Section: Route Calculator -->
    <div class="p-4 md:p-6 border-b border-outline-variant">
      <h2 class="text-xs font-bold uppercase tracking-widest text-on-surface-variant mb-4">
        Route Calculator
      </h2>
      <div class="space-y-4">
        <div>
          <label class="block text-[10px] font-bold uppercase tracking-wider text-on-surface-variant mb-1">
            Source Coords
          </label>
          <div class="flex items-center bg-surface-variant border border-outline-variant rounded p-2 focus-within:border-tertiary">
            <Icon name="my_location" size="16" class="text-on-surface-variant mr-2" />
            <input
              v-model="sourceCoords"
              type="text"
              readonly
              class="bg-transparent border-none p-0 font-mono-data text-xs text-on-surface w-full focus:outline-none"
            />
          </div>
        </div>

        <div>
          <label class="block text-[10px] font-bold uppercase tracking-wider text-on-surface-variant mb-1">
            Destination Target
          </label>
          <div class="flex items-center bg-surface-variant border border-outline-variant rounded p-2 focus-within:border-tertiary">
            <Icon name="location_on" size="16" class="text-on-surface-variant mr-2" />
            <input
              v-model="destinationTarget"
              type="text"
              placeholder="Enter target ID or coords..."
              class="bg-transparent border-none p-0 font-mono-data text-xs text-on-surface w-full focus:outline-none placeholder:text-outline"
            />
          </div>
        </div>

        <button
          @click="handleInitPathfinding"
          :disabled="isPathfinding"
          type="button"
          class="w-full mt-2 py-2.5 bg-transparent border border-tertiary text-tertiary text-xs font-bold uppercase tracking-widest rounded hover:bg-tertiary/10 transition-colors flex items-center justify-center gap-2 disabled:opacity-50"
        >
          <Icon name="route" size="16" />
          <span>{{ isPathfinding ? 'CALCULATING CONFLICTS...' : 'INITIALIZE PATHFINDING' }}</span>
        </button>
      </div>
    </div>

    <!-- Section: Logic Status -->
    <div class="p-4 md:p-6 border-b border-outline-variant bg-surface/50">
      <h2 class="text-xs font-bold uppercase tracking-widest text-on-surface-variant mb-3">
        Logic Status
      </h2>
      <div class="space-y-2.5">
        <div class="flex justify-between items-center font-mono-data text-xs">
          <span class="text-on-surface-variant">CBS Engine</span>
          <span class="text-tertiary font-bold">{{ cbsStatus }}</span>
        </div>
        <div class="w-full h-1.5 bg-surface-variant rounded-full overflow-hidden">
          <div
            :class="[
              'h-full bg-tertiary transition-all duration-500',
              isPathfinding ? 'w-full animate-pulse' : 'w-2/3',
            ]"
          ></div>
        </div>
        <div class="flex justify-between items-center mt-2 font-mono-data text-xs">
          <span class="text-on-surface-variant">Redis Slots</span>
          <span class="text-primary font-bold">ALLOCATED ({{ redisAllocated }}/50)</span>
        </div>
      </div>
    </div>

    <!-- Section: Active Corridors -->
    <ActiveCorridorsList
      :corridors="props.corridors"
      @select-corridor="emit('corridorClicked', $event)"
    />
  </aside>
</template>
