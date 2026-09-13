<script setup lang="ts">
import { ref } from 'vue';
import type { ViewMode, DeploymentRecord, Soldier } from '@/types';
import {
  initialMetrics,
  deploymentRecords,
  soldiersList,
  threatEvents,
  telemetryNodes,
  activeCorridors,
} from '@/data/mockData';

import AuthLayout from '@/layouts/AuthLayout.vue';
import AppLayout from '@/layouts/AppLayout.vue';

import LoginForm from '@/components/auth/LoginForm.vue';
import HomeView from '@/pages/HomeView.vue';
import DashboardView from '@/pages/DashboardView.vue';
import PersonnelView from '@/pages/PersonnelView.vue';
import RoutingView from '@/pages/RoutingView.vue';
import LogsView from '@/pages/LogsView.vue';
import SettingsView from '@/pages/SettingsView.vue';

// Default to 'home' overview so the command center home page is immediately visible
const currentView = ref<ViewMode>('home');
const searchQuery = ref('');

const metricsData = ref(initialMetrics);
const deploymentData = ref(deploymentRecords);
const soldiersData = ref(soldiersList);
const threatEventsData = ref(threatEvents);
const telemetryNodesData = ref(telemetryNodes);
const corridorsData = ref(activeCorridors);

function handleLoginSuccess() {
  currentView.value = 'dashboard';
}

function handleNavigate(view: ViewMode) {
  currentView.value = view;
}

function handleSectorSelected(record: DeploymentRecord) {
  searchQuery.value = record.sector;
  currentView.value = 'personnel';
}

function handleMissionExecuted(payload: { code: string; sector: string }) {
  // Update metrics
  metricsData.value.activeMissions.count += 1;
  metricsData.value.activeMissions.delta = '+3 from 0800';

  // Add a new event in threat log
  threatEventsData.value.unshift({
    id: `threat-${Date.now()}`,
    timeZulu: 'JUST NOW',
    severity: 'ELEVATED',
    description: `TACTICAL ORDERS EXECUTED: ${payload.code} dispatched to ${payload.sector}. Tracking telemetry active.`,
  });

  // Add corridor
  corridorsData.value.unshift({
    id: `corridor-${Date.now()}`,
    code: payload.code,
    status: 'ACTIVE',
    description: `Direct mission response in ${payload.sector}`,
    source: 'Strategic Ops Command',
    destination: payload.sector,
  });
}

function handleReassignSoldier(soldier: Soldier) {
  const s = soldiersData.value.find((item) => item.id === soldier.id);
  if (s) {
    s.deployStatus = s.deployStatus === 'ACTIVE' ? 'EN ROUTE' : 'ACTIVE';
  }
}
</script>

<template>
  <div class="relative min-h-screen bg-surface">
    <!-- View Switcher Bar for Quick Demonstration of both Login Screen and Tactical Views -->
    <div
      class="fixed bottom-2 left-2 z-50 bg-[#0d1c2d]/90 border border-outline-variant rounded px-2.5 py-1 text-[11px] font-mono-data text-on-surface-variant flex items-center gap-2 shadow-lg backdrop-blur-xs"
    >
      <span class="text-tertiary font-bold">VIEW:</span>
      <button
        @click="currentView = 'home'"
        :class="[
          'px-2 py-0.5 rounded transition-colors uppercase',
          currentView === 'home' ? 'bg-tertiary text-on-tertiary font-bold' : 'hover:text-on-surface',
        ]"
      >
        Home
      </button>
      <span class="text-outline">|</span>
      <button
        @click="currentView = 'dashboard'"
        :class="[
          'px-2 py-0.5 rounded transition-colors uppercase',
          currentView === 'dashboard' ? 'bg-tertiary text-on-tertiary font-bold' : 'hover:text-on-surface',
        ]"
      >
        Dashboard
      </button>
      <span class="text-outline">|</span>
      <button
        @click="currentView = 'personnel'"
        :class="[
          'px-2 py-0.5 rounded transition-colors uppercase',
          currentView === 'personnel' ? 'bg-tertiary text-on-tertiary font-bold' : 'hover:text-on-surface',
        ]"
      >
        Personnel
      </button>
      <span class="text-outline">|</span>
      <button
        @click="currentView = 'routing'"
        :class="[
          'px-2 py-0.5 rounded transition-colors uppercase',
          currentView === 'routing' ? 'bg-tertiary text-on-tertiary font-bold' : 'hover:text-on-surface',
        ]"
      >
        Routing Map
      </button>
      <span class="text-outline">|</span>
      <button
        @click="currentView = 'login'"
        :class="[
          'px-2 py-0.5 rounded transition-colors uppercase',
          currentView === 'login' ? 'bg-tertiary text-on-tertiary font-bold' : 'hover:text-on-surface',
        ]"
      >
        Login Screen
      </button>
    </div>

    <!-- 1. Authentication View (Image 2) -->
    <AuthLayout v-if="currentView === 'login'">
      <LoginForm @login-success="handleLoginSuccess" />
    </AuthLayout>

    <!-- 2. Authenticated Tactical Command Layout (Images 4, 6, 9) -->
    <AppLayout
      v-else
      :current-view="currentView"
      :search-query="searchQuery"
      @navigate="handleNavigate"
      @update-search="searchQuery = $event"
      @mission-executed="handleMissionExecuted"
      v-slot="{ openMissionModal }"
    >
      <!-- Home / Overview View -->
      <HomeView
        v-if="currentView === 'home'"
        :metrics="metricsData"
        :deployment-records="deploymentData"
        :threat-events="threatEventsData"
        :corridors="corridorsData"
        @navigate="handleNavigate"
        @open-mission-modal="openMissionModal"
      />

      <!-- Dashboard View -->
      <DashboardView
        v-else-if="currentView === 'dashboard'"
        :metrics="metricsData"
        :deployment-records="deploymentData"
        :telemetry-nodes="telemetryNodesData"
        :threat-events="threatEventsData"
        @navigate="handleNavigate"
        @sector-selected="handleSectorSelected"
      />

      <!-- Personnel Management View -->
      <PersonnelView
        v-else-if="currentView === 'personnel'"
        :soldiers="soldiersData"
        :search-query="searchQuery"
        @reassign="handleReassignSoldier"
      />

      <!-- Mission Routing View (Tactical Map) -->
      <RoutingView
        v-else-if="currentView === 'routing' || currentView === 'map'"
        :corridors="corridorsData"
      />

      <!-- Tactical Logs View -->
      <LogsView v-else-if="currentView === 'logs'" />

      <!-- Settings / Support View -->
      <SettingsView v-else-if="currentView === 'settings' || currentView === 'support'" />
    </AppLayout>
  </div>
</template>
