<script setup lang="ts">
import type { ActiveCorridor } from '../../types';
import TacticalBadge from '../common/TacticalBadge.vue';

interface Props {
  corridors: ActiveCorridor[];
  selectedCode?: string;
}

const props = defineProps<Props>();

const emit = defineEmits<{
  (e: 'selectCorridor', corridor: ActiveCorridor): void;
}>();
</script>

<template>
  <div class="flex-1 p-4 md:p-6 overflow-y-auto">
    <h2 class="text-xs font-bold uppercase tracking-widest text-on-surface-variant mb-3">
      Active Corridors
    </h2>
    <div class="space-y-2.5">
      <div
        v-for="c in props.corridors"
        :key="c.id"
        @click="emit('selectCorridor', c)"
        :class="[
          'p-3 border rounded bg-surface-container-low cursor-pointer transition-colors',
          c.code === props.selectedCode
            ? 'border-tertiary bg-tertiary/5'
            : 'border-outline-variant hover:border-tertiary/50',
        ]"
      >
        <div class="flex justify-between items-start mb-1.5">
          <span class="font-mono-data text-xs font-bold text-on-surface">
            {{ c.code }}
          </span>
          <TacticalBadge :status="c.status" size="sm" />
        </div>
        <p class="font-mono-data text-[11px] text-on-surface-variant leading-tight">
          {{ c.description }}
        </p>
        <div class="mt-2 text-[10px] font-mono-data text-outline flex items-center justify-between border-t border-outline-variant/50 pt-1.5">
          <span>SRC: {{ c.source }}</span>
          <span>DST: {{ c.destination }}</span>
        </div>
      </div>
    </div>
  </div>
</template>
