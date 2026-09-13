<script setup lang="ts">
import type { ThreatEvent } from '../../types';
import TacticalBadge from '../common/TacticalBadge.vue';
import Icon from '../common/Icon.vue';

interface Props {
  events: ThreatEvent[];
}

const props = defineProps<Props>();
</script>

<template>
  <div class="bg-primary-container border border-outline-variant rounded p-4 md:p-6">
    <div class="flex justify-between items-center mb-4 border-b border-outline-variant pb-2">
      <h3 class="text-xl font-semibold text-on-surface">Recent Threat Events</h3>
      <Icon name="warning" size="20" class="text-on-surface-variant" />
    </div>

    <ul class="space-y-3">
      <li
        v-for="event in props.events"
        :key="event.id"
        class="p-3 bg-surface border border-outline-variant rounded flex flex-col gap-2 hover:border-outline transition-colors"
      >
        <div class="flex justify-between items-start">
          <span class="font-mono-data text-xs text-on-surface-variant font-medium">
            {{ event.timeZulu }}
          </span>
          <TacticalBadge :status="event.severity" size="sm" />
        </div>
        <p class="text-sm text-on-surface leading-relaxed">
          {{ event.description }}
        </p>
      </li>
    </ul>
  </div>
</template>
