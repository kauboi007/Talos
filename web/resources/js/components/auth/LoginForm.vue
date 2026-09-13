<script setup lang="ts">
import { ref } from 'vue';
import TalosLogo from '../common/TalosLogo.vue';
import Icon from '../common/Icon.vue';

const emit = defineEmits<{
  (e: 'login-success'): void;
}>();

const serviceId = ref('OPR-7702-ALPHA');
const authKey = ref('••••••••••••');
const isLoading = ref(false);
const errorMsg = ref('');

function handleSubmit() {
  isLoading.value = true;
  errorMsg.value = '';

  setTimeout(() => {
    isLoading.value = false;
    emit('login-success');
  }, 600);
}

function handleQuickFill() {
  serviceId.value = 'OPR-COMMANDER-01';
  authKey.value = 'SEC_KEY_VAL_99';
}
</script>

<template>
  <div class="bg-surface-container border border-outline-variant rounded-none shadow-[0_4px_24px_-4px_rgba(0,0,0,0.8)] flex flex-col relative overflow-hidden backdrop-blur-sm">
    <!-- Moving Scanline Effect -->
    <div class="scanline"></div>

    <!-- Card Header -->
    <div class="p-6 border-b border-outline-variant bg-surface-container-high/50 flex flex-col items-center justify-center relative overflow-hidden">
      <!-- Talos Shield Logo -->
      <div class="mb-4">
        <TalosLogo size="lg" />
      </div>

      <h1 class="text-2xl font-bold text-primary tracking-tighter uppercase text-center w-full">
        TALOS SYSTEM
      </h1>

      <p class="text-xs font-bold text-tertiary tracking-[0.2em] mt-2 flex items-center gap-2 font-mono-data">
        <span class="w-2 h-2 bg-tertiary rounded-full animate-pulse shadow-[0_0_8px_rgba(255,185,95,0.8)]"></span>
        RESTRICTED ACCESS
      </p>

      <!-- Decorative tech corners -->
      <div class="tech-corner-tl"></div>
      <div class="tech-corner-tr"></div>
    </div>

    <!-- Login Form Area -->
    <div class="p-6 flex flex-col gap-6">
      <!-- Warning Text -->
      <div class="bg-error-container/10 border border-error/20 p-3 flex items-start gap-3">
        <Icon name="warning" size="20" class="text-error" />
        <p class="font-mono-data text-xs text-error/90 uppercase leading-snug">
          Classified Access Only. Unauthorized attempts will be logged and traced.
        </p>
      </div>

      <form @submit.prevent="handleSubmit" class="flex flex-col gap-5">
        <!-- Service ID Field -->
        <div class="flex flex-col gap-1.5">
          <label class="text-xs font-bold uppercase tracking-wider text-on-surface-variant flex items-center justify-between" for="service-id">
            <span>Service ID</span>
            <span class="font-mono-data text-[10px] text-outline opacity-60">AUTH_REQ_01</span>
          </label>
          <div class="relative">
            <Icon name="badge" size="18" class="absolute left-3 top-1/2 -translate-y-1/2 text-outline-variant" />
            <input
              id="service-id"
              v-model="serviceId"
              type="text"
              autocomplete="off"
              spellcheck="false"
              placeholder="Enter Operator ID"
              class="w-full bg-surface-container-highest border border-outline-variant text-on-surface font-mono-data text-sm h-12 pl-10 pr-3 focus:outline-none focus:border-tertiary transition-colors placeholder:text-outline-variant"
            />
          </div>
        </div>

        <!-- Authorization Key Field -->
        <div class="flex flex-col gap-1.5">
          <label class="text-xs font-bold uppercase tracking-wider text-on-surface-variant flex items-center justify-between" for="auth-key">
            <span>Authorization Key</span>
            <span class="font-mono-data text-[10px] text-outline opacity-60">ENC_LEVEL_IV</span>
          </label>
          <div class="relative">
            <Icon name="key" size="18" class="absolute left-3 top-1/2 -translate-y-1/2 text-outline-variant" />
            <input
              id="auth-key"
              v-model="authKey"
              type="password"
              placeholder="Enter Secure Key"
              class="w-full bg-surface-container-highest border border-outline-variant text-on-surface font-mono-data text-sm h-12 pl-10 pr-3 focus:outline-none focus:border-tertiary transition-colors placeholder:text-outline-variant"
            />
          </div>
        </div>

        <!-- Submit Action Button -->
        <button
          type="submit"
          :disabled="isLoading"
          class="mt-2 w-full h-12 bg-tertiary text-on-tertiary font-bold text-xs uppercase tracking-widest flex items-center justify-center gap-2 hover:bg-tertiary-fixed transition-colors active:scale-[0.98] shadow-[0_0_12px_rgba(255,185,95,0.3)] disabled:opacity-50"
        >
          <Icon name="login" size="18" />
          <span>{{ isLoading ? 'Verifying Cipher...' : 'Execute Authentication' }}</span>
        </button>
      </form>
    </div>

    <!-- Card Footer -->
    <div class="p-4 border-t border-outline-variant bg-surface-container-lowest/50 flex flex-col gap-3">
      <div class="flex items-center justify-between font-mono-data text-xs text-on-surface-variant">
        <span class="flex items-center gap-2">
          <span class="w-1.5 h-1.5 bg-[#4ade80] rounded-full animate-pulse shadow-[0_0_6px_rgba(74,222,128,0.6)]"></span>
          SYSTEM STATUS: OPERATIONAL
        </span>
        <span class="opacity-50">NODE_7A</span>
      </div>

      <div class="text-center">
        <button
          @click="handleQuickFill"
          type="button"
          class="text-xs text-outline hover:text-tertiary font-mono-data transition-colors underline uppercase tracking-wider"
        >
          Trouble Logging In? Contact SEC-OPS (Quick Access)
        </button>
      </div>
    </div>

    <!-- Bottom decorative corners -->
    <div class="tech-corner-bl"></div>
    <div class="tech-corner-br"></div>
  </div>
</template>
