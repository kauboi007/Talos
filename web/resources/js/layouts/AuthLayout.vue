<script setup lang="ts">
// Common Layout for Authentication & Restricted Access
</script>

<template>
  <div class="bg-background text-on-surface font-sans min-h-screen w-screen overflow-x-hidden overflow-y-auto flex items-center justify-center relative crt-flicker mesh-bg selection:bg-tertiary selection:text-on-tertiary py-8 px-4">
    <!-- Ambient Radial Gradient Background -->
    <div class="absolute inset-0 bg-[radial-gradient(ellipse_at_center,_var(--tw-gradient-stops))] from-surface-container-high/25 via-background to-background pointer-events-none z-0"></div>

    <!-- Main Centered Authentication Container -->
    <main class="relative z-10 w-full max-w-[480px]">
      <slot />
    </main>

    <!-- Global Atmospheric Telemetry Overlay -->
    <div class="fixed top-4 right-4 pointer-events-none opacity-40 flex flex-col items-end gap-1 font-mono-data text-[10px] text-tertiary">
      <div>LAT_34.0522_N</div>
      <div>LON_118.2437_W</div>
      <div>UPLINK: SECURE_V9</div>
    </div>
  </div>
</template>
