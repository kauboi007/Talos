<?php

use App\Models\User;
use App\Services\Neo4jService;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Layout('layouts::command'), Title('My Profile')]
class extends Component
{
    public string $firstName = '';
    public string $lastName = '';
    public string $phone = '';
    public string $email = '';

    public string $dob = '';
    public string $bloodGroup = '';
    public string $street = '';
    public string $city = '';
    public string $state = '';
    public string $pincode = '';

    public bool $editing = false;
    public bool $saved = false;
    public ?array $currentBase = null;
    public ?array $reportingOfficer = null;
    public ?array $currentTroop = null;
    public ?string $operationalError = null;

    public function mount(): void
    {
        $this->loadProfile();
        $this->loadOperationalData();
    }

    private function currentUser(): User
    {
        $user = Auth::user();

        abort_unless($user instanceof User, 403);

        return $user;
    }

    private function loadProfile(): void
    {
        $user = $this->currentUser();

        $this->firstName = $user->firstName;
        $this->lastName = $user->lastName;
        $this->phone = $user->phone;
        $this->email = $user->email;

        $soldier = $user->soldier;

        if ($soldier) {
            $this->dob = $soldier->dob
                ? $soldier->dob->format('Y-m-d')
                : '';

            $this->bloodGroup = $soldier->bloodGroup;
            $this->street = $soldier->street;
            $this->city = $soldier->city;
            $this->state = $soldier->state;
            $this->pincode = $soldier->pincode;
        }
    }

    private function loadOperationalData(): void
    {
        $user = $this->currentUser();
        $this->currentBase = null;
        $this->reportingOfficer = null;
        $this->currentTroop = null;
        $this->operationalError = null;

        if (! $user->soldier) {
            return;
        }

        try {
            $neo4j = app(Neo4jService::class);
            $overview = $neo4j->soldierOverview($user);
            $chain = $neo4j->reportingChain($user);

            $this->currentBase = data_get($overview, 'checkpoint')
                ?? data_get($overview, 'current_base');
            $this->currentTroop = data_get($overview, 'troop');
            $this->reportingOfficer = data_get($overview, 'reporting_officer')
                ?? data_get($chain, 'immediate')
                ?? data_get($chain, '0');
        } catch (ConnectionException|RequestException $exception) {
            report($exception);
            $this->operationalError = 'Operational assignment data is temporarily unavailable.';
        }
    }

    public function edit(): void
    {
        $this->loadProfile();

        $this->resetValidation();
        $this->saved = false;
        $this->editing = true;
    }

    public function cancel(): void
    {
        $this->loadProfile();

        $this->resetValidation();
        $this->editing = false;
        $this->saved = false;
    }

    public function update(): void
    {
        $user = $this->currentUser();
        $soldier = $user->soldier;

        $rules = [
            'firstName' => ['required', 'string', 'max:255'],
            'lastName' => ['required', 'string', 'max:255'],

            'phone' => [
                'required',
                'string',
                'digits:10',
                Rule::unique('users', 'phone')
                    ->ignore($user->getKey()),
            ],

            'email' => [
                'required',
                'email',
                'max:255',
                Rule::unique('users', 'email')
                    ->ignore($user->getKey()),
            ],
        ];

        if ($soldier) {
            $rules += [
                'dob' => [
                    'required',
                    'date',
                    'before_or_equal:today',
                ],

                'bloodGroup' => [
                    'required',
                    Rule::in([
                        'A+', 'A-', 'B+', 'B-',
                        'AB+', 'AB-', 'O+', 'O-',
                    ]),
                ],

                'street' => ['required', 'string', 'max:255'],
                'city' => ['required', 'string', 'max:255'],
                'state' => ['required', 'string', 'max:255'],
                'pincode' => [
                    'required',
                    'string',
                    'max:20',
                ],
            ];
        }

        $validated = $this->validate($rules);

        $emailChanged = $user->email !== $validated['email'];

        $user->firstName = $validated['firstName'];
        $user->lastName = $validated['lastName'];
        $user->phone = $validated['phone'];
        $user->email = $validated['email'];

        // A changed email must be verified again.
        if ($emailChanged) {
            $user->email_verified_at = null;
        }

        $user->save();

        if ($soldier) {
            $soldier->dob = $validated['dob'];
            $soldier->bloodGroup = $validated['bloodGroup'];
            $soldier->street = $validated['street'];
            $soldier->city = $validated['city'];
            $soldier->state = $validated['state'];
            $soldier->pincode = $validated['pincode'];

            $soldier->save();
        }

        $this->loadProfile();

        $this->resetValidation();
        $this->editing = false;
        $this->saved = true;
    }
};
?>


<div class="p-4 md:p-6 space-y-6">
    @php
        $user = auth()->user();
        $soldier = $user->soldier;
    @endphp

    {{-- Page heading --}}
    <div class="flex flex-wrap items-center justify-between gap-4">
        <div>
            <p class="font-mono-data text-[10px] uppercase tracking-[0.2em] text-tertiary">
                Personnel / Account
            </p>
            <h1 class="mt-1 text-2xl font-bold tracking-tight text-on-surface">
                My Profile
            </h1>
            <p class="mt-1 text-sm text-on-surface-variant">
                Manage your account and personal information.
            </p>
        </div>

        @if (!$editing)
            <button
                type="button"
                wire:click="edit"
                class="bg-tertiary text-on-tertiary py-3 px-4 rounded
                       text-xs font-bold uppercase tracking-wider flex
                       items-center justify-center gap-2
                       hover:bg-tertiary-fixed transition-colors
                       shadow-[0_0_12px_rgba(255,185,95,0.15)]"
            >
                <x-lucide-pencil class="w-4 h-4" />
                Edit Profile
            </button>
        @endif
    </div>

    {{-- Success feedback --}}
    @if ($saved)
        <div
            role="status"
            class="flex items-start gap-3 border border-secure-border
                   bg-secure-bg p-4 text-sm text-secure"
        >
            <x-lucide-check-circle class="w-5 h-5 shrink-0" />
            <div>
                <p class="font-semibold">Profile updated</p>
                <p class="mt-1 text-xs opacity-80">
                    Your account information has been saved successfully.
                </p>
            </div>
        </div>
    @endif

    <form wire:submit="update" class="space-y-6">

        {{-- Operational assignment --}}
        @if ($soldier)
            <section class="bg-primary-container border border-outline-variant rounded p-4 md:p-6">
                <div class="flex items-center gap-3 mb-5 border-b border-outline-variant pb-4">
                    <div class="w-10 h-10 flex items-center justify-center
                                bg-surface-container-high border border-outline-variant
                                text-tertiary">
                        <x-lucide-network class="w-5 h-5" />
                    </div>
                    <div>
                        <h2 class="text-base font-semibold text-on-surface">Operational Assignment</h2>
                        <p class="text-xs text-on-surface-variant mt-1">
                            Current dynamic hierarchy and checkpoint information
                        </p>
                    </div>
                </div>

                @if ($operationalError)
                    <p class="border border-error/30 bg-error-container p-3 text-sm text-error" role="alert">
                        {{ $operationalError }}
                    </p>
                @else
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div class="bg-surface-container-low border border-outline-variant p-4">
                            <p class="text-[10px] uppercase tracking-wider text-on-surface-variant">Current Base</p>
                            <p class="mt-2 text-sm font-semibold text-on-surface">
                                {{ data_get($currentBase, 'name', 'Not assigned') }}
                            </p>
                            <p class="mt-1 font-mono-data text-[10px] text-tertiary">
                                {{ data_get($currentBase, 'checkpoint_code', '—') }}
                            </p>
                        </div>
                        <div class="bg-surface-container-low border border-outline-variant p-4">
                            <p class="text-[10px] uppercase tracking-wider text-on-surface-variant">Reporting Officer</p>
                            <p class="mt-2 text-sm font-semibold text-on-surface">
                                {{ data_get($reportingOfficer, 'name', 'Not assigned') }}
                            </p>
                            <p class="mt-1 font-mono-data text-[10px] text-tertiary">
                                {{ data_get($reportingOfficer, 'service_id', '—') }}
                            </p>
                        </div>
                        <div class="bg-surface-container-low border border-outline-variant p-4">
                            <p class="text-[10px] uppercase tracking-wider text-on-surface-variant">Current Troop</p>
                            <p class="mt-2 text-sm font-semibold text-on-surface">
                                {{ data_get($currentTroop, 'name', 'Not assigned') }}
                            </p>
                            <p class="mt-1 text-xs text-on-surface-variant">
                                {{ data_get($currentTroop, 'type', '—') }}
                            </p>
                        </div>
                    </div>
                @endif
            </section>
        @endif

        {{-- Account information --}}
        <section class="bg-primary-container border border-outline-variant rounded p-4 md:p-6">
            <div class="flex items-center gap-3 mb-5 border-b border-outline-variant pb-4">
                <div class="w-10 h-10 flex items-center justify-center
                            bg-surface-container-high border border-outline-variant
                            text-tertiary">
                    <x-lucide-user-round class="w-5 h-5" />
                </div>
                <div>
                    <h2 class="text-base font-semibold text-on-surface">
                        Account Information
                    </h2>
                    <p class="text-xs text-on-surface-variant mt-1">
                        Account identity and contact details
                    </p>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                <div>
                    <label for="firstName"
                           class="block mb-2 text-[10px] font-bold uppercase
                                  tracking-wider text-on-surface-variant">
                        First Name
                    </label>
                    <input
                        id="firstName"
                        type="text"
                        wire:model="firstName"
                        @disabled(! $editing)
                        autocomplete="given-name"
                        class="tactical-input w-full px-3 py-3
                               disabled:opacity-60"
                    />
                    @error('firstName')
                        <p class="mt-2 text-xs text-error">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="lastName"
                           class="block mb-2 text-[10px] font-bold uppercase
                                  tracking-wider text-on-surface-variant">
                        Last Name
                    </label>
                    <input
                        id="lastName"
                        type="text"
                        wire:model="lastName"
                        @disabled(! $editing)
                        autocomplete="family-name"
                        class="tactical-input w-full px-3 py-3
                               disabled:opacity-60"
                    />
                    @error('lastName')
                        <p class="mt-2 text-xs text-error">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="phone"
                           class="block mb-2 text-[10px] font-bold uppercase
                                  tracking-wider text-on-surface-variant">
                        Phone Number
                    </label>
                    <input
                        id="phone"
                        type="tel"
                        inputmode="numeric"
                        maxlength="10"
                        wire:model="phone"
                        @disabled(! $editing)
                        autocomplete="tel"
                        class="tactical-input w-full px-3 py-3
                               disabled:opacity-60"
                    />
                    @error('phone')
                        <p class="mt-2 text-xs text-error">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="email"
                           class="block mb-2 text-[10px] font-bold uppercase
                                  tracking-wider text-on-surface-variant">
                        Email Address
                    </label>
                    <input
                        id="email"
                        type="email"
                        wire:model="email"
                        @disabled(! $editing)
                        autocomplete="email"
                        class="tactical-input w-full px-3 py-3
                               disabled:opacity-60"
                    />
                    @error('email')
                        <p class="mt-2 text-xs text-error">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-5 mt-6 pt-5 border-t border-outline-variant">
                <div>
                    <p class="text-[10px] uppercase tracking-wider text-on-surface-variant">
                        Account Role
                    </p>
                    <p class="mt-2 text-sm font-semibold text-tertiary">
                        {{ str($user->role)->replace('_', ' ')->title() }}
                    </p>
                </div>
                <div>
                    <p class="text-[10px] uppercase tracking-wider text-on-surface-variant">
                        Account Status
                    </p>
                    <p class="mt-2 flex items-center gap-2 text-sm font-semibold text-on-surface">
                        <span class="{{ $user->status === 'active' ? 'pip-active' : 'pip-standby' }} inline-block w-2 h-2 rounded-full"></span>
                        {{ str($user->status)->title() }}
                    </p>
                </div>
            </div>
        </section>

        {{-- Soldier information --}}
        @if ($soldier)
            <section class="bg-primary-container border border-outline-variant rounded p-4 md:p-6">
                <div class="flex items-center gap-3 mb-5 border-b border-outline-variant pb-4">
                    <div class="w-10 h-10 flex items-center justify-center
                                bg-surface-container-high border border-outline-variant
                                text-tertiary">
                        <x-lucide-shield class="w-5 h-5" />
                    </div>
                    <div>
                        <h2 class="text-base font-semibold text-on-surface">
                            Service & Personal Information
                        </h2>
                        <p class="text-xs text-on-surface-variant mt-1">
                            Personnel record and registered details
                        </p>
                    </div>
                </div>

                {{-- Read-only service identity --}}
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-6">
                    <div class="bg-surface-container-low border border-outline-variant p-4">
                        <p class="text-[10px] uppercase tracking-wider text-on-surface-variant">
                            Service ID
                        </p>
                        <p class="mt-2 font-mono-data text-sm font-bold text-tertiary">
                            {{ $soldier->service_id }}
                        </p>
                    </div>
                    <div class="bg-surface-container-low border border-outline-variant p-4">
                        <p class="text-[10px] uppercase tracking-wider text-on-surface-variant">
                            Rank
                        </p>
                        <p class="mt-2 text-sm font-semibold text-on-surface">
                            {{ $soldier->rank }}
                        </p>
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-5 border-t border-outline-variant pt-5">
                    <div>
                        <label for="dob"
                               class="block mb-2 text-[10px] font-bold uppercase
                                      tracking-wider text-on-surface-variant">
                            Date of Birth
                        </label>
                        <input
                            id="dob"
                            type="date"
                            wire:model="dob"
                            @disabled(! $editing)
                            class="tactical-input w-full px-3 py-3
                                   disabled:opacity-60"
                        />
                        @error('dob')
                            <p class="mt-2 text-xs text-error">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="bloodGroup"
                               class="block mb-2 text-[10px] font-bold uppercase
                                      tracking-wider text-on-surface-variant">
                            Blood Group
                        </label>
                        <select
                            id="bloodGroup"
                            wire:model="bloodGroup"
                            @disabled(! $editing)
                            class="tactical-input w-full px-3 py-3
                                   disabled:opacity-60"
                        >
                            <option value="">Select blood group</option>
                            @foreach ([
                                'A+', 'A-', 'B+', 'B-',
                                'AB+', 'AB-', 'O+', 'O-',
                            ] as $group)
                                <option value="{{ $group }}">{{ $group }}</option>
                            @endforeach
                        </select>
                        @error('bloodGroup')
                            <p class="mt-2 text-xs text-error">{{ $message }}</p>
                        @enderror
                    </div>

                    @foreach ([
                        'street' => 'Street',
                        'city' => 'City',
                        'state' => 'State',
                        'pincode' => 'PIN Code',
                    ] as $field => $label)
                        <div>
                            <label for="{{ $field }}"
                                   class="block mb-2 text-[10px] font-bold uppercase
                                          tracking-wider text-on-surface-variant">
                                {{ $label }}
                            </label>
                            <input
                                id="{{ $field }}"
                                type="text"
                                wire:model="{{ $field }}"
                                @disabled(! $editing)
                                class="tactical-input w-full px-3 py-3
                                       disabled:opacity-60"
                            />
                            @error($field)
                                <p class="mt-2 text-xs text-error">{{ $message }}</p>
                            @enderror
                        </div>
                    @endforeach
                </div>
            </section>
        @endif

        {{-- Actions --}}
        @if ($editing)
            <div class="flex flex-wrap items-center justify-between gap-4">
                <p class="font-mono-data text-[10px] uppercase tracking-wider text-on-surface-variant">
                    Review your changes before saving
                </p>

                <div class="flex gap-3">
                    <button
                        type="button"
                        wire:click="cancel"
                        wire:loading.attr="disabled"
                        class="btn-tactical-outline px-4 py-3
                               text-xs font-bold uppercase tracking-wider"
                    >
                        Cancel
                    </button>

                    <button
                        type="submit"
                        wire:loading.attr="disabled"
                        class="btn-tactical-primary inline-flex items-center
                               justify-center gap-2 px-4 py-3
                               text-xs font-bold uppercase tracking-wider"
                    >
                        <span wire:loading.remove wire:target="update"
                              class="inline-flex items-center gap-2">
                            <x-lucide-save class="w-4 h-4" />
                            Save Changes
                        </span>

                        <span wire:loading wire:target="update"
                              class="inline-flex items-center gap-2">
                            <x-lucide-loader-circle class="w-4 h-4 animate-spin" />
                            Saving...
                        </span>
                    </button>
                </div>
            </div>
        @endif
    </form>
</div>
