<script setup lang="ts">
import type { HierarchyNode } from '../../types';
import Icon from '../common/Icon.vue';

interface Props {
  nodes: HierarchyNode[];
}

const props = defineProps<Props>();
</script>

<template>
  <div class="bg-surface-container-low border border-outline-variant rounded p-4 flex flex-col gap-3 relative overflow-hidden">
    <!-- Vertical Connecting Spine -->
    <div class="absolute left-7 top-6 bottom-6 w-[1px] bg-outline-variant z-0"></div>

    <!-- Hierarchy Nodes -->
    <div
      v-for="(node, index) in props.nodes"
      :key="node.id"
      :class="[
        'flex items-center gap-3 relative z-10',
        index === 1 ? 'ml-4' : index === 2 ? 'ml-8' : '',
      ]"
    >
      <!-- Horizontal Branch Line for indented children -->
      <div
        v-if="index > 0"
        class="absolute -left-[17px] top-1/2 w-4 h-[1px] bg-outline-variant"
      ></div>

      <!-- Node Pip / Icon Container -->
      <div
        :class="[
          'w-6 h-6 rounded-full flex items-center justify-center flex-shrink-0',
          node.role === 'current'
            ? 'bg-tertiary border border-tertiary-fixed text-surface-container-lowest'
            : node.role === 'superior'
              ? 'bg-surface border border-outline text-on-surface'
              : 'bg-surface border border-outline text-on-surface-variant opacity-70',
        ]"
      >
        <Icon :name="node.icon" size="12" />
      </div>

      <!-- Node Text Metadata -->
      <div class="flex flex-col">
        <span
          :class="[
            'font-mono-data text-xs leading-tight',
            node.role === 'current' ? 'text-tertiary font-bold' : 'text-on-surface font-medium',
          ]"
        >
          {{ node.name }}
        </span>
        <span class="text-[10px] text-outline font-bold uppercase tracking-wider">
          {{ node.title }}
        </span>
      </div>
    </div>
  </div>
</template>
