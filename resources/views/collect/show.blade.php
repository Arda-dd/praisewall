<x-layouts.public :wall="$wall" :title="$wall->headline.' · '.$wall->name" :description="$wall->prompt">
    <main class="mx-auto max-w-xl px-4 py-10 sm:py-16">
        <header class="mb-8 text-center">
            @if ($wall->logoUrl())
                <img src="{{ $wall->logoUrl() }}" alt="{{ $wall->name }}" class="mx-auto mb-5 size-16 rounded-2xl object-cover shadow-sm">
            @else
                <span class="mx-auto mb-5 grid size-16 place-items-center rounded-2xl bg-[var(--accent)] text-2xl font-bold text-white shadow-sm">{{ mb_strtoupper(mb_substr($wall->name, 0, 1)) }}</span>
            @endif
            <h1 class="text-2xl font-semibold tracking-tight sm:text-3xl">{{ $wall->headline }}</h1>
            <p class="mx-auto mt-3 max-w-md whitespace-pre-line text-zinc-600 dark:text-zinc-400">{{ $wall->prompt }}</p>
        </header>

        @if (! $wall->collecting)
            <div class="panel p-8 text-center">
                <p class="font-semibold">{{ __('praisewall.closed', ['name' => $wall->name]) }}</p>
                <a href="{{ $wall->wallUrl() }}" class="mt-4 inline-block text-sm font-semibold text-[var(--accent)] hover:underline">{{ __('praisewall.see_others') }} →</a>
            </div>
        @else
            <form method="POST" action="{{ route('collect.store', $wall) }}" enctype="multipart/form-data" class="panel space-y-5 p-6 sm:p-8" data-collect>
                @csrf

                @if ($wall->ask_for_rating)
                    <fieldset>
                        <legend class="label">{{ __('praisewall.rating') }}</legend>
                        <div class="flex gap-1" data-rating>
                            @for ($i = 1; $i <= 5; $i++)
                                <label class="cursor-pointer" title="{{ trans_choice('praisewall.stars', $i) }}">
                                    <input type="radio" name="rating" value="{{ $i }}" class="peer sr-only" @checked((int) old('rating') === $i) required>
                                    <span class="sr-only">{{ trans_choice('praisewall.stars', $i) }}</span>
                                    <svg viewBox="0 0 20 20" class="size-9 fill-zinc-200 transition peer-focus-visible:outline-2 peer-focus-visible:outline-[var(--accent)] dark:fill-zinc-700" data-star="{{ $i }}" aria-hidden="true">
                                        <path d="M10 1.5l2.6 5.3 5.9.9-4.25 4.1 1 5.8L10 14.9l-5.25 2.7 1-5.8L1.5 7.7l5.9-.9L10 1.5z" />
                                    </svg>
                                </label>
                            @endfor
                        </div>
                        @error('rating') <p class="error">{{ $message }}</p> @enderror
                    </fieldset>
                @endif

                <x-field name="body" :label="__('praisewall.body')">
                    <textarea id="body" name="body" rows="5" class="input focus:border-[var(--accent)] focus:ring-[var(--accent)]/15" required minlength="10" maxlength="2000" placeholder="{{ __('praisewall.body_placeholder') }}">{{ old('body') }}</textarea>
                </x-field>

                <div class="grid gap-5 sm:grid-cols-2">
                    <x-field name="author_name" :label="__('praisewall.name')">
                        <input id="author_name" name="author_name" class="input" value="{{ old('author_name') }}" required maxlength="80" autocomplete="name">
                    </x-field>
                    <x-field name="author_title" :label="__('praisewall.title')" :hint="__('praisewall.optional')">
                        <input id="author_title" name="author_title" class="input" value="{{ old('author_title') }}" maxlength="120" autocomplete="organization-title" placeholder="{{ __('praisewall.title_placeholder') }}">
                    </x-field>
                </div>

                <x-field name="author_email" :label="__('praisewall.email')" :hint="__('praisewall.email_hint', ['name' => $wall->name])">
                    <input id="author_email" name="author_email" type="email" class="input" value="{{ old('author_email') }}" maxlength="255" autocomplete="email">
                </x-field>

                @if ($wall->ask_for_avatar)
                    <x-field name="avatar" :label="__('praisewall.photo')" :hint="__('praisewall.photo_hint')">
                        <label class="flex cursor-pointer items-center gap-3" data-file>
                            <span class="grid size-12 flex-none place-items-center overflow-hidden rounded-full bg-zinc-100 text-zinc-400 dark:bg-zinc-800" data-file-preview>
                                <svg viewBox="0 0 24 24" class="size-6" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><circle cx="12" cy="9" r="3.5" /><path d="M5 19.5c1.4-3 4-4.5 7-4.5s5.6 1.5 7 4.5" stroke-linecap="round" /></svg>
                            </span>
                            <span class="btn-secondary" data-file-label>{{ __('praisewall.choose_file') }}</span>
                            <input id="avatar" name="avatar" type="file" accept="image/png,image/jpeg,image/webp" class="sr-only">
                        </label>
                    </x-field>
                @endif

                {{-- Honeypot: hidden from people, filled in by bots. --}}
                <div class="hidden" aria-hidden="true">
                    <label for="website">Website</label>
                    <input id="website" name="website" tabindex="-1" autocomplete="off">
                </div>

                <div>
                    <label class="flex items-start gap-3 text-sm text-zinc-600 dark:text-zinc-400">
                        <input type="checkbox" name="consent" value="1" @checked(old('consent')) required class="mt-0.5 size-4 flex-none accent-[var(--accent)]">
                        {{ __('praisewall.consent', ['name' => $wall->name]) }}
                    </label>
                    @error('consent') <p class="error">{{ $message }}</p> @enderror
                </div>

                <button class="btn w-full bg-[var(--accent)] py-3 text-base text-white shadow-sm hover:brightness-110">{{ __('praisewall.submit') }}</button>
            </form>
        @endif

        <p class="mt-8 text-center text-xs text-zinc-400">
            <a href="https://github.com/Arda-dd/praisewall" class="hover:text-zinc-600 dark:hover:text-zinc-300">{{ __('praisewall.powered_by') }}</a>
        </p>
    </main>
</x-layouts.public>
