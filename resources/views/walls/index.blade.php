<x-layouts.app title="Walls">
    <div class="mb-8 flex flex-wrap items-end justify-between gap-4">
        <div>
            <h1 class="text-2xl font-semibold tracking-tight">Your walls</h1>
            <p class="mt-1 text-sm text-zinc-500 dark:text-zinc-400">One wall per product, service or client. Each has its own collection link and widget.</p>
        </div>
        <a href="{{ route('walls.create') }}" class="btn-primary">New wall</a>
    </div>

    @if ($walls->isEmpty())
        <div class="panel flex flex-col items-center px-6 py-16 text-center">
            <x-logo :size="48" />
            <h2 class="mt-4 text-lg font-semibold">Create your first wall</h2>
            <p class="mt-1 max-w-md text-sm text-zinc-500 dark:text-zinc-400">
                You'll get a link to send to customers. Their testimonials land in your inbox, and the ones you approve show up on your website.
            </p>
            <a href="{{ route('walls.create') }}" class="btn-primary mt-6">New wall</a>
        </div>
    @else
        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
            @foreach ($walls as $wall)
                <div class="panel flex flex-col p-5">
                    <div class="flex items-start gap-3">
                        @if ($wall->logoUrl())
                            <img src="{{ $wall->logoUrl() }}" alt="" class="size-10 rounded-lg object-cover">
                        @else
                            <span class="grid size-10 place-items-center rounded-lg text-sm font-bold text-white" style="background: {{ $wall->accent_color }}">{{ mb_strtoupper(mb_substr($wall->name, 0, 1)) }}</span>
                        @endif
                        <div class="min-w-0 flex-1">
                            <a href="{{ route('walls.show', $wall) }}" class="block truncate font-semibold hover:text-brand-700">{{ $wall->name }}</a>
                            <p class="text-sm text-zinc-500 dark:text-zinc-400">
                                {{ $wall->approved_count }} published
                                @if ($wall->average_rating)
                                    · ★ {{ number_format($wall->average_rating, 1) }}
                                @endif
                            </p>
                        </div>
                        @if ($wall->pending_count)
                            <a href="{{ route('walls.show', $wall) }}" class="rounded-full bg-amber-100 px-2.5 py-1 text-xs font-semibold text-amber-800 dark:bg-amber-950 dark:text-amber-300">{{ $wall->pending_count }} new</a>
                        @endif
                    </div>

                    <div class="mt-5 flex items-center gap-2 rounded-lg bg-zinc-50 p-1.5 pl-3 dark:bg-zinc-800/60">
                        <span class="min-w-0 flex-1 truncate font-mono text-xs text-zinc-600 dark:text-zinc-300">{{ $wall->collectUrl() }}</span>
                        <button type="button" class="btn-ghost text-xs" data-copy="{{ $wall->collectUrl() }}">Copy link</button>
                    </div>

                    <div class="mt-4 flex flex-wrap gap-1 border-t border-zinc-100 pt-3 dark:border-zinc-800">
                        <a href="{{ route('walls.show', $wall) }}" class="btn-ghost">Inbox</a>
                        <a href="{{ route('walls.embed', $wall) }}" class="btn-ghost">Embed</a>
                        <a href="{{ $wall->wallUrl() }}" class="btn-ghost" target="_blank">Public wall <x-external /></a>
                        <a href="{{ route('walls.edit', $wall) }}" class="btn-ghost ml-auto">Settings</a>
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</x-layouts.app>
