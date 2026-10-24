<?php

use App\Models\Soldier;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Layout('layouts::auth'), Title('Login')]
class extends Component {
    public string $serviceId = '';

    public string $password = '';

    public function mount(): void
    {
        if (Auth::check())
            $this->redirectRoute('dashboard');
    }

    public function authenticate(): void
    {
        $validated = $this->validate([
            'serviceId' => ['required', 'string', 'max:32'],
            'password' => ['required', 'string', 'max:128'],
        ]);

        $soldier = Soldier::where('service_id', $validated['serviceId'])->first();
        $user = $soldier?->user;

        if (!$user || !Hash::check($validated['password'], $user->password))
            throw ValidationException::withMessages(['serviceId' => 'Invalid Service ID or Authorization Key.']);

        Auth::login($user);
        request()->session()->regenerate();

        $this->redirectIntended(default: route('dashboard', absolute: false));
    }
};
?>

<div class="bg-surface-container border border-outline-variant shadow-[0_4px_24px_-4px_rgba(0,0,0,0.8)] flex flex-col relative overflow-hidden">

    <div class="scanline"></div>

    <header class="p-6 border-b border-outline-variant bg-surface-container-high/50 flex flex-col items-center justify-center relative">
        <div class="mb-4">
            <x-talos-logo size="lg" />
        </div>

        <h1 class="text-2xl font-bold text-primary tracking-tighter uppercase text-center">
            TALOS SYSTEM
        </h1>

        <p class="text-xs font-bold text-tertiary tracking-[0.2em] mt-2 flex items-center gap-2 font-mono-data">
            <span class="w-2 h-2 bg-tertiary rounded-full animate-pulse"></span>
            RESTRICTED ACCESS
        </p>

        <div class="tech-corner-tl"></div>
        <div class="tech-corner-tr"></div>
    </header>

    <div class="p-6 flex flex-col gap-6">

        <div class="bg-error-container/10 border border-error/20 p-3 flex items-start gap-3">
            <x-lucide-triangle-alert class="w-5 h-5 text-error shrink-0" />
            <p class="font-mono-data text-xs text-error/90 uppercase leading-snug">
                Classified Access Only. Unauthorized attempts will be logged and traced.
            </p>
        </div>

        <form wire:submit="authenticate" class="flex flex-col gap-5">

            <div class="flex flex-col gap-1.5">
                <label for="service-id" class="text-xs font-bold uppercase tracking-wider text-on-surface-variant flex justify-between">
                    <span>Service ID</span>
                    <span class="font-mono-data text-[10px] text-outline opacity-60">AUTH_REQ_01</span>
                </label>

                <div class="relative">
                    <x-lucide-badge class="absolute left-3 top-1/2 -translate-y-1/2 w-[18px] h-[18px] text-outline-variant" />
                    <input
                        id="service-id"
                        type="text"
                        wire:model="serviceId"
                        autocomplete="username"
                        spellcheck="false"
                        placeholder="Enter Operator ID"
                        class="w-full bg-surface-container-highest border border-outline-variant text-on-surface font-mono-data text-sm h-12 pl-10 pr-3 focus:outline-none focus:border-tertiary transition-colors placeholder:text-outline-variant"
                    >
                    @error('serviceId')
                        <p class="text-xs text-error mt-1" role="alert">
                            {{ $message }}
                        </p>
                    @enderror
                </div>
            </div>

            <div class="flex flex-col gap-1.5">
                <label for="auth-key" class="text-xs font-bold uppercase tracking-wider text-on-surface-variant flex justify-between">
                    <span>Authorization Key</span>
                    <span class="font-mono-data text-[10px] text-outline opacity-60">ENC_LEVEL_IV</span>
                </label>

                <div class="relative">
                    <x-lucide-key-round class="absolute left-3 top-1/2 -translate-y-1/2 w-[18px] h-[18px] text-outline-variant" />
                    <input
                        id="auth-key"
                        wire:model="password"
                        type="password"
                        autocomplete="off"
                        placeholder="Enter Secure Key"
                        class="w-full bg-surface-container-highest border border-outline-variant text-on-surface font-mono-data text-sm h-12 pl-10 pr-3 focus:outline-none focus:border-tertiary transition-colors placeholder:text-outline-variant"
                    >
                    @error('password')
                        <p class="text-xs text-error mt-1" role="alert">
                            {{ $message }}
                        </p>
                    @enderror
                </div>

            </div>


            <button
                type="submit"
                wire:loading.attr="disabled"
                class="mt-2 w-full h-12 bg-tertiary text-on-tertiary
                       font-bold text-xs uppercase tracking-widest flex
                       items-center justify-center gap-2 hover:bg-tertiary-fixed
                       transition-colors active:scale-[0.98]
                       shadow-[0_0_12px_rgba(255,185,95,0.3)]
                       disabled:opacity-70 disabled:cursor-not-allowed"
            >
                <span class="inline-flex items-center justify-center gap-2 in-data-loading:hidden">
                    <x-lucide-log-in class="w-[18px] h-[18px]" />
                    <span>Execute Authentication</span>
                </span>

                <span class="hidden items-center justify-center gap-2 not-in-data-loading:hidden">
                    <x-lucide-loader-circle class="w-[18px] h-[18px] animate-spin" />
                    <span>Verifying Credentials...</span>
                </span>
            </button>
        </form>
    </div>

    <footer class="p-4 border-t border-outline-variant bg-surface-container-lowest/50 flex flex-col gap-3">
        <div class="flex items-center justify-between font-mono-data text-xs text-on-surface-variant">
            <span class="flex items-center gap-2">
                <span class="w-1.5 h-1.5 bg-secure rounded-full animate-pulse"></span>
                SYSTEM STATUS: OPERATIONAL
            </span>
            <span class="opacity-50">NODE_7A</span>
        </div>
    </footer>

    <div class="tech-corner-bl"></div>
    <div class="tech-corner-br"></div>
</div>
