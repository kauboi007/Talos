<script setup lang="ts">
import { computed } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';
import Icon from '@/components/common/Icon.vue';
import { dashboard, home, login, logs, personnel, routing, settings } from '@/routes';

type ViewMode = 'home' | 'dashboard' | 'personnel' | 'routing' | 'map' | 'logs' | 'settings' | 'support' | 'login';

interface Props {
    currentView: ViewMode | string;
    isOpenMobile?: boolean;
}

const props = withDefaults(defineProps<Props>(), {
    isOpenMobile: false,
});

const emit = defineEmits<{
    (e: 'openMissionModal'): void;
    (e: 'closeMobileNav'): void;
}>();

const page = usePage();

const officerAvatar =
    'https://lh3.googleusercontent.com/aida-public/AB6AXuC53vu3p9ppwOx1KwS7XoLOJ5kS3ylIluUsD_uuuCDH7s6OGfeYpQzVJ7FCrzIoQEoLnSEmZts1a5wAwwqvoLlobN848XD1s_1r2WQHAYYNj1JQD4zVfeDeGHPL-RR5GiKerTw-pZK1Fq0hxxKRFdAh1eWRNOrSy6EUQ12G6rXGlpv9HisW9H79dENO5OrF3LMkJpAVMO7bjnA27h2KaKoNwaoKR7WdV0vNoRdFvtk1R30xY9HA5cAryA';

const navItems: { view: ViewMode; label: string; icon: string; href: string }[] = [
    { view: 'home', label: 'Overview', icon: 'home', href: home().url },
    { view: 'dashboard', label: 'Dashboard', icon: 'dashboard', href: dashboard().url },
    { view: 'personnel', label: 'Personnel', icon: 'groups', href: personnel().url },
    { view: 'routing', label: 'Mission Routing', icon: 'route', href: routing().url },
    { view: 'map', label: 'Map', icon: 'map', href: '/map' },
    { view: 'logs', label: 'Logs', icon: 'database', href: logs().url },
];

const activeComponent = computed(() => page.component);

function isActive(view: ViewMode) {
    if (props.currentView === view) return true;
    if (view === 'personnel' && activeComponent.value === 'Department') return true;
    return false;
}

function closeMobileNav() {
    emit('closeMobileNav');
}
</script>

<template>
    <div>
        <div
            v-if="props.isOpenMobile"
            @click="closeMobileNav"
            class="fixed inset-0 bg-black/70 backdrop-blur-xs z-40 lg:hidden"
        ></div>

        <aside
            :class="[
                'w-[280px] h-screen fixed left-0 top-0 flex flex-col border-r border-outline-variant bg-surface z-50 transition-transform duration-300 ease-in-out',
                props.isOpenMobile ? 'translate-x-0' : '-translate-x-full lg:translate-x-0',
            ]"
        >
            <div class="p-6 border-b border-outline-variant">
                <Link
                    :href="home().url"
                    @click="closeMobileNav"
                    class="flex items-center gap-3 mb-5 text-left w-full group cursor-pointer"
                >
                    <div class="w-10 h-10 rounded-full bg-surface-container-high border border-outline-variant overflow-hidden flex-shrink-0 group-hover:border-tertiary transition-colors">
                        <img :src="officerAvatar" alt="Commanding Officer" class="w-full h-full object-cover" loading="lazy" />
                    </div>
                    <div>
                        <h1 class="text-xl font-bold text-tertiary leading-tight tracking-tight group-hover:text-tertiary-fixed transition-colors">TALOS SYSTEM</h1>
                        <p class="text-xs text-on-surface-variant font-medium">Strategic Ops Command</p>
                    </div>
                </Link>

                <button
                    @click="emit('openMissionModal')"
                    type="button"
                    class="w-full bg-tertiary text-on-tertiary py-3 px-4 rounded text-xs font-bold uppercase tracking-wider flex items-center justify-center gap-2 hover:bg-tertiary-fixed transition-colors active:scale-[0.98] shadow-[0_0_12px_rgba(255,185,95,0.25)]"
                >
                    <Icon name="bolt" size="18" />
                    <span>EXECUTE MISSION</span>
                </button>
            </div>

            <nav class="flex-1 overflow-y-auto py-2">
                <Link
                    v-for="item in navItems"
                    :key="item.view"
                    :href="item.href"
                    @click="closeMobileNav"
                    :class="[
                        'w-full flex items-center gap-4 px-4 py-3 text-sm text-left transition-colors duration-200 group',
                        isActive(item.view)
                            ? 'text-tertiary border-l-2 border-tertiary bg-surface-container-high font-semibold'
                            : 'text-on-surface-variant hover:bg-surface-variant hover:text-on-surface',
                    ]"
                >
                    <Icon :name="item.icon" size="20" />
                    <span class="font-medium tracking-wide">{{ item.label }}</span>
                </Link>
            </nav>

            <div class="border-t border-outline-variant py-2">
                <Link
                    :href="settings().url"
                    @click="closeMobileNav"
                    :class="[
                        'w-full flex items-center gap-4 px-4 py-3 text-sm text-left transition-colors duration-200',
                        isActive('settings')
                            ? 'text-tertiary bg-surface-container-high font-semibold'
                            : 'text-on-surface-variant hover:bg-surface-variant hover:text-on-surface',
                    ]"
                >
                    <Icon name="settings" size="20" />
                    <span class="font-medium">Settings</span>
                </Link>

                <button
                    type="button"
                    class="w-full flex items-center gap-4 px-4 py-3 text-sm text-left transition-colors duration-200 text-on-surface-variant hover:bg-surface-variant hover:text-on-surface"
                >
                    <Icon name="help" size="20" />
                    <span class="font-medium">Support</span>
                </button>

                <Link
                    :href="login().url"
                    @click="closeMobileNav"
                    class="w-full flex items-center gap-4 px-4 py-2.5 text-xs text-left text-outline hover:text-tertiary hover:bg-surface-variant transition-colors"
                    title="Return to secure authentication gateway"
                >
                    <Icon name="lock" size="16" />
                    <span class="font-mono-data uppercase">SEC-LOCK / LOGOUT</span>
                </Link>
            </div>
        </aside>
    </div>
</template>
