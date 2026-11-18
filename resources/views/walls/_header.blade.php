<div class="mb-8">
    <a href="{{ route('walls.index') }}" class="text-sm text-zinc-500 hover:text-zinc-900 dark:text-zinc-400 dark:hover:text-zinc-100">← Walls</a>
    <div class="mt-2 flex flex-wrap items-center justify-between gap-4">
        <div class="flex items-center gap-3">
            @if ($wall->logoUrl())
                <img src="{{ $wall->logoUrl() }}" alt="" class="size-10 rounded-lg object-cover">
            @endif
            <h1 class="text-2xl font-semibold tracking-tight">{{ $wall->name }}</h1>
            @unless ($wall->collecting)
                <span class="rounded-full bg-zinc-200 px-2.5 py-1 text-xs font-semibold text-zinc-700 dark:bg-zinc-800 dark:text-zinc-300">Form closed</span>
            @endunless
        </div>
        <div class="flex flex-wrap items-center gap-2">
            <button type="button" class="btn-secondary" data-copy="{{ $wall->collectUrl() }}">Copy collection link</button>
            <a href="{{ $wall->wallUrl() }}" target="_blank" class="btn-secondary">Public wall <x-external /></a>
        </div>
    </div>
    <nav class="mt-6 flex gap-1 border-b border-zinc-200 dark:border-zinc-800">
        @foreach (['inbox' => ['Inbox', route('walls.show', $wall)], 'embed' => ['Embed', route('walls.embed', $wall)], 'settings' => ['Settings', route('walls.edit', $wall)]] as $key => [$label, $url])
            <a href="{{ $url }}" @class([
                '-mb-px border-b-2 px-3 py-2.5 text-sm font-medium',
                'border-brand-700 text-zinc-900 dark:text-zinc-100' => $active === $key,
                'border-transparent text-zinc-500 hover:text-zinc-900 dark:text-zinc-400 dark:hover:text-zinc-100' => $active !== $key,
            ])>{{ $label }}</a>
        @endforeach
    </nav>
</div>
