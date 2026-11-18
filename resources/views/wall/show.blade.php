@php($count = count($feed['testimonials']))
<x-layouts.public :wall="$wall" :title="__('praisewall.wall_title', ['name' => $wall->name])" :description="trans_choice('praisewall.wall_description', $count, ['name' => $wall->name])">
    <main class="mx-auto max-w-6xl px-4 py-12 sm:px-6 sm:py-16">
        <header class="mx-auto mb-12 max-w-2xl text-center">
            @if ($wall->logoUrl())
                <img src="{{ $wall->logoUrl() }}" alt="" class="mx-auto mb-5 size-14 rounded-2xl object-cover shadow-sm">
            @endif
            <p class="text-sm font-semibold uppercase tracking-widest text-[var(--accent)]">{{ __('praisewall.wall_kicker') }}</p>
            <h1 class="mt-3 text-3xl font-semibold tracking-tight sm:text-4xl">{{ __('praisewall.wall_title', ['name' => $wall->name]) }}</h1>
            @if ($feed['summary']['average'])
                <p class="mt-4 inline-flex items-center gap-2 text-zinc-600 dark:text-zinc-400">
                    <x-stars :rating="(int) round($feed['summary']['average'])" />
                    <b class="text-zinc-900 dark:text-zinc-100">{{ number_format($feed['summary']['average'], 1) }}</b>
                    · {{ trans_choice('praisewall.widget.reviews', $count) }}
                </p>
            @endif
        </header>

        {{-- The same widget sites embed, with the feed passed in so the page needs no extra request. --}}
        <div id="wall" data-praisewall="{{ $wall->apiUrl() }}" data-layout="masonry" data-theme="auto" data-branding="false"></div>
        <script type="application/json" id="wall-feed">@json($feed)</script>
        <noscript>
            <ul class="mx-auto max-w-2xl space-y-6">
                @foreach ($wall->published as $testimonial)
                    <li><blockquote>“{{ $testimonial->body }}”</blockquote> — {{ $testimonial->author_name }}</li>
                @endforeach
            </ul>
        </noscript>

        <p class="mt-12 text-center text-xs text-zinc-400">
            <a href="https://github.com/Arda-dd/praisewall" class="hover:text-zinc-600 dark:hover:text-zinc-300">{{ __('praisewall.powered_by') }}</a>
        </p>
    </main>

    <script>
        window.addEventListener('DOMContentLoaded', () => {
            const feed = JSON.parse(document.getElementById('wall-feed').textContent);
            window.Praisewall?.mount(document.getElementById('wall'), feed);
        });
    </script>
    <script src="{{ asset('widget.js') }}"></script>
</x-layouts.public>
