<script setup lang="ts">
import { ref } from 'vue';
import type { ViewMode } from '../../types';
import Icon from '../common/Icon.vue';

interface Props {
  currentView: ViewMode;
  searchQuery?: string;
}

const props = withDefaults(defineProps<Props>(), {
  searchQuery: '',
});

const emit = defineEmits<{
  (e: 'toggleMobileNav'): void;
  (e: 'updateSearch', query: string): void;
  (e: 'selectTab', tab: string): void;
  (e: 'navigate', view: ViewMode): void;
}>();

const activeScopeTab = ref('Active Units');
const scopeTabs = ['Sectors', 'Active Units', 'Telemetry'];

const officerAvatar =
  'https://lh3.googleusercontent.com/aida-public/AB6AXuC53vu3p9ppwOx1KwS7XoLOJ5kS3ylIluUsD_uuuCDH7s6OGfeYpQzVJ7FCrzIoQEoLnSEmZts1a5wAwwqvoLlobN848XD1s_1r2WQHAYYNj1JQD4zVfeDeGHPL-RR5GiKerTw-pZK1Fq0hxxKRFdAh1eWRNOrSy6EUQ12G6rXGlpv9HisW9H79dENO5OrF3LMkJpAVMO7bjnA27h2KaKoNwaoKR7WdV0vNoRdFvtk1R30xY9HA5cAryA';

function handleTabClick(tab: string) {
  activeScopeTab.value = tab;
  emit('selectTab', tab);
}
</script>

<template>
  <header class="fixed top-0 right-0 left-0 lg:left-[280px] z-40 flex justify-between items-center px-4 md:px-6 h-16 bg-surface-container border-b border-outline-variant">
    <!-- Left Section: Mobile Toggle, Brand & Search/Nav -->
    <div class="flex items-center gap-4 md:gap-6 h-full">
      <!-- Mobile hamburger -->
      <button
        @click="emit('toggleMobileNav')"
        class="p-2 lg:hidden text-on-surface-variant hover:text-on-surface hover:bg-surface-variant rounded transition-colors"
        aria-label="Toggle navigation"
      >
        <Icon name="menu" size="24" />
      </button>

      <!-- Brand (shown when sidebar is hidden on small screens) -->
      <button
        @click="emit('navigate', 'home')"
        type="button"
        class="lg:hidden flex items-center gap-2 text-left cursor-pointer"
      >
        <span class="text-lg font-black tracking-tighter text-on-surface">TALOS</span>
      </button>

      <!-- Scope Navigation Links (Sectors, Active Units, Telemetry) -->
      <nav class="hidden md:flex h-full items-center gap-6">
        <button
          v-for="tab in scopeTabs"
          :key="tab"
          @click="handleTabClick(tab)"
          type="button"
          :class="[
            'text-xs font-bold uppercase tracking-wider h-full flex items-center transition-colors px-1 border-b-2',
            activeScopeTab === tab
              ? 'text-tertiary border-tertiary'
              : 'text-on-surface-variant border-transparent hover:text-tertiary-fixed-dim',
          ]"
        >
          {{ tab }}
        </button>
      </nav>

      <!-- Query Matrix Search Bar (Always visible or in personnel mode) -->
      <div class="relative w-48 sm:w-64">
        <Icon name="search" size="16" class="absolute left-3 top-1/2 -translate-y-1/2 text-on-surface-variant pointer-events-none" />
        <input
          :value="props.searchQuery"
          @input="emit('updateSearch', ($event.target as HTMLInputElement).value)"
          type="text"
          placeholder="Query Matrix..."
          class="w-full bg-surface border border-outline-variant rounded pl-9 pr-3 py-1.5 text-on-surface font-mono-data text-xs focus:border-tertiary focus:outline-none transition-colors placeholder:text-outline"
        />
      </div>
    </div>

    <!-- Right Section: Security Badge & Action Icons -->
    <div class="flex items-center gap-3 md:gap-4">
      <!-- System Secure Badge -->
      <div class="hidden sm:flex items-center gap-2 bg-[#0d2a1b] border border-[#1e5631] px-3 py-1 rounded">
        <div class="w-2 h-2 rounded-full bg-[#4ade80] animate-pulse shadow-[0_0_6px_rgba(74,222,128,0.6)]"></div>
        <span class="font-mono-data text-xs text-[#4ade80] tracking-wider uppercase font-semibold">
          SYSTEM SECURE
        </span>
      </div>

      <!-- Notifications & Shield -->
      <div class="flex items-center gap-1 text-on-surface-variant">
        <button
          class="p-2 hover:bg-surface-variant hover:text-on-surface rounded-full transition-colors active:scale-95 relative"
          title="Notifications"
        >
          <Icon name="notifications" size="20" />
          <span class="absolute top-1.5 right-1.5 w-2 h-2 bg-tertiary rounded-full animate-ping"></span>
          <span class="absolute top-1.5 right-1.5 w-2 h-2 bg-tertiary rounded-full"></span>
        </button>

        <button
          class="p-2 hover:bg-surface-variant hover:text-on-surface rounded-full transition-colors active:scale-95"
          title="Security Perimeter Verified"
        >
          <Icon name="shield" size="20" />
        </button>

        <button
          class="p-1 hover:bg-surface-variant rounded-full transition-colors active:scale-95 ml-1"
          title="Operator Profile"
        >
          <img
            :src="officerAvatar"
            alt="Commanding Officer Avatar"
            class="w-8 h-8 rounded-full object-cover border border-outline-variant"
          />
        </button>
      </div>
    </div>
  </header>
</template>
