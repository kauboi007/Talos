
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>{{ isset($title) && $title ? $title . ' | ' . config('app.name') : config('app.name') }}</title>

    <link rel="stylesheet" href="{{ asset('css/app.css') }}">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=JetBrains+Mono:wght@400;500;700&display=swap"
        rel="stylesheet"
    >

    @livewireStyles
</head>

<body>
@php
    $operator = auth()->user();
    $name = $operator->name();
    $role = $operator->role;

    // Administrative users may not have a linked Soldier record.
    $rank = $operator->soldier?->rank
        ?? str($role)->replace('_', ' ')->title()->toString();

    $canDispatch = in_array($role, [
        'system_admin',
        'hq_admin',
        'base_admin',
    ], true);

    $canManagePersonnel = in_array($role, [
        'system_admin',
        'hq_admin',
        'base_admin',
    ], true);

    $canViewLogs = in_array($role, [
        'system_admin',
        'hq_admin',
        'base_admin',
    ], true);

    $canManageSettings = in_array($role, [
        'system_admin',
        'hq_admin',
    ], true);
@endphp

<div
    class="min-h-screen bg-surface text-on-surface font-sans"
    x-data="{
        sidebarOpen: false,
        missionOpen: false,
        profileOpen: false
    }"
>
    {{-- Mobile sidebar backdrop --}}
    <div
        x-cloak
        x-show="sidebarOpen"
        x-transition.opacity
        @click="sidebarOpen = false"
        class="fixed inset-0 z-40 bg-black/70 backdrop-blur-sm lg:hidden"
    ></div>

    {{-- Sidebar --}}
    <aside
        class="w-[280px] h-screen fixed left-0 top-0 flex flex-col
               border-r border-outline-variant bg-surface z-50
               transition-transform duration-300 lg:translate-x-0"
        :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'"
    >
        {{-- Brand and mission action --}}
        <div class="p-6 border-b border-outline-variant">
            <a
                href="{{ route('dashboard') }}"
                wire:navigate
                @click="sidebarOpen = false"
                class="flex items-center gap-3 mb-5 group"
            >
                <div
                    class="w-10 h-10 rounded-full bg-surface-container-high
                           border border-outline-variant flex items-center
                           justify-center group-hover:border-tertiary transition-colors"
                >
                    <x-talos-logo size="sm" />
                </div>

                <div>
                    <h1 class="text-xl font-bold text-tertiary leading-tight tracking-tight">
                        TALOS SYSTEM
                    </h1>
                    <p class="text-xs text-on-surface-variant">
                        Strategic Ops Command
                    </p>
                </div>
            </a>

            @if ($canDispatch)
                <button
                    type="button"
                    @click="missionOpen = true; sidebarOpen = false"
                    class="w-full bg-tertiary text-on-tertiary py-3 px-4 rounded
                           text-xs font-bold uppercase tracking-wider flex
                           items-center justify-center gap-2
                           hover:bg-tertiary-fixed transition-colors
                           shadow-[0_0_12px_rgba(255,185,95,0.25)]"
                >
                    <x-lucide-zap class="w-[18px] h-[18px]" />
                    Execute Mission
                </button>
            @endif
        </div>

        {{-- Primary navigation --}}
        <nav class="flex-1 overflow-y-auto py-3">
            <p class="px-5 py-2 text-[10px] font-bold uppercase
                      tracking-[0.2em] text-outline">
                Operations
            </p>

            {{-- Dashboard: available to every role --}}
            <a
                href="{{ route('dashboard') }}"
                wire:navigate
                @click="sidebarOpen = false"
                @class([
                    'flex items-center gap-4 px-4 py-3 text-sm font-semibold border-l-2 transition-colors',
                    'text-tertiary border-tertiary bg-surface-container-high' => request()->routeIs('dashboard'),
                    'text-on-surface-variant border-transparent hover:bg-surface-variant hover:text-on-surface' => !request()->routeIs('dashboard'),
                ])
            >
                <x-lucide-layout-dashboard class="w-5 h-5" />
                Dashboard
            </a>

            {{-- Profile: available to every role --}}
            @if (\Illuminate\Support\Facades\Route::has('profile'))
                <a
                    href="{{ route('profile') }}"
                    wire:navigate
                    @click="sidebarOpen = false"
                    @class([
                        'flex items-center gap-4 px-4 py-3 text-sm font-semibold border-l-2 transition-colors',
                        'text-tertiary border-tertiary bg-surface-container-high' => request()->routeIs('profile'),
                        'text-on-surface-variant border-transparent hover:bg-surface-variant hover:text-on-surface' => !request()->routeIs('profile'),
                    ])
                >
                    <x-lucide-user-round class="w-5 h-5" />
                    My Profile
                </a>
            @endif

            {{-- Administrative navigation --}}
            @if ($canManagePersonnel)
                <a
                    href="/personnel"
                    wire:navigate
                    @click="sidebarOpen = false"
                    class="flex items-center gap-4 px-4 py-3 text-sm
                           text-on-surface-variant hover:bg-surface-variant
                           hover:text-on-surface transition-colors"
                >
                    <x-lucide-users class="w-5 h-5" />
                    Personnel
                </a>
            @endif

            {{-- Mission routing --}}
            <a
                href="/routing"
                wire:navigate
                @click="sidebarOpen = false"
                class="flex items-center gap-4 px-4 py-3 text-sm
                       text-on-surface-variant hover:bg-surface-variant
                       hover:text-on-surface transition-colors"
            >
                <x-lucide-route class="w-5 h-5" />
                Mission Routing
            </a>

            @if ($canViewLogs)
                <a
                    href="/logs"
                    wire:navigate
                    @click="sidebarOpen = false"
                    class="flex items-center gap-4 px-4 py-3 text-sm
                           text-on-surface-variant hover:bg-surface-variant
                           hover:text-on-surface transition-colors"
                >
                    <x-lucide-database class="w-5 h-5" />
                    SEC-OPS Logs
                </a>
            @endif

            @if ($canManageSettings)
                <a
                    href="/settings"
                    wire:navigate
                    @click="sidebarOpen = false"
                    class="flex items-center gap-4 px-4 py-3 text-sm
                           text-on-surface-variant hover:bg-surface-variant
                           hover:text-on-surface transition-colors"
                >
                    <x-lucide-settings class="w-5 h-5" />
                    Settings
                </a>
            @endif
        </nav>

        {{-- Sidebar footer --}}
        <div class="border-t border-outline-variant p-4">
            <div class="flex items-center gap-3">
                <div
                    class="w-9 h-9 shrink-0 rounded-full bg-surface-container-high
                           border border-outline-variant flex items-center
                           justify-center text-tertiary"
                >
                    <x-lucide-shield-check class="w-4 h-4" />
                </div>

                <div class="min-w-0">
                    <p class="text-xs font-semibold text-on-surface">
                        Secure Session
                    </p>
                    <p class="text-[10px] text-on-surface-variant font-mono-data truncate">
                        {{ $name }}
                    </p>
                </div>
            </div>
        </div>
    </aside>

    {{-- Main area --}}
    <div class="min-h-screen flex flex-col lg:ml-[280px]">

        {{-- Navbar --}}
        <header
            class="fixed top-0 right-0 left-0 lg:left-[280px] z-30
                   flex items-center justify-between gap-4 px-4 md:px-6
                   h-16 bg-surface-container border-b border-outline-variant"
        >
            <div class="flex items-center gap-3 min-w-0">
                <button
                    type="button"
                    @click="sidebarOpen = !sidebarOpen"
                    class="p-2 -ml-2 lg:hidden text-on-surface-variant
                           hover:text-tertiary transition-colors"
                    aria-label="Toggle navigation"
                    :aria-expanded="sidebarOpen.toString()"
                >
                    <x-lucide-menu class="w-6 h-6" />
                </button>

                <div class="min-w-0">
                    <p class="text-xs font-bold uppercase tracking-[0.16em] text-on-surface">
                        Command Center
                    </p>
                    <p class="text-[10px] text-on-surface-variant font-mono-data">
                        TALOS / OPERATIONS
                    </p>
                </div>
            </div>

            <div class="flex items-center gap-4">
                <div class="hidden sm:flex items-center gap-2">
                    <span class="relative flex h-2 w-2">
                        <span
                            class="absolute inline-flex h-full w-full rounded-full
                                   bg-[#4ade80] opacity-60 animate-ping"
                        ></span>
                        <span class="relative inline-flex rounded-full h-2 w-2 bg-[#4ade80]"></span>
                    </span>

                    <span class="text-[10px] font-mono-data uppercase
                                 tracking-wider text-on-surface-variant">
                        Session Active
                    </span>
                </div>

                <div class="h-8 w-px bg-outline-variant hidden sm:block"></div>

                {{-- Operator profile dropdown --}}
                <div
                    class="relative"
                    @click.outside="profileOpen = false"
                    @keydown.escape.window="profileOpen = false"
                >
                    <button
                        type="button"
                        @click="profileOpen = !profileOpen"
                        class="flex items-center gap-2 sm:gap-3 rounded
                               p-1.5 hover:bg-surface-variant
                               transition-colors focus:outline-none
                               focus-visible:ring-2 focus-visible:ring-tertiary"
                        :aria-expanded="profileOpen.toString()"
                        aria-haspopup="true"
                        aria-label="Open operator menu"
                    >
                        <div
                            class="w-9 h-9 rounded-full bg-surface-container-high
                                   border border-outline-variant flex items-center
                                   justify-center text-tertiary"
                        >
                            <x-lucide-user-round class="w-5 h-5" />
                        </div>

                        <div class="hidden sm:block text-left max-w-40">
                            <p class="text-xs font-semibold text-on-surface truncate">
                                {{ $name }}
                            </p>
                            <p class="text-[10px] uppercase tracking-wider text-on-surface-variant">
                                {{ $rank }}
                            </p>
                        </div>

                        <x-lucide-chevron-down
                            class="w-4 h-4 text-on-surface-variant transition-transform"
                        />
                    </button>

                    {{-- Dropdown menu --}}
                    <div
                        x-cloak
                        x-show="profileOpen"
                        x-transition.origin.top.right
                        class="absolute right-0 top-full mt-2 w-64
                               bg-surface-container border border-outline-variant
                               shadow-xl z-50"
                    >
                        <div class="p-4 border-b border-outline-variant">
                            <p class="text-[10px] uppercase tracking-[0.18em] text-on-surface-variant mb-1">
                                Signed in as
                            </p>
                            <p class="text-sm font-semibold text-on-surface break-words">
                                {{ $name }}
                            </p>
                            <p class="text-[10px] text-on-surface-variant mt-1">
                                {{ $rank }}
                            </p>
                        </div>

                        <div class="p-2">
                            @if (\Illuminate\Support\Facades\Route::has('profile'))
                                <a
                                    href="{{ route('profile') }}"
                                    wire:navigate
                                    @click="profileOpen = false"
                                    class="w-full flex items-center gap-3 px-3 py-2.5
                                           text-sm text-on-surface-variant
                                           hover:bg-surface-variant hover:text-tertiary
                                           transition-colors"
                                >
                                    <x-lucide-user-round class="w-4 h-4" />
                                    <span>My Profile</span>
                                </a>
                            @endif

                            @if (\Illuminate\Support\Facades\Route::has('logout'))
                                <div class="border-t border-outline-variant my-2"></div>

                                <form action="{{ route('logout') }}" method="POST">
                                    @csrf
                                    @method('DELETE')

                                    <button
                                        type="submit"
                                        class="w-full flex items-center gap-3 px-3 py-2.5
                                               text-sm text-on-surface-variant
                                               hover:bg-surface-variant hover:text-tertiary
                                               transition-colors text-left"
                                    >
                                        <x-lucide-log-out class="w-4 h-4" />
                                        <span>Sign out</span>
                                    </button>
                                </form>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </header>

        {{-- Page content --}}
        <main class="flex-1 mt-16 w-full">
            {{ $slot }}
        </main>
    </div>

    {{-- Mission dialog --}}
    @if ($canDispatch)
        <div
            x-cloak
            x-show="missionOpen"
            x-transition.opacity
            @keydown.escape.window="missionOpen = false"
            class="fixed inset-0 z-[60] flex items-center justify-center
                   p-4 bg-black/70 backdrop-blur-sm"
            role="dialog"
            aria-modal="true"
            aria-labelledby="mission-dialog-title"
        >
            <div
                @click.outside="missionOpen = false"
                x-transition
                class="w-full max-w-lg bg-surface-container
                       border border-outline-variant p-6 relative"
            >
                <div class="tech-corner-tl"></div>
                <div class="tech-corner-tr"></div>

                <div class="flex items-center justify-between mb-5">
                    <h2
                        id="mission-dialog-title"
                        class="text-lg font-bold uppercase tracking-wider text-on-surface"
                    >
                        Tactical Mission Dispatch
                    </h2>

                    <button
                        type="button"
                        @click="missionOpen = false"
                        aria-label="Close dialog"
                        class="text-on-surface-variant hover:text-tertiary"
                    >
                        <x-lucide-x class="w-5 h-5" />
                    </button>
                </div>

                <p class="text-sm text-on-surface-variant mb-5">
                    Mission dispatch interface placeholder.
                    Connect mission validation and dispatch later.
                </p>

                <label
                    for="mission-name"
                    class="block text-xs font-bold uppercase tracking-wider
                           text-on-surface-variant mb-2"
                >
                    Mission Designation
                </label>

                <input
                    id="mission-name"
                    type="text"
                    placeholder="Enter mission designation"
                    class="w-full bg-surface-container-highest
                           border border-outline-variant p-3 text-sm
                           font-mono-data focus:outline-none focus:border-tertiary"
                >

                <div class="flex justify-end gap-3 mt-6">
                    <button
                        type="button"
                        @click="missionOpen = false"
                        class="px-4 py-2 border border-outline-variant
                               text-on-surface-variant text-xs font-bold uppercase"
                    >
                        Cancel
                    </button>

                    <button
                        type="button"
                        @click="missionOpen = false"
                        class="px-4 py-2 bg-tertiary text-on-tertiary
                               text-xs font-bold uppercase"
                    >
                        Close Preview
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>

@livewireScripts
</body>
</html>
