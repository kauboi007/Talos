<script setup lang="ts">
import { computed } from 'vue';

interface Props {
  status: string;
  size?: 'sm' | 'md';
}

const props = withDefaults(defineProps<Props>(), {
  size: 'md',
});

const config = computed(() => {
  const s = props.status.toUpperCase();
  switch (s) {
    case 'ACTIVE':
    case 'OPERATIONAL':
    case 'SYSTEM SECURE':
    case 'ROUTINE':
      return {
        bg: 'bg-[#0d2a1b]',
        border: 'border-[#1e5631]',
        text: 'text-[#4ade80]',
        pip: 'bg-[#4ade80]',
        pulse: true,
      };
    case 'EN ROUTE':
    case 'EN-ROUTE':
    case 'ELEVATED':
    case 'DEFER':
      return {
        bg: 'bg-[#251400]',
        border: 'border-[#ffb95f]/40',
        text: 'text-[#ffb95f]',
        pip: 'bg-[#ffb95f]',
        pulse: false,
      };
    case 'HALTED':
    case 'CRITICAL':
    case 'ERROR':
      return {
        bg: 'bg-[#93000a]/20',
        border: 'border-[#ffb4ab]/40',
        text: 'text-[#ffb4ab]',
        pip: 'bg-[#ffb4ab]',
        pulse: true,
      };
    case 'STANDBY':
    case 'RESOLVED':
    default:
      return {
        bg: 'bg-[#122131]',
        border: 'border-[#45464d]',
        text: 'text-[#909097]',
        pip: 'bg-[#909097]',
        pulse: false,
      };
  }
});
</script>

<template>
  <div
    :class="[
      'inline-flex items-center gap-1.5 border rounded',
      config.bg,
      config.border,
      props.size === 'sm' ? 'px-1.5 py-0.5 text-[10px]' : 'px-2 py-0.5 text-xs',
    ]"
  >
    <div
      :class="[
        'w-1.5 h-1.5 rounded-full flex-shrink-0',
        config.pip,
        config.pulse ? 'animate-pulse' : '',
      ]"
    ></div>
    <span :class="['font-mono-data font-medium tracking-wide uppercase', config.text]">
      {{ props.status }}
    </span>
  </div>
</template>
