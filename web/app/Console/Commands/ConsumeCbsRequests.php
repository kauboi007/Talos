<?php

namespace App\Console\Commands;

use App\Models\Transport;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Redis;
use Throwable;

class ConsumeCbsRequests extends Command
{
    protected $signature = 'talos:cbs-worker';

    protected $description = 'Consume CBS vehicle speed requests from Redis Streams';

    public function handle(): int
    {
        $requestStream = (string) env('CBS_REQUEST_STREAM', 'talos:cbs:requests');
        $responseStream = (string) env('CBS_RESPONSE_STREAM', 'talos:cbs:responses');
        $lastId = '0-0';

        $this->info("Consuming CBS requests from {$requestStream}.");

        while (true) {
            try {
                $entries = Redis::connection()->executeRaw([
                    'XREAD',
                    'BLOCK',
                    5000,
                    'COUNT',
                    10,
                    'STREAMS',
                    $requestStream,
                    $lastId,
                ]);
            } catch (Throwable $exception) {
                report($exception);
                $this->error('Redis request stream unavailable: '.$exception->getMessage());
                sleep(1);

                continue;
            }

            foreach ($this->entries($entries, $requestStream) as $entry) {
                [$entryId, $fields] = $entry;
                $lastId = $entryId;
                $correlationId = $fields['correlation_id'] ?? '';
                $vehicleId = $fields['vehicle_id'] ?? '';

                if ($correlationId === '' || $vehicleId === '') {
                    continue;
                }

                $response = [
                    'correlation_id' => $correlationId,
                    'vehicle_id' => $vehicleId,
                ];

                try {
                    $transport = Transport::find($vehicleId);
                    if (! $transport) {
                        throw new \RuntimeException("Transport [{$vehicleId}] was not found.");
                    }

                    $response['speed_kmh'] = (string) $transport->speed_kmh;
                } catch (Throwable $exception) {
                    report($exception);
                    $response['error'] = $exception->getMessage();
                }

                try {
                    Redis::connection()->executeRaw([
                        'XADD',
                        $responseStream,
                        '*',
                        ...$this->streamFields($response),
                    ]);
                } catch (Throwable $exception) {
                    report($exception);
                    $this->error('Redis response stream unavailable: '.$exception->getMessage());
                }
            }
        }
    }

    private function entries(mixed $result, string $stream): array
    {
        if (! is_array($result)) {
            return [];
        }

        if (array_is_list($result)) {
            foreach ($result as $streamResult) {
                if (is_array($streamResult)
                    && count($streamResult) === 2
                    && (string) $streamResult[0] === $stream) {
                    return $this->normalizeEntries($streamResult[1]);
                }
            }

            return [];
        }

        foreach ($result as $streamName => $streamEntries) {
            if ((string) $streamName === $stream && is_array($streamEntries)) {
                return $this->normalizeEntries($streamEntries);
            }
        }

        return [];
    }

    private function normalizeEntries(array $streamEntries): array
    {
        $entries = [];

        foreach ($streamEntries as $entryId => $fields) {
            if (is_string($entryId) && is_array($fields)) {
                $entries[] = [$entryId, $this->fieldMap($fields)];
            } elseif (is_array($fields) && count($fields) === 2) {
                $entries[] = [$fields[0], $this->fieldMap($fields[1])];
            }
        }

        return $entries;
    }

    private function fieldMap(array $fields): array
    {
        if (! array_is_list($fields)) {
            return $fields;
        }

        $mapped = [];
        foreach (array_chunk($fields, 2) as $pair) {
            if (count($pair) === 2) {
                $mapped[(string) $pair[0]] = (string) $pair[1];
            }
        }

        return $mapped;
    }

    private function streamFields(array $fields): array
    {
        $arguments = [];

        foreach ($fields as $key => $value) {
            $arguments[] = $key;
            $arguments[] = (string) $value;
        }

        return $arguments;
    }
}
