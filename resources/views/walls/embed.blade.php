@php
    $layouts = [
        'masonry' => ['Wall', 'M3 3h6v8H3zM11 3h6v5h-6zM3 13h6v4H3zM11 10h6v7h-6z'],
        'grid' => ['Grid', 'M3 3h6v6H3zM11 3h6v6h-6zM3 11h6v6H3zM11 11h6v6h-6z'],
        'carousel' => ['Carousel', 'M1 5h3v10H1zM6 4h8v12H6zM16 5h3v10h-3z'],
        'slider' => ['Slider', 'M4 4h12v9H4zM8 16h1M10 16h3M14 16h1'],
        'list' => ['List', 'M4 3h12v4H4zM4 9h12v4H4zM4 15h12v2H4z'],
        'popup' => ['Popup', 'M2 3h16v14H2zM4 11h7v4H4z'],
        'badge' => ['Badge', 'M2 7h16v6H2zM4 9h2v2H4zM8 9.5h7v1H8z'],
    ];
@endphp
<x-layouts.app :title="$wall->name.' widget'">
    @include('walls._header', ['active' => 'embed'])

    <div class="grid gap-6 lg:grid-cols-[340px_1fr]" data-embed data-feed="{{ $wall->apiUrl() }}" data-widget="{{ $widgetUrl }}" data-wall-style="{{ $wall->style }}" data-wall-accent="{{ $wall->accent_color }}" data-wall-name="{{ $wall->name }}">
        <div class="space-y-4">
            <div class="panel space-y-5 p-5">
                <fieldset>
                    <legend class="label">Layout</legend>
                    <div class="grid grid-cols-4 gap-2">
                        @foreach ($layouts as $value => [$label, $path])
                            <label class="cursor-pointer">
                                <input type="radio" name="layout" value="{{ $value }}" class="peer sr-only" @checked($value === 'masonry')>
                                <span class="flex flex-col items-center gap-1 rounded-lg border border-zinc-200 px-1 py-2 text-xs font-medium text-zinc-600 peer-checked:border-brand-700 peer-checked:bg-brand-50 peer-checked:text-brand-700 peer-focus-visible:outline-2 peer-focus-visible:outline-brand-600 dark:border-zinc-700 dark:text-zinc-400 dark:peer-checked:bg-brand-700/20 dark:peer-checked:text-brand-100">
                                    <svg viewBox="0 0 20 20" class="size-5" fill="currentColor" fill-opacity=".85" aria-hidden="true"><path d="{{ $path }}" /></svg>
                                    {{ $label }}
                                </span>
                            </label>
                        @endforeach
                    </div>
                </fieldset>

                <fieldset>
                    <legend class="label">Style</legend>
                    <div class="grid grid-cols-2 gap-2">
                        @foreach (config('praisewall.styles') as $value => $label)
                            <label class="cursor-pointer">
                                <input type="radio" name="style" value="{{ $value }}" class="peer sr-only" @checked($value === $wall->style)>
                                <span class="flex items-center gap-2 rounded-lg border border-zinc-200 p-1.5 text-sm font-medium peer-checked:border-brand-700 peer-checked:bg-brand-50 peer-checked:text-brand-700 peer-focus-visible:outline-2 peer-focus-visible:outline-brand-600 dark:border-zinc-700 dark:peer-checked:bg-brand-700/20 dark:peer-checked:text-brand-100">
                                    <x-style-swatch :style="$value" :accent="$wall->accent_color" />
                                    {{ $label }}
                                </span>
                            </label>
                        @endforeach
                    </div>
                </fieldset>

                <div class="grid grid-cols-2 gap-4">
                    <fieldset>
                        <legend class="label">Theme</legend>
                        <select name="theme" class="input">
                            <option value="auto">Auto</option>
                            <option value="light">Light</option>
                            <option value="dark">Dark</option>
                        </select>
                    </fieldset>
                    <fieldset>
                        <legend class="label">Corners</legend>
                        <select name="radius" class="input">
                            <option value="none">Square</option>
                            <option value="small">Small</option>
                            <option value="medium" selected>Rounded</option>
                            <option value="large">Round</option>
                        </select>
                    </fieldset>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label for="accent" class="label">Accent</label>
                        <input id="accent" name="accent" type="color" value="{{ $wall->accent_color }}" class="h-10 w-full cursor-pointer rounded-lg border border-zinc-300 bg-white p-1 dark:border-zinc-700 dark:bg-zinc-900">
                    </div>
                    <div data-only="masonry grid">
                        <label for="columns" class="label">Columns</label>
                        <select id="columns" name="columns" class="input">
                            <option value="">Auto</option>
                            @foreach ([1, 2, 3, 4] as $n)
                                <option value="{{ $n }}">{{ $n }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div data-only="popup">
                        <label for="position" class="label">Position</label>
                        <select id="position" name="position" class="input">
                            <option value="bottom-left">Bottom left</option>
                            <option value="bottom-right">Bottom right</option>
                        </select>
                    </div>
                </div>

                <div>
                    <label for="limit" class="label">Show at most</label>
                    <select id="limit" name="limit" class="input">
                        <option value="">All approved testimonials</option>
                        @foreach ([3, 6, 9, 12, 20] as $n)
                            <option value="{{ $n }}">{{ $n }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="panel p-5">
                <p class="label">Show</p>
                <div class="grid grid-cols-2 gap-x-4 gap-y-2.5 text-sm">
                    @foreach (['rating' => 'Stars', 'avatar' => 'Photos', 'title' => 'Job titles', 'quote' => 'Quote mark'] as $part => $label)
                        <label class="flex items-center gap-2"><input type="checkbox" name="show" value="{{ $part }}" checked class="size-4 accent-brand-700"> {{ $label }}</label>
                    @endforeach
                    <label class="flex items-center gap-2"><input type="checkbox" name="date" value="1" class="size-4 accent-brand-700"> Dates</label>
                    <label class="flex items-center gap-2"><input type="checkbox" name="font" value="inherit" class="size-4 accent-brand-700"> My site's font</label>
                    <label class="flex items-center gap-2"><input type="checkbox" name="cta" value="1" checked class="size-4 accent-brand-700"> "Leave one" button</label>
                    <label class="flex items-center gap-2"><input type="checkbox" name="branding" value="1" checked class="size-4 accent-brand-700"> Praisewall link</label>
                </div>
            </div>

            <div class="panel space-y-3 p-5 text-sm">
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
            <div class="panel p-5">
                <div class="mb-3 flex items-center justify-between gap-3">
                    <div>
                        <h2 class="font-semibold">Embed code</h2>
                        <p class="text-sm text-zinc-500 dark:text-zinc-400">Paste it where the testimonials should appear. Works in any HTML, WordPress, Webflow or Laravel page.</p>
                    </div>
                    <button type="button" class="btn-primary flex-none" data-copy-target="#snippet">Copy</button>
                </div>
                <pre class="code whitespace-pre-wrap break-all"><code id="snippet"></code></pre>
            </div>

            <div class="panel overflow-hidden lg:sticky lg:top-6">
                <div class="flex flex-wrap items-center justify-between gap-3 border-b border-zinc-200 px-5 py-3 text-sm dark:border-zinc-800">
                    <span class="font-semibold">Preview</span>
                    <div class="flex items-center gap-2" role="group" aria-label="Preview background">
                        <span class="text-zinc-500 dark:text-zinc-400">Background</span>
                        @foreach (['light' => '#fafafa', 'dark' => '#0a0a0b', 'gradient' => 'linear-gradient(135deg,'.$wall->accent_color.',#f472b6 55%,#fb923c)', 'mesh' => 'radial-gradient(at 15% 20%, #a78bfa, transparent 55%), radial-gradient(at 85% 25%, #f9a8d4, transparent 55%), radial-gradient(at 50% 95%, #67e8f9, transparent 55%), #312e81'] as $name => $background)
                            <button type="button" class="size-6 rounded-full border border-zinc-300 ring-brand-600 ring-offset-2 aria-pressed:ring-2 dark:border-zinc-600 dark:ring-offset-zinc-900" style="background: {{ $background }}" data-preview-bg="{{ $background }}" aria-pressed="{{ $loop->first ? 'true' : 'false' }}" aria-label="{{ ucfirst($name) }}"></button>
                        @endforeach
                    </div>
                </div>
                {{-- transform makes the popup position itself inside the preview instead of the page. --}}
                <div class="relative min-h-[420px] p-6 transition-[background] [transform:translateZ(0)]" style="background: #fafafa" data-preview></div>
            </div>
        </div>
    </div>

    <script src="{{ $widgetUrl }}" defer></script>
</x-layouts.app>
