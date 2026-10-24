<?php

use App\Services\Neo4jService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Layout('layouts::command'), Title('Personnel')]
class extends Component
{
    public array $soldiers = [];
    public ?string $error = null;

    public function mount(Neo4jService $neo4j): void
    {
        $user = Auth::user();

        try {
            $this->soldiers = $neo4j->subordinates($user);
        } catch (ConnectionException|RequestException $exception) {
            report($exception);
            $this->error = 'Personnel data is temporarily unavailable from the CBS service.';
        }
    }
};
?>

<div class="p-4 md:p-6 space-y-6">
    <div>
        <p class="font-mono-data text-[10px] uppercase tracking-[0.2em] text-tertiary">
            Operations / Personnel
        </p>
        <h1 class="mt-1 text-2xl font-bold tracking-tight text-on-surface">Personnel in Your Command</h1>
        <p class="mt-1 text-sm text-on-surface-variant">
            Soldiers and officers returned by the hierarchy service.
        </p>
    </div>

    @if ($error)
        <div class="border border-error/30 bg-error-container p-4 text-sm text-error" role="alert">
            {{ $error }}
        </div>
    @elseif (count($soldiers) === 0)
        <div class="bg-primary-container border border-outline-variant p-6 text-sm text-on-surface-variant">
            No subordinate personnel were returned for this command.
        </div>
    @else
        <section class="bg-primary-container border border-outline-variant rounded p-4 md:p-6">
            <div class="overflow-x-auto">
                <table class="w-full min-w-[640px] text-left">
                    <thead>
                        <tr class="border-b border-outline-variant text-[10px] uppercase tracking-wider text-on-surface-variant">
                            <th class="p-3">Service ID</th>
                            <th class="p-3">Name</th>
                            <th class="p-3">Rank</th>
                            <th class="p-3">Checkpoint</th>
                            <th class="p-3">Troop</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($soldiers as $soldier)
                            <tr wire:key="personnel-{{ data_get($soldier, 'service_id', $loop->index) }}" class="border-b border-outline-variant/70">
                                <td class="p-3 font-mono-data text-xs text-tertiary">{{ data_get($soldier, 'service_id', '—') }}</td>
                                <td class="p-3 text-sm text-on-surface">{{ data_get($soldier, 'name', '—') }}</td>
                                <td class="p-3 text-sm text-on-surface-variant">{{ data_get($soldier, 'rank', '—') }}</td>
                                <td class="p-3 text-sm text-on-surface-variant">{{ data_get($soldier, 'checkpoint.checkpoint_code', data_get($soldier, 'checkpoint_code', '—')) }}</td>
                                <td class="p-3 text-sm text-on-surface-variant">{{ data_get($soldier, 'troop.name', data_get($soldier, 'troop_name', '—')) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </section>
    @endif
</div>
