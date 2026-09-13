<script setup lang="ts">
import type { DeploymentRecord } from '../../types';
import TacticalBadge from '../common/TacticalBadge.vue';

interface Props {
  records: DeploymentRecord[];
}

const props = defineProps<Props>();

const emit = defineEmits<{
  (e: 'selectSector', record: DeploymentRecord): void;
  (e: 'viewAll'): void;
}>();
</script>

<template>
  <div class="bg-primary-container border border-outline-variant rounded p-4 md:p-6">
    <div class="flex justify-between items-center mb-4 border-b border-outline-variant pb-3">
      <h2 class="text-xl md:text-2xl font-semibold text-on-surface">
        Deployment Status
      </h2>
      <button
        @click="emit('viewAll')"
        type="button"
        class="text-tertiary text-xs font-bold uppercase tracking-wider hover:text-tertiary-fixed transition-colors"
      >
        VIEW ALL
      </button>
    </div>

    <div class="overflow-x-auto">
      <table class="w-full text-left border-collapse">
        <thead>
          <tr class="bg-surface-container-high border-b border-outline-variant">
            <th class="p-2.5 text-xs font-bold uppercase tracking-wider text-on-surface-variant">SECTOR</th>
            <th class="p-2.5 text-xs font-bold uppercase tracking-wider text-on-surface-variant">UNIT DESIGNATION</th>
            <th class="p-2.5 text-xs font-bold uppercase tracking-wider text-on-surface-variant text-right">SOLDIER RECORDS</th>
            <th class="p-2.5 text-xs font-bold uppercase tracking-wider text-on-surface-variant text-right">OFFICERS</th>
            <th class="p-2.5 text-xs font-bold uppercase tracking-wider text-on-surface-variant text-center">STATUS</th>
          </tr>
        </thead>
        <tbody>
          <tr
            v-for="row in props.records"
            :key="row.id"
            @click="emit('selectSector', row)"
            class="border-b border-outline-variant hover:bg-surface-variant/40 transition-colors cursor-pointer"
          >
            <td class="p-2.5 font-mono-data text-xs text-on-surface font-semibold">
              {{ row.sector }}
            </td>
            <td class="p-2.5 text-sm text-on-surface">
              {{ row.unitDesignation }}
            </td>
            <td class="p-2.5 font-mono-data text-xs text-on-surface-variant text-right">
              {{ row.soldierRecords }}
            </td>
            <td class="p-2.5 font-mono-data text-xs text-on-surface-variant text-right">
              {{ row.officers }}
            </td>
            <td class="p-2.5 text-center">
              <TacticalBadge :status="row.status" size="sm" />
            </td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</template>
