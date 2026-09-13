<script setup lang="ts">
import { ref } from 'vue';
import type { ViewMode } from '../types';
import SideNavBar from '../components/layout/SideNavBar.vue';
import TopAppBar from '../components/layout/TopAppBar.vue';
import MissionModal from '../components/common/MissionModal.vue';
import Icon from '../components/common/Icon.vue';

interface Props {
  currentView: ViewMode;
  searchQuery?: string;
}

const props = withDefaults(defineProps<Props>(), {
  searchQuery: '',
});

const emit = defineEmits<{
  (e: 'navigate', view: ViewMode): void;
  (e: 'updateSearch', query: string): void;
  (e: 'missionExecuted', payload: { code: string; sector: string }): void;
}>();

const isMobileNavOpen = ref(false);
const isMissionModalOpen = ref(false);
const showNotificationToast = ref(false);
const toastMessage = ref('');

function onNavigate(view: ViewMode) {
  emit('navigate', view);
}

function handleMissionExecuted(payload: { code: string; sector: string }) {
  toastMessage.value = `MISSION DISPATCHED: ${payload.code} [Sector: ${payload.sector}]`;
  showNotificationToast.value = true;
  emit('missionExecuted', payload);
  setTimeout(() => {
    showNotificationToast.value = false;
  }, 4000);
}
</script>

<template>
  <div class="min-h-screen bg-surface text-on-surface flex flex-col font-sans selection:bg-tertiary selection:text-on-tertiary">
    <!-- Side Navigation Bar -->
    <SideNavBar
      :current-view="props.currentView"
      :is-open-mobile="isMobileNavOpen"
      @navigate="onNavigate"
      @open-mission-modal="isMissionModalOpen = true"
      @close-mobile-nav="isMobileNavOpen = false"
    />

    <!-- Main Content Area -->
    <div class="flex-1 lg:ml-[280px] flex flex-col min-h-screen">
      <!-- Top Application Bar -->
      <TopAppBar
        :current-view="props.currentView"
        :search-query="props.searchQuery"
        @toggle-mobile-nav="isMobileNavOpen = !isMobileNavOpen"
        @update-search="emit('updateSearch', $event)"
        @navigate="onNavigate"
      />

      <!-- Dynamic Page Canvas -->
      <main class="flex-1 mt-16 overflow-y-auto">
        <slot :open-mission-modal="() => isMissionModalOpen = true" />
      </main>
    </div>

    <!-- Interactive Tactical Mission Modal -->
    <MissionModal
      v-if="isMissionModalOpen"
      @close="isMissionModalOpen = false"
      @execute="handleMissionExecuted"
    />

    <!-- Real-time Tactical Notification Toast -->
    <div
      v-if="showNotificationToast"
      class="fixed bottom-6 right-6 z-50 bg-[#0f172a] border border-tertiary text-on-surface p-4 shadow-[0_0_20px_rgba(255,185,95,0.4)] flex items-center gap-3 animate-bounce"
    >
      <Icon name="bolt" size="24" class="text-tertiary" />
      <div>
        <div class="text-[10px] font-mono-data text-tertiary uppercase tracking-widest font-bold">
          TACTICAL BROADCAST
        </div>
        <div class="text-xs font-mono-data text-on-surface font-semibold">
          {{ toastMessage }}
        </div>
      </div>
      <button
        @click="showNotificationToast = false"
        class="ml-2 text-outline hover:text-on-surface"
      >
        <Icon name="close" size="16" />
      </button>
    </div>
  </div>
</template>
