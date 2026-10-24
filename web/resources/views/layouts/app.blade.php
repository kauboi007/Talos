<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">

        <title>{{ $title ? $title . ' | ' . config('app.name') : config('app.name') }}</title>

        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&family=JetBrains+Mono:wght@400;500;600;700&display=swap" rel="stylesheet">

        <script>
            document.documentElement.dataset.theme = localStorage.getItem('talos-theme') || 'light';
        </script>
        <link rel="stylesheet" href="{{ asset('css/app.css') }}">
        @livewireStyles
    </head>
    <body>
        <div class="min-h-screen bg-surface text-on-surface flex flex-col font-sans selection:bg-tertiary selection:text-on-tertiary relative w-full">
            <div class="absolute top-[5%] left-[5%] w-[45vw] h-[45vw] rounded-full blur-[140px] pointer-events-none -z-10 bg-primary/4"></div>
            <div class="absolute bottom-[10%] right-[5%] w-[35vw] h-[35vw] rounded-full blur-[120px] pointer-events-none -z-10 bg-secondary/6"></div>
            <div class="absolute top-[40%] right-[10%] w-[30vh] h-[30vh] rounded-full blur-[110px] pointer-events-none -z-10 bg-tertiary/4"></div>
            <main class="flex-1 w-full flex flex-col">
                {{ $slot }}
            </main>
        </div>
        @livewireScripts
    </body>
</html>
