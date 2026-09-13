<script setup lang="ts">
import { ref } from 'vue';
import Icon from './Icon.vue';

const emit = defineEmits<{
  (e: 'close'): void;
  (e: 'execute', missionData: { code: string; sector: string; authKey: string }): void;
}>();

const missionCode = ref('OP-PHOENIX-STRIKE');
const targetSector = ref('Delta-W');
const authKey = ref('');
const isExecuting = ref(false);
const executionLog = ref('');

const sectors = ['Alpha-N', 'Bravo-S', 'Charlie-E', 'Delta-W', 'Perimeter Gamma'];

function handleExecute() {
  if (!authKey.value) {
    authKey.value = 'ALPHA-909-AUTH';
  }
  isExecuting.value = true;
  executionLog.value = 'ENCRYPTING MISSION PAYLOAD...';

  setTimeout(() => {
    executionLog.value = 'DISPATCHING TO FIELD UNITS VIA SECURE_V9...';
    setTimeout(() => {
      isExecuting.value = false;
      emit('execute', {
        code: missionCode.value,
        sector: targetSector.value,
        authKey: authKey.value,
      });
      emit('close');
    }, 900);
  }, 700);
}
</script>

<template>
  <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/80 backdrop-blur-sm">
    <div class="relative w-full max-w-lg bg-[#0d1c2d] border border-outline-variant shadow-[0_4px_30px_rgba(0,0,0,0.9)] overflow-hidden">
      <!-- Tech corners -->
      <div class="tech-corner-tl"></div>
      <div class="tech-corner-tr"></div>
      <div class="tech-corner-bl"></div>
      <div class="tech-corner-br"></div>

      <!-- Header -->
      <div class="p-4 bg-[#1c2b3c]/60 border-b border-outline-variant flex items-center justify-between">
        <div class="flex items-center gap-2">
          <Icon name="bolt" size="20" class="text-tertiary" />
          <h3 class="font-bold text-lg text-primary tracking-wider uppercase">EXECUTE TACTICAL MISSION</h3>
        </div>
        <button
          @click="emit('close')"
          class="text-outline hover:text-on-surface p-1 transition-colors"
          title="Cancel"
        >
          <Icon name="close" size="16" />
        </button>
      </div>

      <!-- Content -->
      <div class="p-6 space-y-5">
        <div class="bg-error-container/15 border border-error/30 p-3 flex items-start gap-3">
          <Icon name="warning" size="20" class="text-error" />
          <p class="text-xs text-error/90 font-mono-data uppercase leading-relaxed">
            MANDATORY DISPATCH: Direct combat orders bypass standard deferral protocols. All telemetry will be logged and routed to regional commanders.
          </p>
        </div>

        <div class="space-y-4">
          <div>
            <label class="block text-xs uppercase font-bold tracking-wider text-on-surface-variant mb-1.5">
              Mission Designation
            </label>
            <input
              v-model="missionCode"
              type="text"
              class="w-full h-11 px-3 bg-[#273647] border border-outline-variant text-on-surface font-mono-data text-sm focus:border-tertiary focus:outline-none"
            />
          </div>

          <div>
            <label class="block text-xs uppercase font-bold tracking-wider text-on-surface-variant mb-1.5">
              Target Operational Sector
            </label>
            <select
              v-model="targetSector"
              class="w-full h-11 px-3 bg-[#273647] border border-outline-variant text-on-surface font-mono-data text-sm focus:border-tertiary focus:outline-none"
            >
              <option v-for="sec in sectors" :key="sec" :value="sec">{{ sec }}</option>
            </select>
          </div>

          <div>
            <div class="flex justify-between items-center mb-1.5">
              <label class="text-xs uppercase font-bold tracking-wider text-on-surface-variant">
                Authorization Key
              </label>
              <span class="text-[10px] text-outline font-mono-data">SEC_LVL_ALPHA</span>
            </div>
            <input
              v-model="authKey"
              type="password"
              placeholder="Enter encrypted operator key (or leave empty for test)"
              class="w-full h-11 px-3 bg-[#273647] border border-outline-variant text-on-surface font-mono-data text-sm focus:border-tertiary focus:outline-none"
            />
          </div>

          <div v-if="executionLog" class="p-2.5 bg-[#010f1f] border border-tertiary/40 text-tertiary font-mono-data text-xs flex items-center gap-2">
            <span class="w-2 h-2 rounded-full bg-tertiary animate-pulse"></span>
            {{ executionLog }}
          </div>
        </div>
      </div>

      <!-- Footer Buttons -->
      <div class="p-4 bg-[#010f1f] border-t border-outline-variant flex items-center justify-end gap-3">
        <button
          @click="emit('close')"
          type="button"
          class="px-4 py-2 text-xs font-bold uppercase tracking-wider text-on-surface-variant hover:text-on-surface border border-outline-variant transition-colors"
        >
          Abort
        </button>
        <button
          @click="handleExecute"
          :disabled="isExecuting"
          type="button"
          class="px-5 py-2 text-xs font-bold uppercase tracking-wider bg-tertiary text-on-tertiary hover:bg-tertiary-fixed transition-colors flex items-center gap-2 disabled:opacity-50"
        >
          <Icon name="bolt" size="16" />
          <span>{{ isExecuting ? 'Dispatching...' : 'Transmit Orders' }}</span>
        </button>
      </div>
    </div>
  </div>
</template>
