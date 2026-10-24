<?php

use App\Services\Neo4jService;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Layout('layouts::command'), Title('Checkpoint Graph')]
class extends Component
{
    public array $graph = [];
    public array $mapData = [];
    public ?string $error = null;

    public function mount(Neo4jService $neo4j): void
    {
        try {
            $this->graph = $neo4j->checkpointGraph(Auth::user());
            $this->mapData = $this->buildMapData($this->graph);
        } catch (ConnectionException|RequestException $exception) {
            report($exception);
            $this->error = 'Checkpoint graph data is temporarily unavailable from the CBS service.';
        }
    }

    private function buildMapData(array $graph): array
    {
        $nodes = [];
        foreach ($graph['nodes'] ?? [] as $node) {
            $id = data_get($node, 'checkpoint_code')
                ?? data_get($node, 'code')
                ?? data_get($node, 'id')
                ?? data_get($node, 'name');
            $lat = data_get($node, 'lat');
            $lng = data_get($node, 'lng');

            if (! is_string($id) || ! is_numeric($lat) || ! is_numeric($lng)) {
                continue;
            }

            $nodes[$id] = [
                'id' => $id,
                'name' => data_get($node, 'name', $id),
                'code' => data_get($node, 'checkpoint_code', data_get($node, 'code', $id)),
                'type' => data_get($node, 'type', 'checkpoint'),
                'status' => data_get($node, 'status', 'unknown'),
                'lat' => (float) $lat,
                'lng' => (float) $lng,
            ];
        }

        $routes = [];
        foreach ($graph['edges'] ?? [] as $edge) {
            $source = data_get($edge, 'from_code', data_get($edge, 'from'));
            $target = data_get($edge, 'to_code', data_get($edge, 'to'));

            if (! is_string($source) || ! is_string($target)
                || ! isset($nodes[$source], $nodes[$target])) {
                continue;
            }

            $routes[] = [
                'source' => $source,
                'target' => $target,
                'source_name' => $nodes[$source]['name'],
                'target_name' => $nodes[$target]['name'],
                'distance' => data_get($edge, 'distance'),
                'source_lat' => $nodes[$source]['lat'],
                'source_lng' => $nodes[$source]['lng'],
                'target_lat' => $nodes[$target]['lat'],
                'target_lng' => $nodes[$target]['lng'],
            ];
        }

        return [
            'nodes' => array_values($nodes),
            'routes' => $routes,
        ];
    }
};
?>

<div class="p-4 md:p-6 space-y-6">
    <div class="flex flex-wrap items-end justify-between gap-4">
        <div>
            <p class="font-mono-data text-[10px] uppercase tracking-[0.2em] text-tertiary">
                Operations / Checkpoint Network
            </p>
            <h1 class="mt-1 text-2xl font-bold tracking-tight text-on-surface">Checkpoint Graph</h1>
            <p class="mt-1 text-sm text-on-surface-variant">
                Routing info and checkpoint placements for military activities.
            </p>
        </div>
        <span class="border border-secure-border bg-secure-bg px-3 py-2 text-[10px] font-mono-data uppercase text-secure">
            Live CBS data
        </span>
    </div>

    @if ($error)
        <div class="border border-error/30 bg-error-container p-4 text-sm text-error" role="alert">
            {{ $error }}
        </div>
    @else
        <section
            wire:ignore
            x-data="checkpointMap(@js($mapData))"
            x-init="init()"
            class="bg-primary-container border border-outline-variant rounded p-2 md:p-4"
        >
            <div class="flex flex-wrap items-center justify-between gap-3 px-2 pb-3">
                <div>
                    <h2 class="text-sm font-semibold uppercase tracking-wider text-on-surface">
                        Visual Checkpoint Map
                    </h2>
                    <p class="mt-1 text-xs text-on-surface-variant">
                        For Available Camps in TN
                    </p>
                </div>
                <button
                    type="button"
                    @click="fitBounds()"
                    class="btn-tactical-outline px-3 py-2 text-[10px] font-bold uppercase tracking-wider"
                >
                    Fit Network
                </button>
            </div>

            <div
                x-ref="map"
                class="h-[620px] w-full rounded border border-outline-variant bg-surface-container-low"
                role="img"
                aria-label="Interactive checkpoint relationship map"
            ></div>
        </section>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-3 text-xs text-on-surface-variant">
            <div class="border border-outline-variant bg-primary-container p-3">
                <span class="font-mono-data text-tertiary">{{ count(data_get($graph, 'nodes', [])) }}</span>
                checkpoint nodes
            </div>
            <div class="border border-outline-variant bg-primary-container p-3">
                <span class="font-mono-data text-tertiary">{{ count(data_get($graph, 'edges', [])) }}</span>
                route relationships
            </div>
            <div class="border border-outline-variant bg-primary-container p-3">
                <span class="font-mono-data text-secure">LIVE</span>
                CBS service data
            </div>
        </div>
    @endif
<script>
    window.checkpointMap = function (data) {
        return {
            data,
            map: null,
            init() {
                if (typeof L === 'undefined') {
                    window.addEventListener('leaflet:ready', () => this.init(), { once: true });
                    return;
                }

                this.$nextTick(() => {
                    if (this.map || !this.$refs.map) {
                        return;
                    }

                    this.map = L.map(this.$refs.map, {
                        minZoom: 6,
                        maxZoom: 13,
                        zoomControl: true,
                    }).setView([11.1, 78.6], 7);

                    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                        attribution: '&copy; OpenStreetMap contributors',
                        maxZoom: 19,
                    }).addTo(this.map);

                    const bounds = [];
                    this.data.nodes.forEach((node) => {
                        const position = [node.lat, node.lng];
                        bounds.push(position);

                        L.circleMarker(position, {
                            radius: 8,
                            color: '#172235',
                            weight: 2,
                            fillColor: '#b45309',
                            fillOpacity: 0.95,
                        }).addTo(this.map).bindPopup(`
                            <strong>${this.escape(node.name)}</strong><br>
                            <code>${this.escape(node.code)}</code><br>
                            ${this.escape(node.type)} &middot; ${this.escape(node.status)}
                        `);
                    });

                    this.data.routes.forEach((route) => {
                        const line = L.polyline([
                            [route.source_lat, route.source_lng],
                            [route.target_lat, route.target_lng],
                        ], {
                            color: '#b45309',
                            weight: 3,
                            opacity: 0.75,
                        }).addTo(this.map);

                        line.bindTooltip(
                            `${this.escape(route.source_name)} &rarr; ${this.escape(route.target_name)}`
                            + (route.distance !== null ? ` &middot; ${this.escape(String(route.distance))} km` : ''),
                        );
                    });

                    requestAnimationFrame(() => {
                        this.map.invalidateSize();
                        if (bounds.length > 0) {
                            this.map.fitBounds(bounds, { padding: [30, 30] });
                        }
                    });
                });
            },
            fitBounds() {
                if (! this.map || this.data.nodes.length === 0) {
                    return;
                }

                this.map.fitBounds(this.data.nodes.map((node) => [node.lat, node.lng]), {
                    padding: [30, 30],
                });
            },
            escape(value) {
                return String(value ?? '').replace(/[&<>"']/g, (character) => ({
                    '&': '&amp;',
                    '<': '&lt;',
                    '>': '&gt;',
                    '"': '&quot;',
                    "'": '&#039;',
                }[character]));
            },
        };
    };
</script>
</div>
