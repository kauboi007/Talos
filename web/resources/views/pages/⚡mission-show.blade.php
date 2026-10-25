<?php

use App\Models\Mission;
use App\Services\Neo4jService;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Layout('layouts::command'), Title('Mission Details')]
class extends Component
{
    public Mission $mission;
    public array $plan = [];
    public array $mapData = ['nodes' => [], 'routes' => []];
    public ?string $error = null;

    public function mount(Mission $mission, Neo4jService $neo4j): void
    {
        $this->mission = $mission;
        $this->plan = $this->extractPlan($mission);

        try {
            $this->mapData = $this->buildMapData(
                $mission,
                $neo4j->missionOptions()['checkpoints'],
            );
        } catch (ConnectionException|RequestException $exception) {
            report($exception);
            $this->error = 'Mission checkpoint data is temporarily unavailable from the CBS service.';
        }
    }

    private function buildMapData(Mission $mission, array $checkpoints): array
    {
        $byName = [];
        foreach ($checkpoints as $checkpoint) {
            $name = data_get($checkpoint, 'name');
            if (is_string($name)) {
                $byName[$name] = $checkpoint;
            }
        }

        $plan = $this->extractPlan($mission);
        $path = data_get($plan, 'path', []);
        $nodes = [];
        $pathNodes = [];

        foreach ($path as $stop) {
            $name = data_get($stop, 'checkpoint');
            $checkpoint = $byName[$name] ?? null;
            if (! is_array($checkpoint)
                || ! is_numeric(data_get($checkpoint, 'lat'))
                || ! is_numeric(data_get($checkpoint, 'lng'))) {
                continue;
            }

            $nodeId = (string) $name;
            $nodes[$nodeId] = [
                'name' => $nodeId,
                'lat' => (float) data_get($checkpoint, 'lat'),
                'lng' => (float) data_get($checkpoint, 'lng'),
                'start' => count($pathNodes) === 0,
                'end' => false,
            ];
            $pathNodes[] = $nodeId;
        }

        if ($pathNodes !== []) {
            $nodes[$pathNodes[array_key_last($pathNodes)]]['end'] = true;
        }

        $routes = [];
        for ($index = 1; $index < count($pathNodes); $index++) {
            $from = $nodes[$pathNodes[$index - 1]];
            $to = $nodes[$pathNodes[$index]];
            $routes[] = [
                'source_lat' => $from['lat'],
                'source_lng' => $from['lng'],
                'target_lat' => $to['lat'],
                'target_lng' => $to['lng'],
            ];
        }

        return ['nodes' => array_values($nodes), 'routes' => $routes];
    }

    private function extractPlan(Mission $mission): array
    {
        $plan = data_get($mission->planning_result, 'plan');
        if (is_array($plan) && $plan !== []) {
            return $plan;
        }

        $plan = data_get($mission->planning_result, "plans.{$mission->troop_name}", []);
        if (is_array($plan) && $plan !== []) {
            return $plan;
        }

        return collect(data_get($mission->planning_result, 'plans', []))
            ->first(fn (mixed $candidate): bool => is_array($candidate)) ?? [];
    }
};
?>

<div class="p-4 md:p-6 space-y-6">
    <div class="flex flex-wrap items-start justify-between gap-4">
        <div>
            <a href="{{ route('dashboard') }}" wire:navigate class="text-xs text-tertiary hover:underline">&larr; Back to dashboard</a>
            <p class="mt-4 font-mono-data text-[10px] uppercase tracking-[0.2em] text-tertiary">Missions / Approved route</p>
            <h1 class="mt-1 text-2xl font-bold tracking-tight text-on-surface">{{ $mission->name }}</h1>
            <p class="mt-1 text-sm text-on-surface-variant">{{ $mission->troop_name }} mission details and route graph</p>
        </div>
        <span class="border border-secure/40 bg-secure-container px-3 py-2 text-[10px] font-bold uppercase tracking-wider text-secure">{{ $mission->status }}</span>
    </div>

    @if ($error)
        <div class="border border-error/30 bg-error-container p-4 text-sm text-error">{{ $error }}</div>
    @endif

    <section class="grid grid-cols-2 md:grid-cols-4 gap-4">
        <div class="bg-primary-container border border-outline-variant p-4"><p class="text-[10px] uppercase text-on-surface-variant">Troop</p><p class="mt-1 font-semibold text-on-surface">{{ $mission->troop_name }}</p></div>
        <div class="bg-primary-container border border-outline-variant p-4"><p class="text-[10px] uppercase text-on-surface-variant">Speed</p><p class="mt-1 font-semibold text-on-surface">{{ data_get($plan, 'speed_kmh', '—') }} km/h</p></div>
        <div class="bg-primary-container border border-outline-variant p-4"><p class="text-[10px] uppercase text-on-surface-variant">Travel time</p><p class="mt-1 font-semibold text-on-surface">{{ data_get($plan, 'total_time_hr', '—') }} hr</p></div>
        <div class="bg-primary-container border border-outline-variant p-4"><p class="text-[10px] uppercase text-on-surface-variant">Threat exposure</p><p class="mt-1 font-semibold text-on-surface">{{ data_get($plan, 'threat_exposure', '—') }}</p></div>
    </section>

    <section class="grid grid-cols-1 xl:grid-cols-2 gap-6">
        <div class="bg-primary-container border border-outline-variant p-4 md:p-6">
            <h2 class="mb-4 text-base font-semibold text-on-surface">Approved mission path</h2>
            <div wire:ignore x-data="missionDetailMap(@js($mapData))" x-init="init()" class="h-[480px]">
                <div x-ref="map" class="h-full w-full border border-outline-variant"></div>
            </div>
        </div>
        <div class="bg-primary-container border border-outline-variant p-4 md:p-6">
            <h2 class="mb-4 text-base font-semibold text-on-surface">Checkpoint schedule</h2>
            <div class="overflow-x-auto">
                <table class="w-full min-w-[420px] text-left">
                    <thead><tr class="border-b border-outline-variant text-[10px] uppercase tracking-wider text-on-surface-variant"><th class="p-2">Checkpoint</th><th class="p-2">Arrival</th><th class="p-2">Departure</th></tr></thead>
                    <tbody>
                        @foreach (data_get($plan, 'path', []) as $stop)
                            <tr class="border-b border-outline-variant/70">
                                <td class="p-2 text-sm text-on-surface">{{ data_get($stop, 'checkpoint', '—') }}</td>
                                <td class="p-2 font-mono-data text-xs text-on-surface-variant">{{ data_get($stop, 'arrival_hr', '—') }} hr</td>
                                <td class="p-2 font-mono-data text-xs text-on-surface-variant">{{ data_get($stop, 'departure_hr', '—') }} hr</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="mt-6 border-t border-outline-variant pt-4 text-xs text-on-surface-variant">
                Approved {{ $mission->approved_at?->format('Y-m-d H:i') ?? '—' }}
            </div>
        </div>
    </section>
</div>

<script>
window.missionDetailMap = function (data) {
    return {
        data, map: null, layers: null,
        init() {
            this.$nextTick(() => {
                if (typeof L === 'undefined' || !this.$refs.map) return;
                this.map = L.map(this.$refs.map).setView([11.1, 78.6], 7);
                L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', { attribution: '&copy; OpenStreetMap contributors' }).addTo(this.map);
                this.layers = L.layerGroup().addTo(this.map);
                this.render();
            });
        },
        render() {
            this.data.routes.forEach((route) => L.polyline([[route.source_lat, route.source_lng], [route.target_lat, route.target_lng]], { color: '#2563eb', weight: 5 }).addTo(this.layers));
            const bounds = [];
            this.data.nodes.forEach((node) => {
                const position = [node.lat, node.lng];
                bounds.push(position);
                L.circleMarker(position, {
                    radius: node.start || node.end ? 10 : 7,
                    color: node.start ? '#15803d' : (node.end ? '#b91c1c' : '#172235'),
                    fillColor: node.start ? '#22c55e' : (node.end ? '#ef4444' : '#f59e0b'),
                    fillOpacity: 0.95,
                }).addTo(this.layers).bindPopup(`<strong>${this.escape(node.name)}</strong><br>${node.start ? 'START' : ''}${node.end ? ' END' : ''}`);
            });
            if (bounds.length) this.map.fitBounds(bounds, { padding: [25, 25] });
            requestAnimationFrame(() => this.map.invalidateSize());
        },
        escape(value) {
            return String(value ?? '').replace(/[&<>"']/g, (character) => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;' }[character]));
        },
    };
};
</script>
