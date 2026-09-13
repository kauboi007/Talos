<script setup lang="ts">
import type { Soldier } from '../../types';
import TacticalBadge from '../common/TacticalBadge.vue';
import Icon from '../common/Icon.vue';

interface Props {
  soldiers: Soldier[];
  selectedId: string;
}

const props = defineProps<Props>();

const emit = defineEmits<{
  (e: 'select', soldier: Soldier): void;
}>();
</script>

<template>
  <div class="flex-1 flex flex-col bg-surface-container-low border border-outline-variant rounded-lg overflow-hidden">
    <!-- Table Grid Header -->
    <div class="grid grid-cols-12 gap-4 px-4 py-3 bg-surface-container border-b border-outline-variant text-xs font-bold uppercase tracking-wider text-on-surface-variant">
      <div class="col-span-1">Rank</div>
      <div class="col-span-3">Name & ID</div>
      <div class="col-span-2">Unit</div>
      <div class="col-span-3">Specialization</div>
      <div class="col-span-2">Deploy Status</div>
      <div class="col-span-1 text-right">Last CP</div>
    </div>

    <!-- Scrollable Rows Container -->
    <div class="flex-1 overflow-y-auto divide-y divide-outline-variant">
      <div
        v-for="soldier in props.soldiers"
        :key="soldier.id"
        @click="emit('select', soldier)"
        :class="[
          'grid grid-cols-12 gap-4 px-4 py-3 items-center cursor-pointer transition-colors group',
          soldier.id === props.selectedId
            ? 'bg-tertiary/10 border-l-3 border-l-tertiary font-medium'
            : 'hover:bg-surface-variant/40 border-l-3 border-l-transparent',
        ]"
      >
        <!-- Rank -->
        <div
          :class="[
            'col-span-1 font-mono-data text-xs font-bold',
            soldier.id === props.selectedId ? 'text-tertiary' : 'text-on-surface',
          ]"
        >
          {{ soldier.rank }}
        </div>

        <!-- Name & ID -->
        <div class="col-span-3 flex flex-col">
          <span
            :class="[
              'text-sm transition-colors',
              soldier.id === props.selectedId
                ? 'text-on-surface font-semibold'
                : 'text-on-surface group-hover:text-tertiary',
            ]"
          >
            {{ soldier.name }}
          </span>
          <span
            :class="[
              'font-mono-data text-xs',
              soldier.id === props.selectedId ? 'text-tertiary-fixed-dim' : 'text-on-surface-variant',
            ]"
          >
            {{ soldier.id }}
          </span>
        </div>

        <!-- Unit -->
        <div class="col-span-2 font-mono-data text-xs text-on-surface">
          {{ soldier.unit }}
        </div>

        <!-- Specialization -->
        <div class="col-span-3 text-sm text-on-surface-variant">
          {{ soldier.specialization }}
        </div>

        <!-- Deploy Status -->
        <div class="col-span-2">
          <TacticalBadge :status="soldier.deployStatus" size="sm" />
        </div>

        <!-- Last CP -->
        <div class="col-span-1 text-right font-mono-data text-xs text-on-surface-variant">
          {{ soldier.lastCp }}
        </div>
      </div>
    </div>

    <!-- Pagination Footer -->
    <div class="p-3 border-t border-outline-variant bg-surface-container flex justify-between items-center">
      <span class="font-mono-data text-xs text-on-surface-variant">
        Showing 1-{{ props.soldiers.length }} of 1,204 records
      </span>
      <div class="flex gap-1">
        <button
          type="button"
          class="w-8 h-8 flex items-center justify-center rounded hover:bg-surface-variant text-on-surface-variant transition-colors"
          title="Previous Page"
        >
          <Icon name="chevron_left" size="16" />
        </button>
        <button
          type="button"
          class="w-8 h-8 flex items-center justify-center rounded hover:bg-surface-variant text-on-surface-variant transition-colors"
          title="Next Page"
        >
          <Icon name="chevron_right" size="16" />
        </button>
      </div>
    </div>
  </div>
</template>
