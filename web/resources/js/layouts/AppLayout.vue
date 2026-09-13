<script setup lang="ts">
import AlertNotification from "@/components/notifications/AlertNotification.vue";
</script>

<template>
  <div
    class="min-h-screen bg-surface text-on-surface flex flex-col font-sans selection:bg-tertiary selection:text-on-tertiary relative w-full"
  >
    <!-- Global notifications -->
    <AlertNotification />

    <!-- Ambient background -->
    <div
      class="absolute top-[5%] left-[5%] w-[45vw] h-[45vw] rounded-full blur-[140px] pointer-events-none -z-10 bg-primary/4"
    />
    <div
      class="absolute bottom-[10%] right-[5%] w-[35vw] h-[35vw] rounded-full blur-[120px] pointer-events-none -z-10 bg-secondary/6"
    />
    <div
      class="absolute top-[40%] right-[10%] w-[30vh] h-[30vh] rounded-full blur-[110px] pointer-events-none -z-10 bg-tertiary/4"
    />

    <main class="flex-1 w-full flex flex-col">
      <slot />
    </main>
  </div>
</template>
