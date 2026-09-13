<script setup lang="ts">
import { ref } from 'vue';
import type { Soldier } from '../../types';
import CommandHierarchyTree from './CommandHierarchyTree.vue';
import Icon from '../common/Icon.vue';

interface Props {
  soldier: Soldier;
}

const props = defineProps<Props>();

const emit = defineEmits<{
  (e: 'reassign', soldier: Soldier): void;
  (e: 'download', soldier: Soldier): void;
}>();

const actionFeedback = ref('');

function handleReassign() {
  actionFeedback.value = `REASSIGNMENT INITIATED: ${props.soldier.name} [${props.soldier.id}]`;
  emit('reassign', props.soldier);
  setTimeout(() => {
    actionFeedback.value = '';
  }, 3000);
}

function handleDownload() {
  actionFeedback.value = `DOSSIER DOWNLOADED: ${props.soldier.id}_RECORD.ENC`;
  emit('download', props.soldier);
  setTimeout(() => {
    actionFeedback.value = '';
  }, 3000);
}
</script>

<template>
  <aside class="w-full xl:w-96 flex flex-col bg-surface-container border border-outline-variant rounded-lg overflow-y-auto shadow-[0_4px_6px_-1px_rgba(0,0,0,0.5)]">
    <!-- ID Badge Header -->
    <div class="p-6 border-b border-outline-variant relative overflow-hidden bg-surface-container-low">
      <!-- Subtle background radial dots -->
      <div
        class="absolute inset-0 opacity-10 pointer-events-none"
        style="background-image: radial-gradient(#d4e4fa 1px, transparent 1px); background-size: 10px 10px;"
      ></div>

      <div class="flex gap-4 items-start relative z-10">
        <!-- Photo Container -->
        <div class="w-20 h-24 bg-surface border border-tertiary-fixed-dim rounded p-1 flex-shrink-0">
          <div class="w-full h-full bg-surface-variant rounded-xs overflow-hidden">
            <img
              :src="props.soldier.avatarUrl"
              :alt="props.soldier.name"
              class="w-full h-full object-cover grayscale opacity-90 mix-blend-screen"
            />
          </div>
        </div>

        <div class="flex flex-col flex-1">
          <div class="font-mono-data text-xs text-tertiary mb-1 font-bold">
            ID: {{ props.soldier.id }}
          </div>
          <h3 class="text-xl font-bold text-on-surface leading-tight uppercase">
            {{ props.soldier.name }}
          </h3>
          <div class="text-xs text-on-surface-variant mt-1.5 font-bold uppercase tracking-widest">
            {{ props.soldier.rankFull }}
          </div>

          <div class="mt-3 inline-flex items-center gap-1.5 px-2 py-1 bg-surface border border-[#a8c7fa]/30 rounded w-fit">
            <span class="w-2 h-2 rounded-full bg-[#a8c7fa] animate-pulse"></span>
            <span class="font-mono-data text-[11px] text-[#a8c7fa] font-bold">
              DEPLOYED - SECURE
            </span>
          </div>
        </div>
      </div>
    </div>

    <!-- Logistics Specs -->
    <div class="p-6 border-b border-outline-variant flex flex-col gap-3">
      <h4 class="text-xs font-bold uppercase tracking-wider text-on-surface-variant">
        Troop Logistics
      </h4>
      <div class="grid grid-cols-2 gap-y-4 gap-x-2">
        <div class="flex flex-col">
          <span class="text-[10px] font-bold uppercase tracking-wider text-outline">Assigned Unit</span>
          <span class="font-mono-data text-xs text-on-surface font-semibold">{{ props.soldier.assignedUnit }}</span>
        </div>
        <div class="flex flex-col">
          <span class="text-[10px] font-bold uppercase tracking-wider text-outline">Specialization</span>
          <span class="font-mono-data text-xs text-on-surface font-semibold">{{ props.soldier.specialization }}</span>
        </div>
        <div class="flex flex-col">
          <span class="text-[10px] font-bold uppercase tracking-wider text-outline">Rations Level</span>
          <span class="font-mono-data text-xs text-on-surface font-semibold">{{ props.soldier.rationsLevel }}</span>
        </div>
        <div class="flex flex-col">
          <span class="text-[10px] font-bold uppercase tracking-wider text-outline">Loadout Wgt</span>
          <span class="font-mono-data text-xs text-on-surface font-semibold">{{ props.soldier.loadoutWgt }}</span>
        </div>
      </div>
    </div>

    <!-- Officer Hierarchy (Neo4j concept visualization) -->
    <div class="p-6 border-b border-outline-variant flex-1">
      <div class="flex justify-between items-center mb-4">
        <h4 class="text-xs font-bold uppercase tracking-wider text-on-surface-variant">
          Command Hierarchy
        </h4>
        <Icon name="hub" size="16" class="text-outline" />
      </div>

      <CommandHierarchyTree :nodes="props.soldier.hierarchy" />
    </div>

    <!-- Actions Area -->
    <div class="p-6 bg-surface-container-high flex flex-col gap-3 mt-auto">
      <div v-if="actionFeedback" class="p-2 bg-[#010f1f] border border-tertiary text-tertiary font-mono-data text-[11px] text-center font-bold">
        {{ actionFeedback }}
      </div>

      <button
        @click="handleReassign"
        type="button"
        class="w-full py-2.5 bg-tertiary text-on-tertiary font-bold text-xs uppercase tracking-widest rounded hover:bg-tertiary-fixed transition-colors flex items-center justify-center gap-2"
      >
        <Icon name="swap_horiz" size="18" />
        <span>REASSIGN UNIT</span>
      </button>

      <button
        @click="handleDownload"
        type="button"
        class="w-full py-2.5 bg-transparent border border-outline-variant text-on-surface font-bold text-xs uppercase tracking-widest rounded hover:bg-surface-variant transition-colors flex items-center justify-center gap-2"
      >
        <Icon name="file_download" size="18" />
        <span>DOWNLOAD RECORD</span>
      </button>
    </div>
  </aside>
</template>
