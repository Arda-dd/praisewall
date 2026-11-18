<x-layouts.public :wall="$wall" :title="__('praisewall.thanks').' · '.$wall->name">
    <main class="mx-auto grid min-h-screen max-w-lg place-items-center px-4 py-16 text-center">
        <div>
            <span class="mx-auto grid size-16 place-items-center rounded-full bg-[var(--accent)] text-white shadow-sm">
                <svg viewBox="0 0 24 24" class="size-8" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M5 12.5l4.5 4.5L19 7.5" /></svg>
            </span>
            <h1 class="mt-6 text-2xl font-semibold tracking-tight">{{ __('praisewall.thanks') }}</h1>
            <p class="mt-3 whitespace-pre-line text-zinc-600 dark:text-zinc-400">{{ $wall->thank_you_message }}</p>
            <a href="{{ $wall->wallUrl() }}" class="mt-8 inline-block text-sm font-semibold text-[var(--accent)] hover:underline">{{ __('praisewall.see_others') }} →</a>
        </div>
    </main>
</x-layouts.public>
