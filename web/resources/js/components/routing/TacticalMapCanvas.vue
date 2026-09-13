<script setup lang="ts">
import { ref } from 'vue';

interface Props {
  isCalculating?: boolean;
}

const props = withDefaults(defineProps<Props>(), {
  isCalculating: false,
});

const emit = defineEmits<{
  (e: 'waypointSelected', point: string): void;
}>();

const selectedWaypoint = ref('SRC-ALPHA');

const mapBg =
  'https://lh3.googleusercontent.com/aida-public/AB6AXuAjdbA2Y0CTKbX78i93FE8zl9up1q3WrO2fPU6ZyZQmO2cWk4CMkgqrFoBehK6DwoaK6sG_AB2zbR-OERad0cQeoe6iaGFc4VAoma7JoXVOPfcaRgxBauyUm-dfinLOTEl4qOuhcIptn3JkrkmAUBNCKmHCqWs7y3DtUSzJ8uN-1AcuNM5nTo6EUIoC34WYahA2T6AEYRnxO0joeEjVWRoOteZu8PJuJDFeKAACEsSUMg4Ap_PHRxuuDQ';

function selectPoint(name: string) {
  selectedWaypoint.value = name;
  emit('waypointSelected', name);
}
</script>

<template>
  <div class="flex-1 relative overflow-hidden bg-[#0a111a] select-none h-full min-h-[450px]">
    <!-- Tactical Map Topographical Background -->
    <div
      class="absolute inset-0 bg-cover bg-center opacity-45 mix-blend-luminosity"
      :style="{ backgroundImage: `url('${mapBg}')` }"
    ></div>

    <!-- Coordinate Grid Overlay (40px) -->
    <div
      class="absolute inset-0 pointer-events-none"
      style="background-image: linear-gradient(#1E293B 1px, transparent 1px), linear-gradient(90deg, #1E293B 1px, transparent 1px); background-size: 40px 40px; opacity: 0.35;"
    ></div>

    <!-- Route Paths (SVG Vector Overlay) -->
    <svg class="absolute inset-0 w-full h-full pointer-events-none" preserveAspectRatio="none">
      <!-- Trajectory 1: Keerambur to Checkpoint 01 -->
      <path
        d="M 220 280 Q 420 140 620 420"
        fill="none"
        stroke="#ffb95f"
        stroke-width="2.5"
        class="route-dash"
      />
      <!-- Trajectory 2: Checkpoint 01 to Forward Outpost -->
      <path
        d="M 620 420 L 780 340"
        fill="none"
        stroke="#ffb95f"
        stroke-width="2.5"
        stroke-dasharray="6"
      />

      <!-- Node Circles on SVG -->
      <circle cx="220" cy="280" fill="#bec6e0" r="5" />
      <circle cx="620" cy="420" fill="#ffb95f" r="5" />
      <circle cx="780" cy="340" fill="#ffb4ab" r="5" />
    </svg>

    <!-- Source Marker: SRC-ALPHA -->
    <div
      @click="selectPoint('SRC-ALPHA')"
      class="absolute top-[280px] left-[220px] -translate-x-1/2 -translate-y-1/2 flex flex-col items-center cursor-pointer z-20 group"
    >
      <div class="w-3.5 h-3.5 bg-secondary rounded-full border-2 border-surface map-pulse group-hover:scale-125 transition-transform"></div>
      <span class="mt-1 font-mono-data text-[11px] text-secondary bg-surface/90 px-1.5 py-0.5 rounded border border-outline-variant font-bold">
        SRC-ALPHA
      </span>
      <span class="text-[9px] font-mono-data text-outline">Keerambur Hub</span>
    </div>

    <!-- Checkpoint Marker: CHK-PT-01 -->
    <div
      @click="selectPoint('CHK-PT-01')"
      class="absolute top-[420px] left-[620px] -translate-x-1/2 -translate-y-1/2 flex flex-col items-center cursor-pointer z-20 group"
    >
      <div class="w-3.5 h-3.5 bg-tertiary rounded-full border-2 border-surface map-pulse group-hover:scale-125 transition-transform"></div>
      <span class="mt-1 font-mono-data text-[11px] text-tertiary bg-surface/90 px-1.5 py-0.5 rounded border border-outline-variant font-bold">
        CHK-PT-01
      </span>
      <span class="text-[9px] font-mono-data text-outline">T.Kalathur Waypoint</span>
    </div>

    <!-- Destination Marker: DEST-GAMMA -->
    <div
      @click="selectPoint('DEST-GAMMA')"
      class="absolute top-[340px] left-[780px] -translate-x-1/2 -translate-y-1/2 flex flex-col items-center cursor-pointer z-20 group"
    >
      <div class="w-3 h-3 bg-error rounded-full border-2 border-surface group-hover:scale-125 transition-transform"></div>
      <span class="mt-1 font-mono-data text-[10px] text-error bg-surface/90 px-1 py-0.5 rounded border border-error/40 font-bold">
        DEST-GAMMA
      </span>
    </div>

    <!-- Active Conflict Zone Radial Aura -->
    <div class="absolute top-[320px] left-[680px] w-52 h-36 border border-error/60 bg-error/10 rounded-full blur-[1px] pointer-events-none animate-pulse"></div>
    <div class="absolute top-[330px] left-[710px] text-error font-mono-data text-[10px] uppercase font-bold tracking-widest bg-black/60 px-2 py-0.5 border border-error/40 rounded">
      Active Conflict Zone
    </div>

    <!-- Calculating Overlay -->
    <div
      v-if="props.isCalculating"
      class="absolute bottom-6 left-6 z-30 bg-surface-container/90 border border-tertiary px-4 py-2 flex items-center gap-3 backdrop-blur-xs"
    >
      <div class="w-2.5 h-2.5 rounded-full bg-tertiary animate-ping"></div>
      <span class="font-mono-data text-xs text-tertiary uppercase tracking-wider font-bold">
        COMPUTING CONFLICT-FREE CORRIDOR (CBS V4.1)...
      </span>
    </div>

    <!-- Coordinate HUD in bottom right -->
    <div class="absolute bottom-4 right-4 z-20 bg-surface/80 border border-outline-variant px-3 py-1.5 font-mono-data text-[11px] text-on-surface-variant flex items-center gap-4">
      <span>GRID: TAMIL-NADU-SEC-4</span>
      <span class="text-tertiary">13.0827° N, 80.2707° E</span>
    </div>
  </div>
</template>
