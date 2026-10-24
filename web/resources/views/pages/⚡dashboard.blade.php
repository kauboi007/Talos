<?php

use App\Services\Neo4jService;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Layout('layouts::command'), Title('Dashboard')]
class extends Component
{
    public array $metrics = [];
    public array $deployments = [];
    public array $threatEvents = [];
    public ?string $error = null;

    public function mount(Neo4jService $neo4j): void
    {
        try {
            $data = $neo4j->dashboardData(Auth::user());
            $checkpointCount = (int) data_get($data, 'checkpoint_count', 0);
            $operationalCount = (int) data_get($data, 'operational_checkpoint_count', 0);

            $this->metrics = [
                [
                    'label' => 'Active Missions',
                    'value' => '—',
                    'detail' => 'Mission table pending',
                    'icon' => 'compass',
                    'color' => 'text-tertiary',
                ],
                [
                    'label' => 'Personnel',
                    'value' => (string) data_get($data, 'officer_count', 0),
                    'detail' => 'Personnel in scope',
                    'icon' => 'users',
                    'color' => 'text-primary',
                ],
                [
                    'label' => 'Threat Events',
                    'value' => (string) count(data_get($data, 'threat_events', [])),
                    'detail' => 'Recorded in CBS',
                    'icon' => 'shield-alert',
                    'color' => 'text-tertiary',
                ],
                [
                    'label' => 'Checkpoints',
                    'value' => $checkpointCount > 0
                        ? "{$operationalCount}/{$checkpointCount}"
                        : '0',
                    'detail' => 'Operational checkpoints',
                    'icon' => 'radar',
                    'color' => 'text-secure',
                ],
            ];
            $this->deployments = data_get($data, 'deployments', []);
            $this->threatEvents = data_get($data, 'threat_events', []);
        } catch (ConnectionException|RequestException $exception) {
            report($exception);
            $this->error = 'Dashboard data is temporarily unavailable from the CBS service.';
        }
    }
};
?>

<div class="p-4 md:p-6 space-y-6">
    @if ($error)
        <div class="border border-error/30 bg-error-container p-4 text-sm text-error" role="alert">
            {{ $error }}
        </div>
    @else
        <section class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4">
            @foreach ($metrics as $metric)
                <article
                    wire:key="metric-{{ $metric['label'] }}"
                    class="bg-primary-container border border-outline-variant rounded p-4 flex flex-col justify-between min-h-32"
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

        <div class="grid grid-cols-1 xl:grid-cols-3 gap-6">
            <section class="xl:col-span-2 bg-primary-container border border-outline-variant rounded p-4 md:p-6">
                <div class="flex flex-wrap justify-between items-center gap-3 mb-5 border-b border-outline-variant pb-3">
                    <div>
                        <h2 class="text-lg font-semibold text-on-surface">Troop Deployment</h2>
                        <p class="text-xs text-on-surface-variant mt-1">
                            Personnel distribution from CBS data
                        </p>
                    </div>
                    <span class="font-mono-data text-[10px] text-secure border border-secure-border px-2 py-1">
                        LIVE CBS DATA
                    </span>
                </div>

                @if (count($deployments) === 0)
                    <p class="text-sm text-on-surface-variant">No troop deployment data was returned.</p>
                @else
                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse min-w-[560px]">
                            <thead>
                                <tr class="bg-surface-container-high border-b border-outline-variant">
                                    <th class="p-3 text-[10px] font-bold uppercase tracking-wider text-on-surface-variant">Troop</th>
                                    <th class="p-3 text-[10px] font-bold uppercase tracking-wider text-on-surface-variant">Type</th>
                                    <th class="p-3 text-[10px] font-bold uppercase tracking-wider text-on-surface-variant text-right">Personnel</th>
                                    <th class="p-3 text-[10px] font-bold uppercase tracking-wider text-on-surface-variant text-center">Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($deployments as $deployment)
                                    <tr wire:key="deployment-{{ $loop->index }}" class="border-b border-outline-variant">
                                        <td class="p-3 font-mono-data text-xs text-tertiary">{{ $deployment['sector'] }}</td>
                                        <td class="p-3 text-sm text-on-surface">{{ $deployment['unit'] }}</td>
                                        <td class="p-3 font-mono-data text-xs text-on-surface-variant text-right">{{ $deployment['soldiers'] }}</td>
                                        <td class="p-3 text-center text-xs text-on-surface-variant">{{ $deployment['status'] }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </section>

            <section class="bg-primary-container border border-outline-variant rounded p-4">
                <div class="flex items-center justify-between mb-4 border-b border-outline-variant pb-3">
                    <div>
                        <h2 class="text-base font-semibold text-on-surface">Recent Threat Events</h2>
                        <p class="text-[10px] text-on-surface-variant mt-1">Recorded in CBS</p>
                    </div>
                    <x-lucide-activity class="w-5 h-5 text-tertiary" />
                </div>

                @if (count($threatEvents) === 0)
                    <p class="text-sm text-on-surface-variant">No threat events were returned.</p>
                @else
                    <div class="space-y-3">
                        @foreach ($threatEvents as $event)
                            <article wire:key="event-{{ $loop->index }}" class="p-3 bg-surface border border-outline-variant rounded">
                                <div class="flex items-center justify-between gap-2 mb-2">
                                    <span class="font-mono-data text-[10px] text-on-surface-variant">
                                        {{ data_get($event, 'timestamp', 'Recorded event') }}
                                    </span>
                                    <span class="font-mono-data text-[10px] text-tertiary uppercase">
                                        {{ data_get($event, 'severity', 'UNKNOWN') }}
                                    </span>
                                </div>
                                <p class="text-xs text-on-surface-variant leading-relaxed">
                                    {{ data_get($event, 'name', 'Unnamed threat event') }}
                                    @if (data_get($event, 'type'))
                                        <span class="text-on-surface">· {{ data_get($event, 'type') }}</span>
                                    @endif
                                </p>
                            </article>
                        @endforeach
                    </div>
                @endif
            </section>
        </div>
    @endif
</div>
