<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title ?? 'TALOS — Strategic Operations' }}</title>

    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=JetBrains+Mono:wght@400;500;700&display=swap" rel="stylesheet">

    @livewireStyles
</head>
<body>
<div
    class="min-h-screen bg-surface text-on-surface font-sans"
    x-data="{ sidebarOpen: false, missionOpen: false, activeTab: 'Active Units' }"
>
    {{-- Mobile backdrop --}}
    <div
        x-cloak
        x-show="sidebarOpen"
        @click="sidebarOpen = false"
        class="fixed inset-0 z-40 bg-black/70 backdrop-blur-sm lg:hidden"
    ></div>

    {{-- Sidebar --}}
    <aside
        class="w-[280px] h-screen fixed left-0 top-0 flex flex-col border-r border-outline-variant bg-surface z-50 transition-transform duration-300 lg:translate-x-0"
        :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'"
    >
        <div class="p-6 border-b border-outline-variant">
            <a href="/" class="flex items-center gap-3 mb-5 group">
                <div class="w-10 h-10 rounded-full bg-surface-container-high border border-outline-variant flex items-center justify-center group-hover:border-tertiary transition-colors">
                    <x-talos-logo size="sm" />
                </div>
                <div>
                    <h1 class="text-xl font-bold text-tertiary leading-tight tracking-tight">TALOS SYSTEM</h1>
                    <p class="text-xs text-on-surface-variant">Strategic Ops Command</p>
                </div>
            </a>

            <button
                type="button"
                @click="missionOpen = true; sidebarOpen = false"
                class="w-full bg-tertiary text-on-tertiary py-3 px-4 rounded text-xs font-bold uppercase tracking-wider flex items-center justify-center gap-2 hover:bg-tertiary-fixed transition-colors shadow-[0_0_12px_rgba(255,185,95,0.25)]"
            >
                <x-lucide-zap class="w-[18px] h-[18px]" />
                Execute Mission
            </button>
        </div>

        <nav class="flex-1 overflow-y-auto py-2">
            <a href="/dashboard" class="flex items-center gap-4 px-4 py-3 text-sm text-tertiary border-l-2 border-tertiary bg-surface-container-high font-semibold">
                <x-lucide-layout-dashboard class="w-5 h-5" />
                Dashboard
            </a>

            <a href="/personnel" class="flex items-center gap-4 px-4 py-3 text-sm text-on-surface-variant hover:bg-surface-variant hover:text-on-surface transition-colors">
                <x-lucide-users class="w-5 h-5" />
                Personnel
            </a>

            <a href="/routing" class="flex items-center gap-4 px-4 py-3 text-sm text-on-surface-variant hover:bg-surface-variant hover:text-on-surface transition-colors">
                <x-lucide-route class="w-5 h-5" />
                Mission Routing
            </a>

            <a href="/logs" class="flex items-center gap-4 px-4 py-3 text-sm text-on-surface-variant hover:bg-surface-variant hover:text-on-surface transition-colors">
                <x-lucide-database class="w-5 h-5" />
                SEC-OPS Logs
            </a>

            <a href="/settings" class="flex items-center gap-4 px-4 py-3 text-sm text-on-surface-variant hover:bg-surface-variant hover:text-on-surface transition-colors">
                <x-lucide-settings class="w-5 h-5" />
                Settings
            </a>
        </nav>

        <div class="border-t border-outline-variant py-2">
            <button type="button" class="w-full flex items-center gap-4 px-4 py-3 text-sm text-left text-on-surface-variant hover:bg-surface-variant">
                <x-lucide-circle-help class="w-5 h-5" />
                Support
            </button>

            <a href="/login" class="flex items-center gap-4 px-4 py-3 text-xs text-outline hover:text-tertiary hover:bg-surface-variant">
                <x-lucide-lock class="w-4 h-4" />
                <span class="font-mono-data uppercase">Access Portal</span>
            </a>
        </div>
    </aside>

    {{-- Main area --}}
    <div class="min-h-screen flex flex-col lg:ml-[280px]">
        <header class="fixed top-0 right-0 left-0 lg:left-[280px] z-30 flex justify-between items-center gap-3 px-4 md:px-6 h-16 bg-surface-container border-b border-outline-variant">
            <div class="flex items-center gap-3 md:gap-6 min-w-0">
                <button
                    type="button"
                    @click="sidebarOpen = !sidebarOpen"
                    class="p-2 lg:hidden text-on-surface-variant hover:text-on-surface"
                    aria-label="Toggle navigation"
                >
                    <x-lucide-menu class="w-6 h-6" />
                </button>

                <a href="/" class="lg:hidden text-lg font-black tracking-tight">TALOS</a>

                <nav class="hidden xl:flex h-full items-center gap-6">
                    <button
                        type="button"
                        @click="activeTab = 'Sectors'"
                        :class="activeTab === 'Sectors' ? 'text-tertiary border-tertiary' : 'text-on-surface-variant border-transparent'"
                        class="text-xs font-bold uppercase tracking-wider h-16 border-b-2"
                    >Sectors</button>

                    <button
                        type="button"
                        @click="activeTab = 'Active Units'"
                        :class="activeTab === 'Active Units' ? 'text-tertiary border-tertiary' : 'text-on-surface-variant border-transparent'"
                        class="text-xs font-bold uppercase tracking-wider h-16 border-b-2"
                    >Active Units</button>

                    <button
                        type="button"
                        @click="activeTab = 'Telemetry'"
                        :class="activeTab === 'Telemetry' ? 'text-tertiary border-tertiary' : 'text-on-surface-variant border-transparent'"
                        class="text-xs font-bold uppercase tracking-wider h-16 border-b-2"
                    >Telemetry</button>
                </nav>

                <div class="relative w-32 sm:w-48 md:w-64">
                    <x-lucide-search class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-on-surface-variant" />
                    <input
                        type="search"
                        placeholder="Query Matrix..."
                        class="w-full bg-surface border border-outline-variant rounded pl-9 pr-3 py-2 text-on-surface font-mono-data text-xs focus:border-tertiary focus:outline-none placeholder:text-outline"
                    >
                </div>
            </div>

            <div class="flex items-center gap-2 md:gap-4">
                <div class="hidden sm:flex items-center gap-2 bg-[#0d2a1b] border border-[#1e5631] px-3 py-1 rounded">
                    <span class="w-2 h-2 rounded-full bg-[#4ade80] animate-pulse"></span>
                    <span class="font-mono-data text-xs text-[#4ade80] uppercase font-semibold">System Secure</span>
                </div>

                <button type="button" class="p-2 text-on-surface-variant hover:bg-surface-variant rounded-full relative" aria-label="Notifications">
                    <x-lucide-bell class="w-5 h-5" />
                    <span class="absolute top-1.5 right-1.5 w-2 h-2 bg-tertiary rounded-full"></span>
                </button>

                <button type="button" class="p-2 text-on-surface-variant hover:bg-surface-variant rounded-full" aria-label="Security status">
                    <x-lucide-shield-check class="w-5 h-5" />
                </button>

                <div class="hidden md:block text-right">
                    <p class="text-xs font-semibold text-on-surface">Command Operator</p>
                    <a href="/login" class="text-[10px] uppercase tracking-wider text-outline hover:text-tertiary">Access Portal</a>
                </div>
            </div>
        </header>

        <main class="flex-1 mt-16 w-full">
            {{ $slot }}
        </main>
    </div>

    {{-- Frontend-only mission dialog --}}
    <div
        x-cloak
        x-show="missionOpen"
        x-transition.opacity
        @keydown.escape.window="missionOpen = false"
        class="fixed inset-0 z-[60] flex items-center justify-center p-4 bg-black/70 backdrop-blur-sm"
        role="dialog"
        aria-modal="true"
        aria-labelledby="mission-dialog-title"
    >
        <div
            @click.outside="missionOpen = false"
            x-transition
            class="w-full max-w-lg bg-surface-container border border-outline-variant p-6 relative"
        >
            <div class="tech-corner-tl"></div>
            <div class="tech-corner-tr"></div>

            <div class="flex items-center justify-between mb-5">
                <h2 id="mission-dialog-title" class="text-lg font-bold uppercase tracking-wider text-on-surface">
                    Tactical Mission Dispatch
                </h2>
                <button type="button" @click="missionOpen = false" aria-label="Close dialog" class="text-on-surface-variant hover:text-tertiary">
                    <x-lucide-x class="w-5 h-5" />
                </button>
            </div>

            <p class="text-sm text-on-surface-variant mb-5">
                Mission dispatch interface placeholder. Connect mission validation and dispatch later.
            </p>

            <label for="mission-name" class="block text-xs font-bold uppercase tracking-wider text-on-surface-variant mb-2">
                Mission Designation
            </label>
            <input id="mission-name" type="text" placeholder="Enter mission designation" class="w-full bg-surface-container-highest border border-outline-variant p-3 text-sm font-mono-data focus:outline-none focus:border-tertiary">

            <div class="flex justify-end gap-3 mt-6">
                <button type="button" @click="missionOpen = false" class="px-4 py-2 border border-outline-variant text-on-surface-variant text-xs font-bold uppercase">
                    Cancel
                </button>
                <button type="button" @click="missionOpen = false" class="px-4 py-2 bg-tertiary text-on-tertiary text-xs font-bold uppercase">
                    Close Preview
                </button>
            </div>
        </div>
    </div>
</div>

@livewireScripts
</body>
</html>
