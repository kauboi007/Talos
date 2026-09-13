import { createInertiaApp } from '@inertiajs/vue3';

import AppLayout from '@/layouts/AppLayout.vue';
import AuthLayout from '@/layouts/AuthLayout.vue';
import CommandLayout from '@/layouts/CommandLayout.vue';

const appName = import.meta.env.VITE_APP_NAME || 'TALOS';

void createInertiaApp({
    title: (title) => (title ? `${title} - ${appName}` : appName),

    layout: (name) => {
        if (name === 'Login')
            return AuthLayout;
        else if (name === 'Home')
            return AppLayout;

        return [AppLayout, CommandLayout];
    },

    progress: {
        color: '#4B5563',
    },
});