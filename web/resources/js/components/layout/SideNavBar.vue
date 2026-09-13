<script setup lang="ts">
import type { ViewMode } from '../../types';
import Icon from '../common/Icon.vue';

interface Props {
  currentView: ViewMode;
  isOpenMobile?: boolean;
}

const props = withDefaults(defineProps<Props>(), {
  isOpenMobile: false,
});

const emit = defineEmits<{
  (e: 'navigate', view: ViewMode): void;
  (e: 'openMissionModal'): void;
  (e: 'closeMobileNav'): void;
}>();

const officerAvatar =
  'https://lh3.googleusercontent.com/aida-public/AB6AXuC53vu3p9ppwOx1KwS7XoLOJ5kS3ylIluUsD_uuuCDH7s6OGfeYpQzVJ7FCrzIoQEoLnSEmZts1a5wAwwqvoLlobN848XD1s_1r2WQHAYYNj1JQD4zVfeDeGHPL-RR5GiKerTw-pZK1Fq0hxxKRFdAh1eWRNOrSy6EUQ12G6rXGlpv9HisW9H79dENO5OrF3LMkJpAVMO7bjnA27h2KaKoNwaoKR7WdV0vNoRdFvtk1R30xY9HA5cAryA';

const navItems: { view: ViewMode; label: string; icon: string }[] = [
  { view: 'home', label: 'Overview', icon: 'home' },
  { view: 'dashboard', label: 'Dashboard', icon: 'dashboard' },
  { view: 'personnel', label: 'Personnel', icon: 'groups' },
  { view: 'routing', label: 'Mission Routing', icon: 'route' },
  { view: 'map', label: 'Map', icon: 'map' },
  { view: 'logs', label: 'Logs', icon: 'database' },
];

function handleNav(view: ViewMode) {
  emit('navigate', view);
  emit('closeMobileNav');
}
</script>

<template>
  <div>
    <!-- Mobile Backdrop -->
    <div
      v-if="props.isOpenMobile"
      @click="emit('closeMobileNav')"
      class="fixed inset-0 bg-black/70 backdrop-blur-xs z-40 lg:hidden"
    ></div>

    <!-- Aside Sidebar -->
    <aside
      :class="[
        'w-[280px] h-screen fixed left-0 top-0 flex flex-col border-r border-outline-variant bg-surface z-50 transition-transform duration-300 ease-in-out',
        props.isOpenMobile ? 'translate-x-0' : '-translate-x-full lg:translate-x-0',
      ]"
    >
      <!-- Brand & Profile Header -->
      <div class="p-6 border-b border-outline-variant">
        <button
          @click="handleNav('home')"
          type="button"
          class="flex items-center gap-3 mb-5 text-left w-full group cursor-pointer"
        >
          <div class="w-10 h-10 rounded-full bg-surface-container-high border border-outline-variant overflow-hidden flex-shrink-0 group-hover:border-tertiary transition-colors">
            <img
              :src="officerAvatar"
              alt="Commanding Officer"
              class="w-full h-full object-cover"
              loading="lazy"
            />
          </div>
          <div>
            <h1 class="text-xl font-bold text-tertiary leading-tight tracking-tight group-hover:text-tertiary-fixed transition-colors">TALOS SYSTEM</h1>
            <p class="text-xs text-on-surface-variant font-medium">Strategic Ops Command</p>
          </div>
        </button>

        <button
          @click="emit('openMissionModal')"
          type="button"
          class="w-full bg-tertiary text-on-tertiary py-3 px-4 rounded text-xs font-bold uppercase tracking-wider flex items-center justify-center gap-2 hover:bg-tertiary-fixed transition-colors active:scale-[0.98] shadow-[0_0_12px_rgba(255,185,95,0.25)]"
        >
          <Icon name="bolt" size="18" />
          <span>EXECUTE MISSION</span>
        </button>
      </div>

      <!-- Main Navigation Items -->
      <nav class="flex-1 overflow-y-auto py-2">
        <button
          v-for="item in navItems"
          :key="item.view"
          @click="handleNav(item.view)"
          type="button"
          :class="[
            'w-full flex items-center gap-4 px-4 py-3 text-sm text-left transition-colors duration-200 group',
            props.currentView === item.view
              ? 'text-tertiary border-l-2 border-tertiary bg-surface-container-high font-semibold'
              : 'text-on-surface-variant hover:bg-surface-variant hover:text-on-surface',
          ]"
        >
          <Icon :name="item.icon" size="20" />
          <span class="font-medium tracking-wide">{{ item.label }}</span>
        </button>
      </nav>

      <!-- Bottom Secondary Navigation -->
      <div class="border-t border-outline-variant py-2">
        <button
          @click="handleNav('settings')"
          type="button"
          :class="[
            'w-full flex items-center gap-4 px-4 py-3 text-sm text-left transition-colors duration-200',
            props.currentView === 'settings'
              ? 'text-tertiary bg-surface-container-high font-semibold'
              : 'text-on-surface-variant hover:bg-surface-variant hover:text-on-surface',
          ]"
        >
          <Icon name="settings" size="20" />
          <span class="font-medium">Settings</span>
        </button>

        <button
          @click="handleNav('support')"
          type="button"
          :class="[
            'w-full flex items-center gap-4 px-4 py-3 text-sm text-left transition-colors duration-200',
            props.currentView === 'support'
              ? 'text-tertiary bg-surface-container-high font-semibold'
              : 'text-on-surface-variant hover:bg-surface-variant hover:text-on-surface',
          ]"
        >
          <Icon name="help" size="20" />
          <span class="font-medium">Support</span>
        </button>

        <button
          @click="handleNav('login')"
          type="button"
          class="w-full flex items-center gap-4 px-4 py-2.5 text-xs text-left text-outline hover:text-tertiary hover:bg-surface-variant transition-colors"
          title="Return to secure authentication gateway"
        >
          <Icon name="lock" size="16" />
          <span class="font-mono-data uppercase">SEC-LOCK / LOGOUT</span>
        </button>
      </div>
    </aside>
  </div>
</template>
