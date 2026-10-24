<?php

namespace App\Services;

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
        return $this->get("/api/soldiers/{$this->serviceId($actor)}/overview");
    }

    public function reportingChain(User $actor): array
    {
        return $this->get("/api/soldiers/{$this->serviceId($actor)}/reporting-chain");
    }

    public function checkpointForSoldier(User $actor): array
    {
        return $this->get("/api/soldiers/{$this->serviceId($actor)}/checkpoint");
    }

    public function troopForSoldier(User $actor): array
    {
        return $this->get("/api/soldiers/{$this->serviceId($actor)}/troop");
    }

    public function subordinates(User $actor): array
    {
        $this->ensureRole($actor, self::ADMIN_ROLES);

        if ($actor->role === 'system_admin') {
            return $this->get('/api/soldiers/subordinates');
        }

        return $this->get('/api/soldiers/subordinates', [
            'service_id' => $this->serviceId($actor),
        ]);
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

    private function get(string $uri, array $query = []): array
    {
        $response = $this->request()->get($uri, $query);
        $data = $response->throw()->json();

        if (! is_array($data)) {
            throw new UnexpectedValueException('The CBS service returned a non-object response.');
        }

        return $data;
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
