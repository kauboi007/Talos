<?php

namespace App\Services;

use App\Models\Soldier;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;
use InvalidArgumentException;
use UnexpectedValueException;

class Neo4jService
{
    private const ADMIN_ROLES = [
        'base_admin',
        'hq_admin',
        'system_admin',
    ];

    private const GRAPH_ROLES = [
        'base_admin',
        'hq_admin',
        'system_admin',
    ];

    public function soldierOverview(User $actor): array
    {
        $officer = $this->getResource('officers', $this->serviceId($actor));
        $checkpoint = $this->resourceFromReference('checkpoints', data_get($officer, 'stationed_at_id'));
        $troop = $this->resourceFromReference('troops', data_get($officer, 'troop_id'));
        $reportingOfficer = $this->isTopHierarchyOfficer($officer)
            ? null
            : $this->resourceFromReference('officers', data_get($officer, 'reports_to_id'));

        return [
            'soldier' => $officer,
            'checkpoint' => $checkpoint,
            'troop' => $troop,
            'reporting_officer' => $reportingOfficer,
        ];
    }

    public function dashboardData(User $actor): array
    {
        $officers = array_map(
            fn (array $officer): array => $this->enrichOfficer($officer),
            $this->items($this->get('/api/officers')),
        );
        $checkpoints = $this->items($this->get('/api/checkpoints'));
        $troops = $this->items($this->get('/api/troops'));
        $threatEvents = $this->items($this->get('/api/threat-events'));

        if ($actor->role !== 'system_admin' && $actor->soldier) {
            $checkpointCode = $this->checkpointCode($actor);
            $officers = array_values(array_filter(
                $officers,
                fn (array $officer): bool => data_get($officer, 'stationed_at_id') === $checkpointCode,
            ));
        }

        $deployments = array_map(function (array $troop) use ($officers): array {
            $troopName = data_get($troop, 'name');
            $assigned = array_values(array_filter(
                $officers,
                fn (array $officer): bool => data_get($officer, 'troop_id') === $troopName,
            ));

            return [
                'sector' => $troopName ?? '—',
                'unit' => data_get($troop, 'type', 'Unclassified'),
                'soldiers' => count($assigned),
                'officers' => count($assigned),
                'status' => strtoupper((string) data_get($troop, 'status', 'unknown')),
            ];
        }, $troops);

        return [
            'officer_count' => count($officers),
            'troop_count' => count($troops),
            'checkpoint_count' => count($checkpoints),
            'operational_checkpoint_count' => count(array_filter(
                $checkpoints,
                fn (array $checkpoint): bool => in_array(
                    strtolower((string) data_get($checkpoint, 'status')),
                    ['open', 'active', 'operational'],
                    true,
                ),
            )),
            'threat_events' => $threatEvents,
            'deployments' => $deployments,
        ];
    }

    public function reportingChain(User $actor): array
    {
        $chain = [];
        $current = $this->getResource('officers', $this->serviceId($actor));

        if ($this->isTopHierarchyOfficer($current)) {
            return [
                'immediate' => null,
                'chain' => [],
            ];
        }

        while ($current !== null && count($chain) < 25) {
            $reportsTo = data_get($current, 'reports_to_id');
            if (! is_string($reportsTo) || $reportsTo === '') {
                break;
            }

            $current = $this->getResource('officers', $reportsTo);
            if ($current !== null) {
                $chain[] = $current;
            }
        }

        return [
            'immediate' => $chain[0] ?? null,
            'chain' => $chain,
        ];
    }

    public function checkpointForSoldier(User $actor): array
    {
        return $this->soldierOverview($actor)['checkpoint'] ?? [];
    }

    public function troopForSoldier(User $actor): array
    {
        return $this->soldierOverview($actor)['troop'] ?? [];
    }

    public function subordinates(User $actor): array
    {
        $this->ensureRole($actor, self::ADMIN_ROLES);

        $officers = $this->items($this->get('/api/officers'));
        if ($actor->role === 'system_admin') {
            return array_map(
                fn (array $officer): array => $this->enrichPersonnelOfficer($officer),
                $officers,
            );
        }

        $checkpointCode = $this->checkpointCode($actor);

        return array_values(array_map(
            fn (array $officer): array => $this->enrichPersonnelOfficer($officer),
            array_filter(
                $officers,
                fn (array $officer): bool => data_get($officer, 'stationed_at_id') === $checkpointCode,
            ),
        ));
    }

    public function checkpointGraph(User $actor, ?string $checkpointCode = null): array
    {
        $this->ensureRole($actor, self::GRAPH_ROLES);

        $checkpoints = $this->get('/api/checkpoints');
        $routes = $this->get('/api/routes');

        $nodes = $this->items($checkpoints);
        $nodeReferences = [];
        foreach ($nodes as $node) {
            $code = $this->checkpointReference($node);
            if ($code === null) {
                continue;
            }

            foreach (['id', 'code', 'checkpoint_code', 'name'] as $key) {
                $reference = data_get($node, $key);
                if (is_string($reference)) {
                    $nodeReferences[$reference] = $code;
                }
            }
        }

        $edges = array_map(
            function (array $edge) use ($nodeReferences): array {
                $from = $this->checkpointReference($edge, 'from');
                $to = $this->checkpointReference($edge, 'to');

                return $edge + [
                    'from_code' => $nodeReferences[$from] ?? $from,
                    'to_code' => $nodeReferences[$to] ?? $to,
                ];
            },
            $this->items($routes),
        );

        if ($actor->role !== 'system_admin') {
            $checkpointCode ??= $this->checkpointCode($actor);
            $connectedCodes = [$checkpointCode];

            foreach ($edges as $edge) {
                $from = data_get($edge, 'from_code');
                $to = data_get($edge, 'to_code');

                if ($from === $checkpointCode || $to === $checkpointCode) {
                    $connectedCodes[] = $from;
                    $connectedCodes[] = $to;
                }
            }

            $connectedCodes = array_values(array_unique(array_filter($connectedCodes)));
            $nodes = array_values(array_filter(
                $nodes,
                fn (array $node): bool => in_array($this->checkpointReference($node), $connectedCodes, true),
            ));
            $edges = array_values(array_filter(
                $edges,
                fn (array $edge): bool => in_array(data_get($edge, 'from_code'), $connectedCodes, true)
                    && in_array(data_get($edge, 'to_code'), $connectedCodes, true),
            ));
        }

        return [
            'nodes' => $nodes,
            'edges' => $edges,
        ];
    }

    public function checkpoint(string $checkpointCode): array
    {
        return $this->get('/api/checkpoints/'.$this->segment($checkpointCode));
    }

    public function health(): array
    {
        return $this->get('/health');
    }

    public function missionOptions(): array
    {
        return [
            'troops' => $this->items($this->get('/api/troops')),
            'checkpoints' => $this->items($this->get('/api/checkpoints')),
        ];
    }

    public function planMission(array $troops): array
    {
        $response = $this->request()->post('/api/plan', [
            'troops' => array_values($troops),
        ]);

        $data = $response->throw()->json();

        if (! is_array($data)) {
            throw new UnexpectedValueException('The CBS service returned a non-object planning response.');
        }

        return $data;
    }

    private function get(string $uri, array $query = []): array
    {
        $response = $this->request()->get($uri, $query);
        $data = $response->throw()->json();

        if (! is_array($data)) {
            throw new UnexpectedValueException('The CBS service returned a non-object response.');
        }

        return $data;
    }

    private function getResource(string $resource, ?string $identifier): ?array
    {
        if ($identifier === null || $identifier === '') {
            return null;
        }

        $item = $this->get("/api/{$resource}/{$this->segment($identifier)}");

        return $resource === 'officers' ? $this->enrichOfficer($item) : $item;
    }

    private function enrichOfficer(array $officer): array
    {
        $serviceId = data_get($officer, 'service_id');
        if (! is_string($serviceId) || $serviceId === '') {
            return $officer;
        }

        $soldier = Soldier::where('service_id', $serviceId)->first();
        $user = $soldier?->user;

        if ($user) {
            $officer['name'] = $user->name();
        }

        if ($soldier) {
            $officer['rank'] = $soldier->rank;
        }

        $reportingId = data_get($officer, 'reports_to_id');
        if (! $this->isTopHierarchyOfficer($officer)
            && is_string($reportingId)
            && $reportingId !== '') {
            $reportingSoldier = Soldier::where('service_id', $reportingId)->first();
            $reportingUser = $reportingSoldier?->user;

            if ($reportingUser) {
                $officer['reports_to_name'] = $reportingUser->name();
            }
        }

        return $officer;
    }

    private function enrichPersonnelOfficer(array $officer): array
    {
        $officer = $this->enrichOfficer($officer);
        $checkpoint = $this->resourceFromReference('checkpoints', data_get($officer, 'stationed_at_id'));
        $troop = $this->resourceFromReference('troops', data_get($officer, 'troop_id'));

        $officer['checkpoint'] = $checkpoint;
        $officer['troop'] = $troop;

        return $officer;
    }

    private function isTopHierarchyOfficer(?array $officer): bool
    {
        return strtolower(trim((string) data_get($officer, 'rank'))) === 'general';
    }

    private function resourceFromReference(string $resource, mixed $identifier): ?array
    {
        return is_string($identifier) ? $this->getResource($resource, $identifier) : null;
    }

    private function request(): PendingRequest
    {
        $request = Http::baseUrl(rtrim((string) config('neo4j.url'), '/'))
            ->acceptJson()
            ->timeout((int) config('neo4j.timeout', 10))
            ->retry(
                (int) config('neo4j.retry_times', 2),
                (int) config('neo4j.retry_sleep', 100),
                throw: false,
            );

        return $request;
    }

    private function ensureRole(User $actor, array $roles): void
    {
        if (! in_array($actor->role, $roles, true)) {
            throw new AuthorizationException('This operation is not available for the current role.');
        }
    }

    private function serviceId(User $actor): string
    {
        $serviceId = $actor->soldier?->service_id;
        if (! is_string($serviceId) || $serviceId === '') {
            throw new InvalidArgumentException('The operator does not have a linked soldier record.');
        }

        return $serviceId;
    }

    private function checkpointCode(User $actor): string
    {
        $overview = $this->soldierOverview($actor);
        $checkpointCode = data_get($overview, 'checkpoint.checkpoint_code');

        if (! is_string($checkpointCode) || $checkpointCode === '') {
            throw new InvalidArgumentException('The operator has no assigned checkpoint.');
        }

        return $checkpointCode;
    }

    private function segment(string $value): string
    {
        return rawurlencode($value);
    }

    private function items(array $response): array
    {
        $items = $response['items'] ?? $response['data'] ?? $response;

        return is_array($items) && array_is_list($items) ? $items : [];
    }

    private function checkpointReference(array $item, string $side = ''): ?string
    {
        $prefix = $side !== '' ? $side.'_' : '';
        $reference = data_get($item, $prefix.'checkpoint_code')
            ?? data_get($item, $prefix.'code')
            ?? data_get($item, $prefix.'id')
            ?? data_get($item, $prefix.'name');

        return is_string($reference) ? $reference : null;
    }
}
