<script setup lang="ts">
import { ref, computed } from 'vue';
import type { Soldier } from '../types';
import SoldierRecordsTable from '../components/personnel/SoldierRecordsTable.vue';
import SoldierDetailPanel from '../components/personnel/SoldierDetailPanel.vue';
import Icon from '../components/common/Icon.vue';

interface Props {
  soldiers: Soldier[];
  searchQuery?: string;
}

const props = withDefaults(defineProps<Props>(), {
  searchQuery: '',
});

const emit = defineEmits<{
  (e: 'reassign', soldier: Soldier): void;
  (e: 'download', soldier: Soldier): void;
}>();

// Selected soldier (defaults to SGT Reyes, J. TX-8812 to match Image 9)
const selectedSoldierId = ref('TX-8812');
const activeFilter = ref<'ALL' | 'ACTIVE' | 'DEFER' | 'STANDBY'>('ALL');
const showFilterDropdown = ref(false);

const filteredSoldiers = computed(() => {
  let list = props.soldiers;
  if (activeFilter.value !== 'ALL') {
    list = list.filter((s) => s.deployStatus === activeFilter.value);
  }
  if (props.searchQuery.trim()) {
    const q = props.searchQuery.toLowerCase();
    list = list.filter(
      (s) =>
        s.name.toLowerCase().includes(q) ||
        s.id.toLowerCase().includes(q) ||
        s.specialization.toLowerCase().includes(q) ||
        s.unit.toLowerCase().includes(q)
    );
  }
  return list;
});

const currentSelectedSoldier = computed(() => {
  return (
    props.soldiers.find((s) => s.id === selectedSoldierId.value) ||
    props.soldiers[0]
  );
});

function handleSelectSoldier(soldier: Soldier) {
  selectedSoldierId.value = soldier.id;
}

function handleExport() {
  const json = JSON.stringify(props.soldiers, null, 2);
  const blob = new Blob([json], { type: 'application/json' });
  const url = URL.createObjectURL(blob);
  const a = document.createElement('a');
  a.href = url;
  a.download = `TALOS_PERSONNEL_DATABASE_${Date.now()}.json`;
  a.click();
  URL.revokeObjectURL(url);
}
</script>

<template>
  <div class="h-full flex flex-col bg-background">
    <!-- View Header -->
    <div class="px-4 md:px-6 py-4 border-b border-outline-variant flex flex-wrap justify-between items-end gap-3 bg-surface">
      <div>
        <h2 class="text-2xl font-bold text-on-surface">Soldier Records</h2>
        <p class="text-sm text-on-surface-variant mt-1">
          MongoDB Sync: <span class="font-mono-data text-xs text-tertiary font-bold">14ms ping</span>
        </p>
      </div>

      <div class="flex items-center gap-2 relative">
        <!-- Filter Button -->
        <div class="relative">
          <button
            @click="showFilterDropdown = !showFilterDropdown"
            type="button"
            class="px-4 py-2 bg-surface-container border border-outline-variant rounded hover:bg-surface-variant transition-colors flex items-center gap-2 text-xs font-bold uppercase tracking-wider text-on-surface"
          >
            <Icon name="filter_list" size="18" />
            <span>FILTER: {{ activeFilter }}</span>
          </button>

          <!-- Filter menu -->
          <div
            v-if="showFilterDropdown"
            class="absolute right-0 top-full mt-1 w-40 bg-surface-container-high border border-outline-variant rounded shadow-xl z-30 py-1"
          >
            <button
              v-for="status in ['ALL', 'ACTIVE', 'DEFER', 'STANDBY']"
              :key="status"
              @click="activeFilter = status as any; showFilterDropdown = false"
              type="button"
              class="w-full text-left px-3 py-1.5 text-xs font-mono-data hover:bg-tertiary/10 hover:text-tertiary uppercase transition-colors"
            >
              {{ status }}
            </button>
          </div>
        </div>

        <!-- Export Button -->
        <button
          @click="handleExport"
          type="button"
          class="px-4 py-2 bg-surface-container border border-outline-variant rounded hover:bg-surface-variant transition-colors flex items-center gap-2 text-xs font-bold uppercase tracking-wider text-on-surface"
        >
          <Icon name="download" size="18" />
          <span>EXPORT</span>
        </button>
      </div>
    </div>

    <!-- Content Area: Split View -->
    <div class="flex-1 flex flex-col xl:flex-row overflow-hidden p-4 md:p-6 gap-6">
      <!-- Left: Data Table Grid -->
      <SoldierRecordsTable
        :soldiers="filteredSoldiers"
        :selected-id="selectedSoldierId"
        @select="handleSelectSoldier"
      />

      <!-- Right: Detail Panel (Selected Soldier) -->
      <SoldierDetailPanel
        v-if="currentSelectedSoldier"
        :soldier="currentSelectedSoldier"
        @reassign="emit('reassign', $event)"
        @download="emit('download', $event)"
      />
    </div>
  </div>
</template>
