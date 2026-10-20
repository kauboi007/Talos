@props([
    'size' => 'md',
    'showText' => false,
])

@php
    $sizes = [
        'sm' => 'w-6 h-6',
        'md' => 'w-10 h-10',
        'lg' => 'w-20 h-20',
        'xl' => 'w-28 h-28',
    ];

    $sizeClass = $sizes[$size] ?? $sizes['md'];
@endphp

<div class="flex flex-col items-center justify-center">
    <div class="{{ $sizeClass }} relative flex items-center justify-center">
        <svg
            viewBox="0 0 100 120"
            fill="none"
            xmlns="http://www.w3.org/2000/svg"
            class="w-full h-full text-[#bec6e0] drop-shadow-[0_0_10px_rgba(190,198,224,0.3)]"
            aria-label="Talos shield emblem"
            role="img"
        >
            <polygon
                points="50,4 96,28 88,88 50,116 12,88 4,28"
                stroke="currentColor"
                stroke-width="3"
                stroke-linejoin="round"
                class="opacity-90"
            />
            <polygon
                points="50,14 84,34 78,82 50,104 22,82 16,34"
                stroke="currentColor"
                stroke-width="1.8"
                stroke-linejoin="round"
                class="opacity-70"
            />

            <line x1="50" y1="4" x2="50" y2="116" stroke="currentColor" stroke-width="2.5" class="opacity-80" />
            <line x1="20" y1="36" x2="80" y2="36" stroke="currentColor" stroke-width="2.5" class="opacity-80" />

            <line x1="50" y1="4" x2="84" y2="34" stroke="currentColor" stroke-width="1.5" class="opacity-60" />
            <line x1="50" y1="4" x2="16" y2="34" stroke="currentColor" stroke-width="1.5" class="opacity-60" />
            <line x1="50" y1="36" x2="88" y2="88" stroke="currentColor" stroke-width="1.5" class="opacity-60" />
            <line x1="50" y1="36" x2="12" y2="88" stroke="currentColor" stroke-width="1.5" class="opacity-60" />
            <line x1="30" y1="36" x2="50" y2="76" stroke="currentColor" stroke-width="2" class="opacity-80" />
            <line x1="70" y1="36" x2="50" y2="76" stroke="currentColor" stroke-width="2" class="opacity-80" />
            <line x1="50" y1="76" x2="50" y2="104" stroke="currentColor" stroke-width="2.5" class="opacity-90" />

            <polygon
                points="36,44 64,44 58,54 42,54"
                stroke="currentColor"
                stroke-width="1.5"
                fill="rgba(190,198,224,0.15)"
            />
        </svg>
    </div>

    @if ($showText)
        <div class="mt-3 text-center">
            <h2 class="text-primary font-bold tracking-tighter uppercase font-sans text-xl">
                TALOS DEFENSE
            </h2>
            <p class="text-xs text-outline tracking-widest font-mono-data uppercase">
                ANALYTICS
            </p>
        </div>
    @endif
</div>
