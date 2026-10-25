<?php

use App\Models\User;
use App\Models\Soldier;
use App\Models\Mission;
use App\Services\Neo4jService;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Layout('layouts::command'), Title('Dashboard')]
class extends Component
{
    public array $metrics = [];
    public array $deployments = [];
    public array $threatEvents = [];
    public array $missions = [];
    public array $users = [];
    public ?string $error = null;
    public ?string $userNotice = null;
    public ?string $editingUserId = null;
    public string $firstName = '';
    public string $lastName = '';
    public string $phone = '';
    public string $email = '';
    public string $password = '';
    public string $password_confirmation = '';
    public string $role = 'soldier';
    public string $status = 'active';
    public string $service_id = '';
    public string $dob = '';
    public string $bloodGroup = 'A+';
    public string $street = '';
    public string $city = '';
    public string $state = '';
    public string $pincode = '';
    public string $rank = '';

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
            $this->loadMissions($neo4j);
            if (Auth::user()?->role === 'system_admin') {
                $this->loadUsers();
            }

        } catch (ConnectionException|RequestException $exception) {
            report($exception);
            $this->error = 'Dashboard data is temporarily unavailable from the CBS service.';
        }
    }

    private function loadMissions(Neo4jService $neo4j): void
    {
        $missions = Mission::query()->latest()->limit(20)->get();

        $this->missions = $missions->map(fn (Mission $mission): array => [
            'id' => (string) $mission->getKey(),
            'name' => $mission->name,
            'troop_name' => $mission->troop_name
                ?? data_get($mission->planning_result, 'plan.troop_name', '—'),
            'status' => $mission->status,
            'feasible' => $mission->feasible,
            'created_at' => $mission->created_at?->format('Y-m-d H:i'),
        ])->all();
        $this->metrics[0]['value'] = (string) $missions->where('status', 'approved')->count();
        $this->metrics[0]['detail'] = 'Persisted mission plans';
    }

    public function startCreatingUser(): void
    {
        $this->authorizeUserManagement();
        $this->resetUserForm();
        $this->resetValidation();
        $this->userNotice = null;
    }

    public function editUser(string $userId): void
    {
        $this->authorizeUserManagement();

        $user = User::findOrFail($userId);
        $this->editingUserId = (string) $user->getKey();
        $this->firstName = (string) $user->firstName;
        $this->lastName = (string) $user->lastName;
        $this->phone = (string) $user->phone;
        $this->email = (string) $user->email;
        $this->password = '';
        $this->password_confirmation = '';
        $this->role = (string) $user->role;
        $this->status = (string) $user->status;
        $soldier = $user->soldier;
        $this->service_id = (string) ($soldier?->service_id ?? '');
        $this->dob = $soldier?->dob?->format('Y-m-d') ?? '';
        $this->bloodGroup = (string) ($soldier?->bloodGroup ?? 'A+');
        $this->street = (string) ($soldier?->street ?? '');
        $this->city = (string) ($soldier?->city ?? '');
        $this->state = (string) ($soldier?->state ?? '');
        $this->pincode = (string) ($soldier?->pincode ?? '');
        $this->rank = (string) ($soldier?->rank ?? '');
        $this->userNotice = null;
        $this->resetValidation();
    }

    public function saveUser(): void
    {
        $this->authorizeUserManagement();

        $rules = [
            'firstName' => ['required', 'string', 'max:255'],
            'lastName' => ['required', 'string', 'max:255'],
            'phone' => [
                'required',
                'string',
                'digits:10',
                Rule::unique('users', 'phone')->ignore($this->editingUserId),
            ],
            'email' => [
                'required',
                'email',
                'max:255',
                Rule::unique('users', 'email')->ignore($this->editingUserId),
            ],
            'role' => ['required', Rule::in([
                'soldier',
                'base_admin',
                'hq_admin',
                'system_admin',
            ])],
            'status' => ['required', Rule::in(['active', 'inactive', 'suspended'])],
            'service_id' => [
                'required',
                'string',
                'max:255',
                Rule::unique('soldiers', 'service_id')->ignore(
                    $this->editingUserId ? User::find($this->editingUserId)?->soldier?->service_id : null,
                ),
            ],
            'dob' => ['required', 'date', 'before:today'],
            'bloodGroup' => ['required', Rule::in(['A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-'])],
            'street' => ['required', 'string', 'max:255'],
            'city' => ['required', 'string', 'max:255'],
            'state' => ['required', 'string', 'max:255'],
            'pincode' => ['required', 'string', 'max:20'],
            'rank' => ['required', 'string', 'max:255'],
        ];

        if ($this->editingUserId === null) {
            $rules['password'] = ['required', 'string', 'min:8', 'confirmed'];
        } elseif ($this->password !== '') {
            $rules['password'] = ['string', 'min:8', 'confirmed'];
        }

        $validated = $this->validate($rules);
        $user = $this->editingUserId
            ? User::findOrFail($this->editingUserId)
            : new User();

        $user->firstName = $validated['firstName'];
        $user->lastName = $validated['lastName'];
        $user->phone = $validated['phone'];
        $user->email = $validated['email'];
        $user->role = $validated['role'];
        $user->status = $validated['status'];

        if ($this->password !== '') {
            $user->password = $validated['password'];
        }

        $isNewUser = $this->editingUserId === null;

        try {
            $user->save();

            $soldier = $user->soldier ?? new Soldier();
            $soldier->service_id = $validated['service_id'];
            $soldier->user_id = $user->getKey();
            $soldier->dob = $validated['dob'];
            $soldier->bloodGroup = $validated['bloodGroup'];
            $soldier->street = $validated['street'];
            $soldier->city = $validated['city'];
            $soldier->state = $validated['state'];
            $soldier->pincode = $validated['pincode'];
            $soldier->rank = $validated['rank'];
            $soldier->save();
        } catch (\Throwable $exception) {
            if ($isNewUser && $user->exists) {
                $user->delete();
            }

            throw $exception;
        }
        $this->loadUsers();
        $this->resetUserForm();
        $this->resetValidation();
        $this->userNotice = 'User record saved successfully.';
    }

    public function deleteUser(string $userId): void
    {
        $this->authorizeUserManagement();

        $currentUser = Auth::user();
        abort_unless($currentUser instanceof User, 403);
        abort_if((string) $currentUser->getKey() === $userId, 422, 'You cannot delete your own account.');

        $user = User::findOrFail($userId);
        $user->soldier?->delete();
        $user->delete();

        if ($this->editingUserId === $userId) {
            $this->resetUserForm();
        }

        $this->loadUsers();
        $this->userNotice = 'User record deleted successfully.';
    }

    private function authorizeUserManagement(): void
    {
        abort_unless(Auth::user()?->role === 'system_admin', 403);
    }

    private function loadUsers(): void
    {
        $this->users = User::query()
            ->orderBy('firstName')
            ->get()
            ->map(fn (User $user): array => [
                'id' => (string) $user->getKey(),
                'name' => $user->name(),
                'phone' => $user->phone,
                'email' => $user->email,
                'role' => $user->role,
                'status' => $user->status,
            ])
            ->values()
            ->all();
    }

    private function resetUserForm(): void
    {
        $this->editingUserId = null;
        $this->firstName = '';
        $this->lastName = '';
        $this->phone = '';
        $this->email = '';
        $this->password = '';
        $this->password_confirmation = '';
        $this->role = 'soldier';
        $this->status = 'active';
        $this->service_id = '';
        $this->dob = '';
        $this->bloodGroup = 'A+';
        $this->street = '';
        $this->city = '';
        $this->state = '';
        $this->pincode = '';
        $this->rank = '';
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

        <section class="bg-primary-container border border-outline-variant rounded p-4 md:p-6">
            <div class="flex flex-wrap justify-between items-center gap-3 mb-5 border-b border-outline-variant pb-3">
                <div>
                    <h2 class="text-lg font-semibold text-on-surface">Missions</h2>
                    <p class="text-xs text-on-surface-variant mt-1">Persisted CBS mission plans</p>
                </div>
                <span class="font-mono-data text-[10px] text-tertiary">{{ count($missions) }} STORED</span>
            </div>

            @if (count($missions) === 0)
                <p class="text-sm text-on-surface-variant">No missions have been planned yet.</p>
            @else
                <div class="space-y-2">
                        @foreach ($missions as $mission)
                            <a href="{{ route('missions.show', ['mission' => $mission['id']]) }}" wire:navigate class="block w-full text-left border p-3 border-outline-variant bg-surface hover:border-tertiary">
                                <div class="flex items-center justify-between gap-2">
                                    <span class="text-sm font-semibold text-on-surface">{{ $mission['name'] }} <span class="font-normal text-on-surface-variant">/ {{ $mission['troop_name'] }}</span></span>
                                    <span class="text-[10px] uppercase {{ $mission['feasible'] ? 'text-secure' : 'text-error' }}">{{ $mission['status'] }}</span>
                                </div>
                                <p class="mt-1 text-[10px] font-mono-data text-on-surface-variant">{{ $mission['created_at'] }}</p>
                            </a>
                        @endforeach
                </div>
            @endif
        </section>

        @if (Auth::user()?->role === 'system_admin')
            <section id="user-administration" class="bg-primary-container border border-outline-variant rounded p-4 md:p-6">
                <div class="flex flex-wrap items-center justify-between gap-3 mb-5 border-b border-outline-variant pb-3">
                    <div>
                        <h2 class="text-lg font-semibold text-on-surface">User Administration</h2>
                        <p class="text-xs text-on-surface-variant mt-1">
                            Create, update, and remove application user accounts.
                        </p>
                    </div>
                    <button
                        type="button"
                        wire:click="startCreatingUser"
                        class="bg-tertiary text-on-tertiary px-3 py-2 text-xs font-bold uppercase tracking-wider"
                    >
                        <x-lucide-user-plus class="inline-block w-4 h-4 mr-1" />
                        New User
                    </button>
                </div>

                @if ($userNotice)
                    <div class="mb-4 border border-secure-border bg-secure-bg p-3 text-sm text-secure" role="status">
                        {{ $userNotice }}
                    </div>
                @endif

                <form wire:submit="saveUser" class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-4 mb-6">
                    <div>
                        <label for="admin-first-name" class="block mb-2 text-[10px] font-bold uppercase tracking-wider text-on-surface-variant">First Name</label>
                        <input id="admin-first-name" wire:model="firstName" type="text" class="tactical-input w-full px-3 py-2">
                        @error('firstName') <p class="mt-1 text-xs text-error">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label for="admin-last-name" class="block mb-2 text-[10px] font-bold uppercase tracking-wider text-on-surface-variant">Last Name</label>
                        <input id="admin-last-name" wire:model="lastName" type="text" class="tactical-input w-full px-3 py-2">
                        @error('lastName') <p class="mt-1 text-xs text-error">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label for="admin-phone" class="block mb-2 text-[10px] font-bold uppercase tracking-wider text-on-surface-variant">Phone</label>
                        <input id="admin-phone" wire:model="phone" type="text" inputmode="numeric" maxlength="10" class="tactical-input w-full px-3 py-2">
                        @error('phone') <p class="mt-1 text-xs text-error">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label for="admin-email" class="block mb-2 text-[10px] font-bold uppercase tracking-wider text-on-surface-variant">Email</label>
                        <input id="admin-email" wire:model="email" type="email" class="tactical-input w-full px-3 py-2">
                        @error('email') <p class="mt-1 text-xs text-error">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label for="admin-password" class="block mb-2 text-[10px] font-bold uppercase tracking-wider text-on-surface-variant">
                            Password {{ $editingUserId ? '(optional)' : '' }}
                        </label>
                        <input id="admin-password" wire:model="password" type="password" class="tactical-input w-full px-3 py-2">
                        @error('password') <p class="mt-1 text-xs text-error">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label for="admin-password-confirmation" class="block mb-2 text-[10px] font-bold uppercase tracking-wider text-on-surface-variant">Confirm Password</label>
                        <input id="admin-password-confirmation" wire:model="password_confirmation" type="password" class="tactical-input w-full px-3 py-2">
                    </div>
                    <div>
                        <label for="admin-role" class="block mb-2 text-[10px] font-bold uppercase tracking-wider text-on-surface-variant">Role</label>
                        <select id="admin-role" wire:model="role" class="tactical-input w-full px-3 py-2">
                            @foreach (['soldier' => 'Soldier', 'base_admin' => 'Base Admin', 'hq_admin' => 'HQ Admin', 'system_admin' => 'System Admin'] as $value => $label)
                                <option value="{{ $value }}">{{ $label }}</option>
                            @endforeach
                        </select>
                        @error('role') <p class="mt-1 text-xs text-error">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label for="admin-status" class="block mb-2 text-[10px] font-bold uppercase tracking-wider text-on-surface-variant">Status</label>
                        <select id="admin-status" wire:model="status" class="tactical-input w-full px-3 py-2">
                            @foreach (['active' => 'Active', 'inactive' => 'Inactive', 'suspended' => 'Suspended'] as $value => $label)
                                <option value="{{ $value }}">{{ $label }}</option>
                            @endforeach
                        </select>
                        @error('status') <p class="mt-1 text-xs text-error">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label for="admin-service-id" class="block mb-2 text-[10px] font-bold uppercase tracking-wider text-on-surface-variant">Service ID</label>
                        <input id="admin-service-id" wire:model="service_id" type="text" class="tactical-input w-full px-3 py-2">
                        @error('service_id') <p class="mt-1 text-xs text-error">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label for="admin-dob" class="block mb-2 text-[10px] font-bold uppercase tracking-wider text-on-surface-variant">Date of Birth</label>
                        <input id="admin-dob" wire:model="dob" type="date" class="tactical-input w-full px-3 py-2">
                        @error('dob') <p class="mt-1 text-xs text-error">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label for="admin-blood-group" class="block mb-2 text-[10px] font-bold uppercase tracking-wider text-on-surface-variant">Blood Group</label>
                        <select id="admin-blood-group" wire:model="bloodGroup" class="tactical-input w-full px-3 py-2">
                            @foreach (['A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-'] as $group)
                                <option value="{{ $group }}">{{ $group }}</option>
                            @endforeach
                        </select>
                        @error('bloodGroup') <p class="mt-1 text-xs text-error">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label for="admin-rank" class="block mb-2 text-[10px] font-bold uppercase tracking-wider text-on-surface-variant">Rank</label>
                        <input id="admin-rank" wire:model="rank" type="text" class="tactical-input w-full px-3 py-2">
                        @error('rank') <p class="mt-1 text-xs text-error">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label for="admin-street" class="block mb-2 text-[10px] font-bold uppercase tracking-wider text-on-surface-variant">Street</label>
                        <input id="admin-street" wire:model="street" type="text" class="tactical-input w-full px-3 py-2">
                        @error('street') <p class="mt-1 text-xs text-error">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label for="admin-city" class="block mb-2 text-[10px] font-bold uppercase tracking-wider text-on-surface-variant">City</label>
                        <input id="admin-city" wire:model="city" type="text" class="tactical-input w-full px-3 py-2">
                        @error('city') <p class="mt-1 text-xs text-error">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label for="admin-state" class="block mb-2 text-[10px] font-bold uppercase tracking-wider text-on-surface-variant">State</label>
                        <input id="admin-state" wire:model="state" type="text" class="tactical-input w-full px-3 py-2">
                        @error('state') <p class="mt-1 text-xs text-error">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label for="admin-pincode" class="block mb-2 text-[10px] font-bold uppercase tracking-wider text-on-surface-variant">Pincode</label>
                        <input id="admin-pincode" wire:model="pincode" type="text" class="tactical-input w-full px-3 py-2">
                        @error('pincode') <p class="mt-1 text-xs text-error">{{ $message }}</p> @enderror
                    </div>
                    <div class="md:col-span-2 xl:col-span-4 flex gap-3">
                        <button type="submit" class="bg-tertiary text-on-tertiary px-4 py-2 text-xs font-bold uppercase tracking-wider">
                            {{ $editingUserId ? 'Update User' : 'Create User' }}
                        </button>
                        @if ($editingUserId)
                            <button type="button" wire:click="startCreatingUser" class="border border-outline-variant px-4 py-2 text-xs font-bold uppercase tracking-wider text-on-surface-variant">
                                Cancel
                            </button>
                        @endif
                    </div>
                </form>

                <div class="overflow-x-auto">
                    <table class="w-full min-w-[760px] text-left">
                        <thead>
                            <tr class="border-b border-outline-variant text-[10px] uppercase tracking-wider text-on-surface-variant">
                                <th class="p-3">Name</th>
                                <th class="p-3">Email</th>
                                <th class="p-3">Phone</th>
                                <th class="p-3">Role</th>
                                <th class="p-3">Status</th>
                                <th class="p-3 text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($users as $managedUser)
                                <tr wire:key="managed-user-{{ $managedUser['id'] }}" class="border-b border-outline-variant/70">
                                    <td class="p-3 text-sm text-on-surface">{{ $managedUser['name'] }}</td>
                                    <td class="p-3 text-sm text-on-surface-variant">{{ $managedUser['email'] }}</td>
                                    <td class="p-3 font-mono-data text-xs text-on-surface-variant">{{ $managedUser['phone'] }}</td>
                                    <td class="p-3 text-xs uppercase text-tertiary">{{ str($managedUser['role'])->replace('_', ' ') }}</td>
                                    <td class="p-3 text-xs uppercase text-on-surface-variant">{{ $managedUser['status'] }}</td>
                                    <td class="p-3 text-right whitespace-nowrap">
                                        <button type="button" wire:click="editUser('{{ $managedUser['id'] }}')" class="text-xs font-semibold text-tertiary hover:text-tertiary-fixed">
                                            Edit
                                        </button>
                                        @if ((string) Auth::user()->getKey() !== $managedUser['id'])
                                            <button type="button" wire:click="deleteUser('{{ $managedUser['id'] }}')" wire:confirm="Delete this user account?" class="ml-3 text-xs font-semibold text-error hover:underline">
                                                Delete
                                            </button>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </section>
        @endif
    @endif
</div>
