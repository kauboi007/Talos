<script setup lang="ts">
import { computed, ref } from "vue";
import { usePage } from "@inertiajs/vue3";

import MissionModal from "@/components/common/MissionModal.vue";
import SideNavBar from "@/components/layout/SideNavBar.vue";
import TopAppBar from "@/components/layout/TopAppBar.vue";

const page = usePage();

const isMobileNavOpen = ref(false);
const isMissionModalOpen = ref(false);
const searchQuery = ref("");

const currentView = computed(() => {
  const component = page.component;

  if (component === "SettingsView" || component === "Settings") {
    return "settings";
  }

  if (component === "Department") {
    return "personnel";
  }

  if (component === "Home") {
    return "home";
  }

  return component.toLowerCase();
});

function handleSearch(value: string) {
  searchQuery.value = value;
}
</script>

<template>
  <div class="min-h-screen flex flex-col">
    <SideNavBar
      :current-view="currentView"
      :is-open-mobile="isMobileNavOpen"
      @open-mission-modal="isMissionModalOpen = true"
      @close-mobile-nav="isMobileNavOpen = false"
    />

    <div class="flex-1 lg:ml-[280px] flex flex-col min-h-screen">
      <TopAppBar
        :current-view="currentView"
        :search-query="searchQuery"
        @toggle-mobile-nav="isMobileNavOpen = !isMobileNavOpen"
        @update-search="handleSearch"
      />

      <main class="flex-1 mt-16 w-full">
        <slot />
      </main>
    </div>

    <MissionModal v-if="isMissionModalOpen" @close="isMissionModalOpen = false" />
  </div>
</template>
