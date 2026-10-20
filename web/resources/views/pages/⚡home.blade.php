<?php

use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Layout('layouts::app'), Title('Home')]
class extends Component {
    public array $capabilities = [
        [
            'title' => 'Conflict-Based Search (CBS)',
            'desc' => 'Automated multi-agent routing algorithms ensuring zero collision or hostile interception in transit corridors.',
            'icon' => 'radio',
        ],
        [
            'title' => 'Graph Command Hierarchy',
            'desc' => 'Dynamic graph database traversal mapping direct superior officer chains and squad logistics in real-time.',
            'icon' => 'network',
        ],
        [
            'title' => 'Classified Telemetry Uplink',
            'desc' => 'Encrypted AES-256 telemetry streams maintaining low latency data sync across forward operational bases.',
            'icon' => 'lock',
        ],
        [
            'title' => 'Instant Tactical Dispatch',
            'desc' => 'Encrypted combat directives with dual-operator verification bypass and immediate regional routing.',
            'icon' => 'zap',
        ],
    ];
};
?>

<div class="relative min-h-[calc(100vh-64px)] p-4 md:p-8 space-y-8 max-w-7xl mx-auto overflow-hidden">

    {{-- Ambient topographical grid --}}
    <div
        class="absolute inset-0 pointer-events-none opacity-20 z-0"
        style="
            background-image: linear-gradient(#1e293b 1px, transparent 1px),
                linear-gradient(90deg, #1e293b 1px, transparent 1px);
            background-size: 48px 48px;
        "
    ></div>

    {{-- Hero header banner --}}
    <div class="relative z-10 bg-surface-container border border-outline-variant rounded-lg p-6 md:p-8 overflow-hidden shadow-[0_4px_30px_rgba(0,0,0,0.8)]">

        {{-- Decorative tech corners --}}
        <div class="tech-corner-tl"></div>
        <div class="tech-corner-tr"></div>
        <div class="tech-corner-bl"></div>
        <div class="tech-corner-br"></div>

        {{-- Top tactical bar --}}
        <div class="flex flex-wrap items-center justify-between gap-3 border-b border-outline-variant/80 pb-4 mb-6">
            <div class="flex flex-wrap items-center gap-3">
                <div class="w-2.5 h-2.5 rounded-full bg-[#4ade80] animate-pulse shadow-[0_0_8px_rgba(74,222,128,0.8)]"></div>

                <span class="font-mono-data text-xs uppercase tracking-widest text-[#4ade80] font-bold">
                    CLASSIFIED DEFENSE ANALYTICS PLATFORM
                </span>
            </div>

            <div
                class="font-mono-data text-xs text-tertiary font-bold tracking-wider"
                x-data="{
                    currentTimeIst: '',
                    updateClock() {
                        this.currentTimeIst = new Intl.DateTimeFormat('en-IN', {
                            timeZone: 'Asia/Kolkata',
                            hour: '2-digit',
                            minute: '2-digit',
                            second: '2-digit',
                            hour12: false
                        }).format(new Date()) + ' IST';
                    }
                }"
                x-init="updateClock(); setInterval(() => updateClock(), 1000)"
                x-text="currentTimeIst"
            ></div>
        </div>

        {{-- Hero main content --}}
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-center">

            <div class="lg:col-span-8 space-y-4">

                <div class="inline-flex items-center gap-2 px-2.5 py-1 bg-surface-container-high border border-outline-variant rounded text-[11px] font-mono-data text-tertiary">
                    <x-lucide-shield class="w-[14px] h-[14px]" />
                    <span>TALOS STRATEGIC OPERATIONS PLATFORM</span>
                </div>

                <h1 class="text-3xl sm:text-4xl lg:text-5xl font-black text-on-surface tracking-tight uppercase leading-none">
                    Tactical Command
                    <span class="text-tertiary">&amp; Mission Analytics</span>
                </h1>

                <p class="text-sm sm:text-base text-on-surface-variant max-w-2xl leading-relaxed">
                    Centralized defense intelligence orchestrating multi-echelon deployment
                    tracking, conflict-free tactical routing, and automated officer chain
                    verification across all active operational sectors.
                </p>

                {{-- Primary CTA buttons --}}
                <div class="flex flex-wrap items-center gap-4 pt-2">

                    @guest
                        <a
                            href="{{ route('login') }}"
                            class="px-5 py-3 bg-tertiary text-on-tertiary rounded text-xs font-bold uppercase tracking-widest hover:bg-tertiary-fixed transition-colors flex items-center gap-2 shadow-[0_0_15px_rgba(255,185,95,0.3)] active:scale-95"
                        >
                            <x-lucide-lock class="w-[18px] h-[18px]" />
                            <span>LOGIN</span>
                        </a>
                    @endguest

                    @auth
                        <form action="{{ route('logout') }}" method="POST">
                            @csrf
                            @method('DELETE')
                            <button
                                type="submit"
                                class="px-5 py-3 bg-tertiary text-on-tertiary rounded text-xs font-bold uppercase tracking-widest hover:bg-tertiary-fixed transition-colors flex items-center gap-2 shadow-[0_0_15px_rgba(255,185,95,0.3)] active:scale-95"
                            >
                                <x-lucide-log-out class="w-[18px] h-[18px]" />
                                <span>LOGOUT</span>
                            </button>
                        </form>
                    @endauth

                    <a
                        href="{{ route('dashboard') }}"
                        class="px-5 py-3 bg-surface-container-highest border border-tertiary/50 text-tertiary rounded text-xs font-bold uppercase tracking-widest hover:bg-tertiary/10 transition-colors flex items-center gap-2 active:scale-95"
                    >
                        <x-lucide-layout-dashboard class="w-[18px] h-[18px]" />
                        <span>GO TO DASHBOARD</span>
                    </a>

                    @auth
                        <a
                            {{-- href="{{ route('routing') }}" --}}
                            href="/routing"
                            class="px-4 py-3 bg-transparent border border-outline-variant text-on-surface-variant hover:text-on-surface hover:border-outline rounded text-xs font-bold uppercase tracking-widest transition-colors flex items-center gap-2"
                        >
                            <x-lucide-map class="w-[18px] h-[18px]" />
                            <span>VIEW TACTICAL MAP</span>
                        </a>
                    @endauth

                </div>
            </div>

            {{-- Right emblem column --}}
            <div class="lg:col-span-4 flex flex-col items-center justify-center p-6 bg-surface-container-lowest/60 border border-outline-variant/60 rounded">
                <x-talos-logo size="xl" :show-text="true" />

                <div class="mt-4 pt-4 border-t border-outline-variant/40 w-full flex justify-between font-mono-data text-[10px] text-on-surface-variant">
                    <span>UPLINK: SECURE_V9</span>
                    <span class="text-tertiary">CIPHER: AES-256-MIL</span>
                </div>
            </div>

        </div>
    </div>

    {{-- Capabilities and technical matrix --}}
    <div class="relative z-10 bg-surface-container border border-outline-variant rounded-lg p-6 space-y-6">

        <div class="border-b border-outline-variant pb-3">
            <h2 class="text-lg font-bold text-on-surface uppercase tracking-wider">
                System Architecture &amp; Intelligence Capabilities
            </h2>

            <p class="text-xs text-on-surface-variant font-mono-data mt-0.5">
                High-availability tactical infrastructure powered by modern graph databases and
                topological routing engines
            </p>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">

            @foreach ($capabilities as $capability)
                <div
                    wire:key="{{ \Illuminate\Support\Str::slug($capability['title']) }}"
                    class="space-y-2 p-3 bg-surface border border-outline-variant/70 rounded"
                >
                    <div class="w-8 h-8 rounded bg-surface-container-high border border-outline-variant flex items-center justify-center">
                        <x-dynamic-component
                            :component="'lucide-' . $capability['icon']"
                            class="w-[18px] h-[18px] text-tertiary"
                        />
                    </div>

                    <h4 class="font-bold text-xs uppercase tracking-wider text-on-surface">
                        {{ $capability['title'] }}
                    </h4>

                    <p class="text-xs text-on-surface-variant leading-relaxed">
                        {{ $capability['desc'] }}
                    </p>
                </div>
            @endforeach

        </div>
    </div>

</div>
