<script setup lang="ts">
import { onMounted, onUnmounted, ref } from 'vue';
import { router } from '@inertiajs/vue3';
import Icon from '@/components/common/Icon.vue';

type AlertType = 'success' | 'warning' | 'info' | 'error';

interface Alert {
    id: number;
    type: AlertType;
    text: string;
}

const alerts = ref<Alert[]>([]);

function addAlert(toast: { type?: AlertType; text?: string; message?: string }) {
    const id = Date.now() + Math.random();

    alerts.value.push({
        id,
        type: toast.type ?? 'info',
        text: toast.text ?? toast.message ?? '',
    });

    window.setTimeout(() => {
        alerts.value = alerts.value.filter((alert) => alert.id !== id);
    }, 4000);
}

let removeListener: (() => void) | undefined;

onMounted(() => {
    removeListener = router.on('flash', (event) => {
        const toast = event.detail.flash?.toast;
        if (toast) addAlert(toast);
    });
});

onUnmounted(() => {
    removeListener?.();
});
</script>

<template>
    <div class="fixed bottom-6 right-6 z-[200] flex flex-col gap-3 max-w-sm w-[calc(100%-3rem)] pointer-events-none">
        <transition-group name="talos-alert">
            <div
                v-for="alert in alerts"
                :key="alert.id"
                class="bg-[#0f172a] border p-4 shadow-[0_0_20px_rgba(255,185,95,0.25)] flex items-center gap-3 pointer-events-auto"
                :class="{
                    'border-[#4ade80]': alert.type === 'success',
                    'border-tertiary': alert.type === 'warning' || alert.type === 'info',
                    'border-red-500': alert.type === 'error',
                }"
            >
                <Icon
                    :name="alert.type === 'error' ? 'warning' : alert.type === 'success' ? 'check_circle' : 'bolt'"
                    size="22"
                    :class="{
                        'text-[#4ade80]': alert.type === 'success',
                        'text-tertiary': alert.type === 'warning' || alert.type === 'info',
                        'text-red-500': alert.type === 'error',
                    }"
                />

                <div class="min-w-0 flex-1">
                    <div class="text-[10px] font-mono-data text-tertiary uppercase tracking-widest font-bold">
                        TACTICAL BROADCAST
                    </div>
                    <div class="text-xs font-mono-data text-on-surface font-semibold break-words">
                        {{ alert.text }}
                    </div>
                </div>
            </div>
        </transition-group>
    </div>
</template>

<style scoped>
.talos-alert-enter-active,
.talos-alert-leave-active {
    transition: all 180ms ease;
}

.talos-alert-enter-from,
.talos-alert-leave-to {
    opacity: 0;
    transform: translateY(8px);
}
</style>
