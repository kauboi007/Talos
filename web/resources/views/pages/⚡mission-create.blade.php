<?php

use App\Models\Mission;
use App\Services\Neo4jService;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Layout('layouts::command'), Title('Create Mission')]
class extends Component
{
    public array $troops = [];
    public array $checkpoints = [];
    public array $missionRows = [
        ['troop_id' => '', 'start' => '', 'end' => ''],
    ];
    public string $missionName = '';
    public ?array $result = null;
    public array $approvedTroops = [];
    public ?string $error = null;

    public function mount(Neo4jService $neo4j): void
    {
        try {
            $options = $neo4j->missionOptions();
            $this->troops = $options['troops'];
            $this->checkpoints = $options['checkpoints'];
        } catch (ConnectionException|RequestException $exception) {
            report($exception);
            $this->error = 'Mission options are temporarily unavailable from the CBS service.';
        }
    }

    public function addTroop(): void
    {
        $this->missionRows[] = ['troop_id' => '', 'start' => '', 'end' => ''];
    }

    public function removeTroop(int $index): void
    {
        if (count($this->missionRows) > 1) {
            unset($this->missionRows[$index]);
            $this->missionRows = array_values($this->missionRows);
        }
    }

    public function plan(Neo4jService $neo4j): void
    {
        $this->error = null;
        $this->result = null;

        $validated = $this->validate([
            'missionRows' => ['required', 'array', 'min:1'],
            'missionRows.*.troop_id' => ['required', 'string'],
            'missionRows.*.start' => ['required', 'string'],
            'missionRows.*.end' => ['required', 'string'],
        ]);

        try {
            $this->result = $neo4j->planMission($validated['missionRows']);
            $this->approvedTroops = [];
        } catch (ConnectionException|RequestException $exception) {
            report($exception);
            $detail = $exception instanceof RequestException
                ? $exception->response?->json('detail')
                : null;
            $this->error = is_string($detail)
                ? $detail
                : ($exception instanceof RequestException
                    ? 'CBS mission planning failed.'
                    : 'CBS mission planning is temporarily unavailable.');
        }
    }

    public function approveTroop(string $troopName): void
    {
        $plan = null;
        foreach (data_get($this->result, 'plans', []) as $candidate) {
            if (data_get($candidate, 'troop_name') === $troopName) {
                $plan = $candidate;
                break;
            }
        }

        if (! is_array($plan) || ! is_string($troopName) || $troopName === '') {
            $this->error = 'The selected troop plan is invalid.';
            return;
        }

        if (! data_get($this->result, 'feasible', false)) {
            $this->error = 'Only feasible troop plans can be approved.';
            return;
        }

        if (in_array($troopName, $this->approvedTroops, true)) {
            return;
        }

        $mission = Mission::create([
            'name' => $this->missionName !== ''
                ? $this->missionName
                : 'Mission '.now()->format('Y-m-d H:i'),
            'troop_name' => $troopName,
            'created_by' => Auth::id(),
            'approved_by' => Auth::id(),
            'approved_at' => now(),
            'status' => 'approved',
            'feasible' => true,
            'planning_result' => [
                'feasible' => true,
                'plan' => $plan,
            ],
        ]);

        $this->approvedTroops[] = $troopName;
        session()->flash('mission_notice', "{$troopName} mission approved.");
    }
};
?>

<div class="p-4 md:p-6 space-y-6">
    <div>
        <p class="font-mono-data text-[10px] uppercase tracking-[0.2em] text-tertiary">
            Missions / Planning
        </p>
        <h1 class="mt-1 text-2xl font-bold tracking-tight text-on-surface">Create CBS Mission</h1>
        <p class="mt-1 text-sm text-on-surface-variant">
            Submit troop routes to the CBS planner. The response is persisted for dashboard review.
        </p>
    </div>

    @if ($error)
        <div class="border border-error/30 bg-error-container p-4 text-sm text-error" role="alert">
            {{ $error }}
        </div>
    @endif
    @if (session('mission_notice'))
        <div class="border border-secure/30 bg-secure-container p-4 text-sm text-secure" role="status">
            {{ session('mission_notice') }}
        </div>
    @endif

    <section class="bg-primary-container border border-outline-variant rounded p-4 md:p-6">
        <div class="flex items-center justify-between gap-3 mb-5 border-b border-outline-variant pb-4">
            <div class="flex items-center gap-3">
                <x-lucide-route class="h-6 w-6 shrink-0 text-tertiary" />
                <div>
                    <h2 class="text-base font-semibold text-on-surface">CBS mission planning</h2>
                    <p class="mt-1 text-xs text-on-surface-variant">
                        Select a troop and its start and end checkpoints.
                    </p>
                </div>
            </div>
            <button type="button" wire:click="addTroop" class="btn-tactical-outline px-3 py-2 text-[10px] font-bold uppercase tracking-wider">
                Add troop
            </button>
        </div>

        <form wire:submit="plan" class="space-y-4">
            <div>
                <label class="block mb-2 text-[10px] font-bold uppercase tracking-wider text-on-surface-variant">Mission name</label>
                <input wire:model="missionName" type="text" class="tactical-input w-full px-3 py-3" placeholder="Planned Mission's name">
            </div>
            @foreach ($missionRows as $index => $row)
                <div wire:key="mission-row-{{ $index }}" class="grid grid-cols-1 md:grid-cols-4 gap-4 items-end">
                    <div>
                        <label class="block mb-2 text-[10px] font-bold uppercase tracking-wider text-on-surface-variant">Troop</label>
                        <select wire:model="missionRows.{{ $index }}.troop_id" class="tactical-input w-full px-3 py-3">
                            <option value="">Select troop</option>
                            @foreach ($troops as $troop)
                                <option value="{{ data_get($troop, 'name') }}">{{ data_get($troop, 'name') }}</option>
                            @endforeach
                        </select>
                        @error("missionRows.{$index}.troop_id") <p class="mt-1 text-xs text-error">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block mb-2 text-[10px] font-bold uppercase tracking-wider text-on-surface-variant">Start checkpoint</label>
                        <select wire:model="missionRows.{{ $index }}.start" class="tactical-input w-full px-3 py-3">
                            <option value="">Select checkpoint</option>
                            @foreach ($checkpoints as $checkpoint)
                                <option value="{{ data_get($checkpoint, 'checkpoint_code') }}">{{ data_get($checkpoint, 'name') }}</option>
                            @endforeach
                        </select>
                        @error("missionRows.{$index}.start") <p class="mt-1 text-xs text-error">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block mb-2 text-[10px] font-bold uppercase tracking-wider text-on-surface-variant">End checkpoint</label>
                        <select wire:model="missionRows.{{ $index }}.end" class="tactical-input w-full px-3 py-3">
                            <option value="">Select checkpoint</option>
                            @foreach ($checkpoints as $checkpoint)
                                <option value="{{ data_get($checkpoint, 'checkpoint_code') }}">{{ data_get($checkpoint, 'name') }}</option>
                            @endforeach
                        </select>
                        @error("missionRows.{$index}.end") <p class="mt-1 text-xs text-error">{{ $message }}</p> @enderror
                    </div>
                    <button type="button" wire:click="removeTroop({{ $index }})" class="border border-outline-variant px-3 py-3 text-xs text-error hover:bg-error-container" @disabled(count($missionRows) === 1)>
                        Remove
                    </button>
                </div>
            @endforeach

            <button type="submit" class="bg-tertiary text-on-tertiary px-4 py-3 text-xs font-bold uppercase tracking-wider">
                Plan mission
            </button>
        </form>
    </section>

    @if ($result)
        <section class="bg-primary-container border border-outline-variant rounded p-4 md:p-6">
            <div class="flex items-center justify-between gap-3 mb-5 border-b border-outline-variant pb-4">
                <div>
                    <h2 class="text-base font-semibold text-on-surface">Planning Result</h2>
                    <p class="mt-1 text-xs text-on-surface-variant">
                        {{ data_get($result, 'feasible') ? 'Collision-free plan generated.' : 'No feasible plan was found.' }}
                    </p>
                </div>
                <span class="font-mono-data text-[10px] uppercase {{ data_get($result, 'feasible') ? 'text-secure' : 'text-error' }}">
                    {{ data_get($result, 'feasible') ? 'FEASIBLE' : 'INFEASIBLE' }}
                </span>
            </div>

            @if (! data_get($result, 'feasible'))
                <p class="text-sm text-error">{{ data_get($result, 'reason', 'The CBS planner could not produce a route.') }}</p>
            @else
                <div class="space-y-5">
                    @foreach (data_get($result, 'plans', []) as $plan)
                        <article wire:key="planned-troop-{{ $loop->index }}" class="border border-outline-variant p-4">
                            <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-4">
                                <div><p class="text-[10px] uppercase tracking-wider text-on-surface-variant">Troop</p><p class="mt-1 text-sm font-semibold text-on-surface">{{ data_get($plan, 'troop_name', '—') }}</p></div>
                                <div><p class="text-[10px] uppercase tracking-wider text-on-surface-variant">Speed</p><p class="mt-1 text-sm font-semibold text-on-surface">{{ data_get($plan, 'speed_kmh', '—') }} km/h</p></div>
                                <div><p class="text-[10px] uppercase tracking-wider text-on-surface-variant">Travel time</p><p class="mt-1 text-sm font-semibold text-on-surface">{{ data_get($plan, 'total_time_hr', '—') }} hr</p></div>
                                <div><p class="text-[10px] uppercase tracking-wider text-on-surface-variant">Threat exposure</p><p class="mt-1 text-sm font-semibold text-on-surface">{{ data_get($plan, 'threat_exposure', '—') }}</p></div>
                            </div>
                            <div class="mb-4">
                                @if (in_array(data_get($plan, 'troop_name'), $approvedTroops, true))
                                    <span class="text-xs font-bold uppercase text-secure">Approved</span>
                                @else
                                    <button type="button" wire:click="approveTroop('{{ data_get($plan, 'troop_name') }}')" class="bg-tertiary text-on-tertiary px-3 py-2 text-[10px] font-bold uppercase tracking-wider">
                                        Approve mission
                                    </button>
                                @endif
                            </div>
                            <div class="overflow-x-auto">
                                <table class="w-full min-w-[520px] text-left">
                                    <thead><tr class="border-b border-outline-variant text-[10px] uppercase tracking-wider text-on-surface-variant"><th class="p-2">Checkpoint</th><th class="p-2">Arrival (hr)</th><th class="p-2">Departure (hr)</th></tr></thead>
                                    <tbody>
                                        @foreach (data_get($plan, 'path', []) as $stop)
                                            <tr class="border-b border-outline-variant/70">
                                                <td class="p-2 text-sm text-on-surface">{{ data_get($stop, 'checkpoint', '—') }}</td>
                                                <td class="p-2 font-mono-data text-xs text-on-surface-variant">{{ data_get($stop, 'arrival_hr', '—') }}</td>
                                                <td class="p-2 font-mono-data text-xs text-on-surface-variant">{{ data_get($stop, 'departure_hr', '—') }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </article>
                    @endforeach
                </div>
            @endif
        </section>
    @endif
</div>
