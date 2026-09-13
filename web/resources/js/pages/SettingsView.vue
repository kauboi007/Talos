<script setup lang="ts">
import { ref } from 'vue';

const uplinkVersion = ref('SECURE_V9');
const encryptionCipher = ref('AES-256-GCM-MIL');
const telemetryInterval = ref('3500');
const autoPathfinding = ref(true);
const soundAlerts = ref(true);
const savedStatus = ref(false);

function handleSave() {
  savedStatus.value = true;
  setTimeout(() => {
    savedStatus.value = false;
  }, 2500);
}
</script>

<template>
  <div class="p-4 md:p-6 max-w-4xl space-y-6">
    <div class="border-b border-outline-variant pb-4">
      <h2 class="text-2xl font-bold text-on-surface">Command Settings</h2>
      <p class="text-sm text-on-surface-variant mt-1">
        Configure SEC-OPS telemetry protocols, cryptographic ciphers, and display nodes.
      </p>
    </div>

    <div class="bg-surface-container border border-outline-variant rounded p-6 space-y-6">
      <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div>
          <label class="block text-xs uppercase font-bold tracking-wider text-on-surface-variant mb-2">
            Tactical Uplink Node
          </label>
          <input
            v-model="uplinkVersion"
            type="text"
            class="w-full bg-surface-container-highest border border-outline-variant text-on-surface font-mono-data text-sm h-11 px-3 focus:outline-none focus:border-tertiary"
          />
        </div>

        <div>
          <label class="block text-xs uppercase font-bold tracking-wider text-on-surface-variant mb-2">
            Cryptographic Cipher
          </label>
          <input
            v-model="encryptionCipher"
            type="text"
            class="w-full bg-surface-container-highest border border-outline-variant text-on-surface font-mono-data text-sm h-11 px-3 focus:outline-none focus:border-tertiary"
          />
        </div>

        <div>
          <label class="block text-xs uppercase font-bold tracking-wider text-on-surface-variant mb-2">
            Telemetry Refresh Interval (ms)
          </label>
          <input
            v-model="telemetryInterval"
            type="number"
            class="w-full bg-surface-container-highest border border-outline-variant text-on-surface font-mono-data text-sm h-11 px-3 focus:outline-none focus:border-tertiary"
          />
        </div>

        <div class="flex flex-col justify-end space-y-3">
          <label class="flex items-center gap-3 cursor-pointer text-sm font-medium text-on-surface">
            <input v-model="autoPathfinding" type="checkbox" class="w-4 h-4 text-tertiary focus:ring-0 bg-surface" />
            <span>Automatic Conflict-Based Search (CBS) Re-route</span>
          </label>
          <label class="flex items-center gap-3 cursor-pointer text-sm font-medium text-on-surface">
            <input v-model="soundAlerts" type="checkbox" class="w-4 h-4 text-tertiary focus:ring-0 bg-surface" />
            <span>Tactical Threat Alert Audio Synthesizer</span>
          </label>
        </div>
      </div>

      <div class="pt-4 border-t border-outline-variant flex items-center justify-between">
        <span v-if="savedStatus" class="text-xs font-mono-data text-[#4ade80] flex items-center gap-2">
          <span class="w-2 h-2 rounded-full bg-[#4ade80] animate-pulse"></span>
          CONFIGURATION PERSISTED TO HARDWARE MATRIX
        </span>
        <span v-else class="text-xs font-mono-data text-outline">
          LAST BACKUP: TODAY 04:18 ZULU
        </span>

        <button
          @click="handleSave"
          type="button"
          class="px-6 py-2.5 bg-tertiary text-on-tertiary font-bold text-xs uppercase tracking-widest hover:bg-tertiary-fixed transition-colors"
        >
          Save Configuration
        </button>
      </div>
    </div>
  </div>
</template>
