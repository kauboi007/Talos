<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $title ?? 'TALOS' }}</title>

    <link rel="stylesheet" href="{{ asset('css/app.css') }}">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=JetBrains+Mono:wght@400;500;700&display=swap" rel="stylesheet">

    @livewireStyles
</head>
<body>
    <div class="bg-surface text-on-surface font-sans min-h-screen w-full overflow-x-hidden overflow-y-auto flex items-center justify-center relative crt-flicker mesh-bg selection:bg-tertiary selection:text-on-tertiary py-8 px-4">

        <div class="absolute inset-0 bg-[radial-gradient(ellipse_at_center,_var(--tw-gradient-stops))] from-surface-container-high/25 via-surface to-surface pointer-events-none z-0"></div>

        <main class="relative z-10 w-full max-w-[480px]">
            {{ $slot }}
        </main>

        <div class="fixed top-4 right-4 pointer-events-none opacity-40 hidden sm:flex flex-col items-end gap-1 font-mono-data text-[10px] text-tertiary">
            <div>UPLINK: SECURE_V9</div>
            <div>NODE_7A</div>
        </div>
    </div>

    @livewireScripts
</body>
</html>
