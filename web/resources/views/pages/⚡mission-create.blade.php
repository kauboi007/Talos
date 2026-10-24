<?php

use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Layout('layouts::command'), Title('Create Mission')]
class extends Component {};
?>

<div class="p-4 md:p-6 space-y-6">
    <div>
        <p class="font-mono-data text-[10px] uppercase tracking-[0.2em] text-tertiary">
            Missions / Planning
        </p>
        <h1 class="mt-1 text-2xl font-bold tracking-tight text-on-surface">Create CBS Mission</h1>
        <p class="mt-1 text-sm text-on-surface-variant">
            Mission planning workflow will be connected after the mission data model is finalized.
        </p>
    </div>

    <section class="bg-primary-container border border-outline-variant rounded p-6">
        <div class="flex items-start gap-4">
            <x-lucide-route class="mt-1 h-6 w-6 shrink-0 text-tertiary" />
            <div>
                <h2 class="text-base font-semibold text-on-surface">CBS mission planning</h2>
                <p class="mt-2 text-sm leading-relaxed text-on-surface-variant">
                    The mission table and planning inputs are intentionally not enabled yet.
                    This entry point is restricted to HQ and system administrators.
                </p>
            </div>
        </div>
    </section>
</div>
