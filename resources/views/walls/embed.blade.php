<x-layouts.app :title="$wall->name.' embed'">
    @include('walls._header', ['active' => 'embed'])

    <div class="grid gap-6 lg:grid-cols-[320px_1fr]" data-embed data-feed="{{ $wall->apiUrl() }}" data-widget="{{ $widgetUrl }}">
        <div class="space-y-6">
            <div class="panel space-y-5 p-6">
                <fieldset>
                    <legend class="label">Layout</legend>
                    <div class="grid grid-cols-3 gap-2">
                        @foreach (['masonry' => 'Wall', 'carousel' => 'Carousel', 'list' => 'List'] as $value => $label)
                            <label class="cursor-pointer">
                                <input type="radio" name="layout" value="{{ $value }}" class="peer sr-only" @checked($value === 'masonry')>
                                <span class="block rounded-lg border border-zinc-200 px-2 py-2 text-center text-sm font-medium peer-checked:border-brand-700 peer-checked:bg-brand-50 peer-checked:text-brand-700 peer-focus-visible:outline-2 peer-focus-visible:outline-brand-600 dark:border-zinc-700 dark:peer-checked:bg-brand-700/20 dark:peer-checked:text-brand-100">{{ $label }}</span>
                            </label>
                        @endforeach
                    </div>
                </fieldset>

                <fieldset>
                    <legend class="label">Theme</legend>
                    <div class="grid grid-cols-3 gap-2">
                        @foreach (['auto' => 'Auto', 'light' => 'Light', 'dark' => 'Dark'] as $value => $label)
                            <label class="cursor-pointer">
                                <input type="radio" name="theme" value="{{ $value }}" class="peer sr-only" @checked($value === 'auto')>
                                <span class="block rounded-lg border border-zinc-200 px-2 py-2 text-center text-sm font-medium peer-checked:border-brand-700 peer-checked:bg-brand-50 peer-checked:text-brand-700 peer-focus-visible:outline-2 peer-focus-visible:outline-brand-600 dark:border-zinc-700 dark:peer-checked:bg-brand-700/20 dark:peer-checked:text-brand-100">{{ $label }}</span>
                            </label>
                        @endforeach
                    </div>
                    <p class="hint">Auto follows the visitor's light or dark setting.</p>
                </fieldset>

                <div>
                    <label for="limit" class="label">Show at most</label>
                    <select id="limit" name="limit" class="input">
                        <option value="">All approved testimonials</option>
                        @foreach ([3, 6, 9, 12, 20] as $n)
                            <option value="{{ $n }}">{{ $n }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="space-y-3">
                    <label class="flex items-center gap-2 text-sm">
                        <input type="checkbox" name="cta" value="1" checked class="size-4 accent-brand-700">
                        "Leave a testimonial" button
                    </label>
                    <label class="flex items-center gap-2 text-sm">
                        <input type="checkbox" name="branding" value="1" checked class="size-4 accent-brand-700">
                        "Powered by Praisewall" link
                    </label>
                </div>
            </div>

            <div class="panel space-y-3 p-6 text-sm">
                <p class="font-semibold">Other ways to share</p>
                <div>
                    <p class="text-zinc-500 dark:text-zinc-400">Public wall page</p>
                    <a href="{{ $wall->wallUrl() }}" target="_blank" class="break-all font-mono text-xs text-brand-700 hover:underline dark:text-brand-500">{{ $wall->wallUrl() }}</a>
                </div>
                <div>
                    <p class="text-zinc-500 dark:text-zinc-400">JSON API, for your own design</p>
                    <a href="{{ $wall->apiUrl() }}" target="_blank" class="break-all font-mono text-xs text-brand-700 hover:underline dark:text-brand-500">{{ $wall->apiUrl() }}</a>
                </div>
            </div>
        </div>

        <div class="min-w-0 space-y-6">
            <div class="panel p-6">
                <div class="mb-3 flex items-center justify-between gap-3">
                    <div>
                        <h2 class="font-semibold">Embed code</h2>
                        <p class="text-sm text-zinc-500 dark:text-zinc-400">Paste it where the testimonials should appear. Works in any HTML, WordPress, Webflow or Laravel page.</p>
                    </div>
                    <button type="button" class="btn-primary flex-none" data-copy-target="#snippet">Copy</button>
                </div>
                <pre class="code whitespace-pre-wrap break-all"><code id="snippet"></code></pre>
            </div>

            <div class="panel overflow-hidden">
                <div class="flex items-center justify-between border-b border-zinc-200 px-6 py-3 text-sm dark:border-zinc-800">
                    <span class="font-semibold">Preview</span>
                    <span class="text-zinc-500 dark:text-zinc-400">Only approved testimonials are shown</span>
                </div>
                <div class="bg-zinc-50 p-6 dark:bg-zinc-950" data-preview></div>
            </div>
        </div>
    </div>

    <script src="{{ $widgetUrl }}" defer></script>
</x-layouts.app>
