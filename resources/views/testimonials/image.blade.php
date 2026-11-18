<x-layouts.app title="Share as image">
    <a href="{{ route('walls.show', [$testimonial->wall, 'status' => $testimonial->status->value]) }}" class="text-sm text-zinc-500 hover:text-zinc-900 dark:text-zinc-400 dark:hover:text-zinc-100">← {{ $testimonial->wall->name }}</a>
    <h1 class="mt-2 text-2xl font-semibold tracking-tight">Share as image</h1>
    <p class="mt-1 mb-8 text-sm text-zinc-500 dark:text-zinc-400">Turn {{ $testimonial->author_name }}'s words into a post for LinkedIn, Instagram or X. The image is made in your browser.</p>

    <div class="grid gap-6 lg:grid-cols-[300px_1fr]" data-share-image data-testimonial='@json($data)'>
        <div class="panel h-fit space-y-6 p-6">
            <fieldset>
                <legend class="label">Template</legend>
                <div class="grid grid-cols-3 gap-2">
                    @foreach (['aurora' => 'Aurora', 'sunset' => 'Sunset', 'ocean' => 'Ocean', 'midnight' => 'Midnight', 'paper' => 'Paper', 'brand' => 'Brand'] as $value => $label)
                        <label class="cursor-pointer">
                            <input type="radio" name="template" value="{{ $value }}" class="peer sr-only" @checked($loop->first)>
                            <span class="block rounded-xl border-2 border-transparent p-1 text-center text-xs font-medium text-zinc-600 peer-checked:border-brand-700 peer-checked:text-brand-700 peer-focus-visible:outline-2 peer-focus-visible:outline-brand-600 dark:text-zinc-400 dark:peer-checked:text-brand-100">
                                <span class="mb-1 block h-10 rounded-lg" data-template-swatch="{{ $value }}"></span>
                                {{ $label }}
                            </span>
                        </label>
                    @endforeach
                </div>
            </fieldset>

            <fieldset>
                <legend class="label">Size</legend>
                <div class="grid gap-2">
                    @foreach (['square' => ['Square', '1080 × 1080 · Instagram, LinkedIn'], 'landscape' => ['Landscape', '1200 × 627 · LinkedIn, X'], 'story' => ['Story', '1080 × 1920 · Stories, Reels']] as $value => [$label, $hint])
                        <label class="cursor-pointer">
                            <input type="radio" name="size" value="{{ $value }}" class="peer sr-only" @checked($loop->first)>
                            <span class="flex items-center justify-between rounded-lg border border-zinc-200 px-3 py-2 text-sm peer-checked:border-brand-700 peer-checked:bg-brand-50 peer-focus-visible:outline-2 peer-focus-visible:outline-brand-600 dark:border-zinc-700 dark:peer-checked:bg-brand-700/20">
                                <span class="font-medium">{{ $label }}</span>
                                <span class="text-xs text-zinc-500 dark:text-zinc-400">{{ $hint }}</span>
                            </span>
                        </label>
                    @endforeach
                </div>
            </fieldset>

            <div class="space-y-3">
                @foreach (['rating' => 'Star rating', 'avatar' => 'Photo or initials', 'brand' => 'Your logo and name'] as $value => $label)
                    <label class="flex items-center gap-2 text-sm">
                        <input type="checkbox" name="{{ $value }}" value="1" checked class="size-4 accent-brand-700">
                        {{ $label }}
                    </label>
                @endforeach
            </div>

            <button type="button" class="btn-primary w-full" data-download>Download PNG</button>
        </div>

        <div class="panel grid min-h-[480px] place-items-center overflow-hidden bg-[repeating-conic-gradient(#f4f4f5_0%_25%,#ffffff_0%_50%)] bg-[length:24px_24px] p-6 dark:bg-[repeating-conic-gradient(#18181b_0%_25%,#111113_0%_50%)]">
            <canvas class="h-auto max-h-[70vh] w-auto max-w-full rounded-lg shadow-2xl" data-canvas aria-label="Preview of the image"></canvas>
        </div>
    </div>
</x-layouts.app>
