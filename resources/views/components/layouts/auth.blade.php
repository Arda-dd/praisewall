@props(['title', 'intro' => null])
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title }} · Praisewall</title>
    <link rel="icon" href="{{ asset('favicon.svg') }}" type="image/svg+xml">
    @vite(['resources/css/app.css'])
</head>
<body class="grid min-h-screen place-items-center bg-zinc-50 px-4 py-12 font-sans text-zinc-900 antialiased dark:bg-zinc-950 dark:text-zinc-100">
    <div class="w-full max-w-sm">
        <div class="mb-8 flex flex-col items-center gap-3 text-center">
            <x-logo :size="44" />
            <h1 class="text-xl font-semibold tracking-tight">{{ $title }}</h1>
            @if ($intro)
                <p class="text-sm text-zinc-500 dark:text-zinc-400">{{ $intro }}</p>
            @endif
        </div>
        <div class="panel p-6">
            {{ $slot }}
        </div>
    </div>
</body>
</html>
