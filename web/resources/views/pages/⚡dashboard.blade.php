<?php

use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Layout('layouts::command'), Title('Dashboard')]
class extends Component {
    public array $metrics = [
        [
            'label' => 'Active Missions',
            'value' => '12',
            'detail' => '+3 this cycle',
            'icon' => 'compass',
            'color' => 'text-tertiary',
        ],
        [
            'label' => 'Troop Deployment',
            'value' => '248',
            'detail' => 'Personnel deployed',
            'icon' => 'users',
            'color' => 'text-secondary',
        ],
        [
            'label' => 'Threat Level',
            'value' => 'ELEVATED',
            'detail' => '2 sectors flagged',
            'icon' => 'shield-alert',
            'color' => 'text-[#ffb95f]',
        ],
        [
            'label' => 'Checkpoints',
            'value' => '86%',
            'detail' => '12 of 14 operational',
            'icon' => 'radar',
            'color' => 'text-[#4ade80]',
        ],
    ];

    public array $deployments = [
        [
            'sector' => 'ALPHA-01',
            'unit' => 'Reconnaissance Group',
            'soldiers' => 32,
            'officers' => 4,
            'status' => 'ACTIVE',
        ],
        [
            'sector' => 'BRAVO-02',
            'unit' => 'Tactical Response',
            'soldiers' => 48,
            'officers' => 6,
            'status' => 'ACTIVE',
        ],
        [
            'sector' => 'CHARLIE-03',
            'unit' => 'Logistics Division',
            'soldiers' => 24,
            'officers' => 3,
            'status' => 'STANDBY',
        ],
        [
            'sector' => 'DELTA-04',
            'unit' => 'Perimeter Security',
            'soldiers' => 40,
            'officers' => 5,
            'status' => 'ACTIVE',
        ],
    ];

    public array $telemetry = [
        ['name' => 'COMMAND-NODE-01', 'role' => 'Primary Command', 'latency' => '12'],
        ['name' => 'RELAY-NODE-04', 'role' => 'Telemetry Relay', 'latency' => '24'],
        ['name' => 'MAP-NODE-02', 'role' => 'Routing Engine', 'latency' => '18'],
    ];

    public array $threatEvents = [
        [
            'time' => '14:32:08 IST',
            'severity' => 'HIGH',
            'description' => 'Unverified signal detected in Sector Bravo.',
        ],
        [
            'time' => '14:18:42 IST',
            'severity' => 'MEDIUM',
            'description' => 'Checkpoint authentication retry threshold exceeded.',
        ],
        [
            'time' => '13:56:17 IST',
            'severity' => 'LOW',
            'description' => 'Telemetry relay successfully re-established.',
        ],
    ];
};
?>

<div class="p-4 md:p-6 space-y-6">
    {{-- Demo-data notice --}}
    <div class="flex items-center gap-2 px-3 py-2 border border-tertiary/30 bg-tertiary/5 text-tertiary font-mono-data text-[10px] uppercase tracking-wider">
        <x-lucide-flask-conical class="w-4 h-4 shrink-0" />
        Demonstration data — not connected to live operational systems
    </div>

    {{-- Metric cards --}}
    <section class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4">
        @foreach ($metrics as $metric)
            <article
                wire:key="metric-{{ $metric['label'] }}"
                class="bg-primary-container border border-outline-variant rounded p-4 flex flex-col justify-between min-h-32 hover:bg-surface-variant/40 transition-colors"
            >
                <div class="flex justify-between items-start gap-2 mb-4">
                    <span class="text-xs font-bold uppercase tracking-wider text-on-surface-variant">
                        {{ $metric['label'] }}
                    </span>

                    @switch($metric['icon'])
                        @case('compass')
                            <x-lucide-compass class="w-5 h-5 {{ $metric['color'] }}" />
                            @break
                        @case('users')
                            <x-lucide-users class="w-5 h-5 {{ $metric['color'] }}" />
                            @break
                        @case('shield-alert')
                            <x-lucide-shield-alert class="w-5 h-5 {{ $metric['color'] }}" />
                            @break
                        @case('radar')
                            <x-lucide-radar class="w-5 h-5 {{ $metric['color'] }}" />
                            @break
                    @endswitch
                </div>

                <div>
                    <div class="text-3xl font-bold text-on-surface leading-none">
                        {{ $metric['value'] }}
                    </div>
                    <p class="font-mono-data text-[10px] text-on-surface-variant mt-2">
                        {{ $metric['detail'] }}
                    </p>
                </div>
            </article>
        @endforeach
    </section>

    {{-- Main dashboard grid --}}
    <div class="grid grid-cols-1 xl:grid-cols-3 gap-6">

        {{-- Deployment table --}}
        <section class="xl:col-span-2 bg-primary-container border border-outline-variant rounded p-4 md:p-6">
            <div class="flex flex-wrap justify-between items-center gap-3 mb-5 border-b border-outline-variant pb-3">
                <div>
                    <h2 class="text-lg font-semibold text-on-surface">
                        Deployment Status
                    </h2>
                    <p class="text-xs text-on-surface-variant mt-1">
                        Personnel distribution across operational sectors
                    </p>
                </div>

                <span class="font-mono-data text-[10px] text-tertiary border border-tertiary/30 px-2 py-1">
                    DEMO SNAPSHOT
                </span>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse min-w-[560px]">
                    <thead>
                        <tr class="bg-surface-container-high border-b border-outline-variant">
                            <th class="p-3 text-[10px] font-bold uppercase tracking-wider text-on-surface-variant">Sector</th>
                            <th class="p-3 text-[10px] font-bold uppercase tracking-wider text-on-surface-variant">Unit Designation</th>
                            <th class="p-3 text-[10px] font-bold uppercase tracking-wider text-on-surface-variant text-right">Personnel</th>
                            <th class="p-3 text-[10px] font-bold uppercase tracking-wider text-on-surface-variant text-right">Officers</th>
                            <th class="p-3 text-[10px] font-bold uppercase tracking-wider text-on-surface-variant text-center">Status</th>
                        </tr>
                    </thead>

                    <tbody>
                        @foreach ($deployments as $deployment)
                            <tr wire:key="deployment-{{ $deployment['sector'] }}" class="border-b border-outline-variant hover:bg-surface-variant/30 transition-colors">
                                <td class="p-3 font-mono-data text-xs text-tertiary">
                                    {{ $deployment['sector'] }}
                                </td>

                                <td class="p-3 text-sm text-on-surface">
                                    {{ $deployment['unit'] }}
                                </td>

                                <td class="p-3 font-mono-data text-xs text-on-surface-variant text-right">
                                    {{ $deployment['soldiers'] }}
                                </td>

                                <td class="p-3 font-mono-data text-xs text-on-surface-variant text-right">
                                    {{ $deployment['officers'] }}
                                </td>

                                <td class="p-3 text-center">
                                    @if ($deployment['status'] === 'ACTIVE')
                                        <span class="inline-flex items-center gap-1.5 px-2 py-1 text-[10px] font-mono-data bg-[#0d2a1b] border border-[#1e5631] text-[#4ade80] rounded">
                                            <span class="w-1.5 h-1.5 rounded-full bg-[#4ade80]"></span>
                                            ACTIVE
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1.5 px-2 py-1 text-[10px] font-mono-data bg-surface-container-high border border-outline-variant text-on-surface-variant rounded">
                                            <span class="w-1.5 h-1.5 rounded-full bg-outline"></span>
                                            STANDBY
                                        </span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="mt-4 flex items-center justify-between text-xs text-on-surface-variant">
                <span>{{ count($deployments) }} sample sector records</span>
                <a href="/personnel" class="text-tertiary hover:text-tertiary-fixed font-semibold">
                    View Personnel →
                </a>
            </div>
        </section>

        {{-- Right-side widgets --}}
        <div class="space-y-6">

            {{-- System telemetry --}}
            <section class="bg-primary-container border border-outline-variant rounded p-4">
                <div class="flex items-center justify-between mb-4 border-b border-outline-variant pb-3">
                    <div>
                        <h2 class="text-base font-semibold text-on-surface">System Telemetry</h2>
                        <p class="text-[10px] text-on-surface-variant mt-1">Sample node health</p>
                    </div>
                    <x-lucide-server class="w-5 h-5 text-tertiary" />
                </div>

                <div class="space-y-4">
                    @foreach ($telemetry as $node)
                        <div wire:key="telemetry-{{ $node['name'] }}" class="flex items-center justify-between gap-3">
                            <div class="min-w-0">
                                <p class="text-xs font-semibold text-on-surface truncate">
                                    {{ $node['name'] }}
                                </p>
                                <p class="font-mono-data text-[10px] text-on-surface-variant mt-1">
                                    {{ $node['role'] }}
                                </p>
                            </div>

                            <div class="text-right shrink-0">
                                <p class="font-mono-data text-xs text-[#4ade80]">
                                    {{ $node['latency'] }} ms
                                </p>
                                <p class="text-[10px] text-on-surface-variant">LATENCY</p>
                            </div>
                        </div>
                    @endforeach
                </div>
            </section>

            {{-- Recent threat events --}}
            <section class="bg-primary-container border border-outline-variant rounded p-4">
                <div class="flex items-center justify-between mb-4 border-b border-outline-variant pb-3">
                    <div>
                        <h2 class="text-base font-semibold text-on-surface">Recent Threat Events</h2>
                        <p class="text-[10px] text-on-surface-variant mt-1">Illustrative event stream</p>
                    </div>
                    <x-lucide-activity class="w-5 h-5 text-tertiary" />
                </div>

                <div class="space-y-3">
                    @foreach ($threatEvents as $event)
                        <article wire:key="event-{{ $loop->index }}" class="p-3 bg-surface border border-outline-variant rounded">
                            <div class="flex items-center justify-between gap-2 mb-2">
                                <span class="font-mono-data text-[10px] text-on-surface-variant">
                                    {{ $event['time'] }}
                                </span>

                                @if ($event['severity'] === 'HIGH')
                                    <span class="font-mono-data text-[10px] text-error uppercase">{{ $event['severity'] }}</span>
                                @elseif ($event['severity'] === 'MEDIUM')
                                    <span class="font-mono-data text-[10px] text-tertiary uppercase">{{ $event['severity'] }}</span>
                                @else
                                    <span class="font-mono-data text-[10px] text-[#4ade80] uppercase">{{ $event['severity'] }}</span>
                                @endif
                            </div>

                            <p class="text-xs text-on-surface-variant leading-relaxed">
                                {{ $event['description'] }}
                            </p>
                        </article>
                    @endforeach
                </div>
            </section>
        </div>
    </div>
</div>
