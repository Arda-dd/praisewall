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
                <p class="font-semibold">{{ $wall->name }} isn't collecting testimonials right now.</p>
                <a href="{{ $wall->wallUrl() }}" class="mt-4 inline-block text-sm font-semibold text-[var(--accent)] hover:underline">See what others said →</a>
            </div>
        @else
            <form method="POST" action="{{ route('collect.store', $wall) }}" enctype="multipart/form-data" class="panel space-y-5 p-6 sm:p-8" data-collect>
                @csrf

                @if ($wall->ask_for_rating)
                    <fieldset>
                        <legend class="label">Your rating</legend>
                        <div class="flex gap-1" data-rating>
                            @for ($i = 1; $i <= 5; $i++)
                                <label class="cursor-pointer" title="{{ $i }} {{ Str::plural('star', $i) }}">
                                    <input type="radio" name="rating" value="{{ $i }}" class="peer sr-only" @checked((int) old('rating') === $i) required>
                                    <span class="sr-only">{{ $i }} {{ Str::plural('star', $i) }}</span>
                                    <svg viewBox="0 0 20 20" class="size-9 fill-zinc-200 transition peer-focus-visible:outline-2 peer-focus-visible:outline-[var(--accent)] dark:fill-zinc-700" data-star="{{ $i }}" aria-hidden="true">
                                        <path d="M10 1.5l2.6 5.3 5.9.9-4.25 4.1 1 5.8L10 14.9l-5.25 2.7 1-5.8L1.5 7.7l5.9-.9L10 1.5z" />
                                    </svg>
                                </label>
                            @endfor
                        </div>
                        @error('rating') <p class="error">{{ $message }}</p> @enderror
                    </fieldset>
                @endif

                <x-field name="body" label="Your testimonial">
                    <textarea id="body" name="body" rows="5" class="input focus:border-[var(--accent)] focus:ring-[var(--accent)]/15" required minlength="10" maxlength="2000" placeholder="A sentence or two is perfect.">{{ old('body') }}</textarea>
                </x-field>

                <div class="grid gap-5 sm:grid-cols-2">
                    <x-field name="author_name" label="Your name">
                        <input id="author_name" name="author_name" class="input" value="{{ old('author_name') }}" required maxlength="80" autocomplete="name">
                    </x-field>
                    <x-field name="author_title" label="Title and company" hint="Optional">
                        <input id="author_title" name="author_title" class="input" value="{{ old('author_title') }}" maxlength="120" autocomplete="organization-title" placeholder="Founder at Acme">
                    </x-field>
                </div>

                <x-field name="author_email" label="Email" hint="Optional. Only {{ $wall->name }} sees it, in case they want to thank you.">
                    <input id="author_email" name="author_email" type="email" class="input" value="{{ old('author_email') }}" maxlength="255" autocomplete="email">
                </x-field>

                @if ($wall->ask_for_avatar)
                    <x-field name="avatar" label="Your photo" hint="Optional. Testimonials with a face are trusted more.">
                        <input id="avatar" name="avatar" type="file" accept="image/png,image/jpeg,image/webp" class="block w-full text-sm text-zinc-600 file:mr-3 file:rounded-lg file:border-0 file:bg-zinc-100 file:px-3 file:py-2 file:text-sm file:font-semibold file:text-zinc-800 hover:file:bg-zinc-200 dark:text-zinc-400 dark:file:bg-zinc-800 dark:file:text-zinc-100">
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
                        I agree that {{ $wall->name }} may show my testimonial, name, title and photo on their website.
                    </label>
                    @error('consent') <p class="error">{{ $message }}</p> @enderror
                </div>

                <button class="btn w-full bg-[var(--accent)] py-3 text-base text-white shadow-sm hover:brightness-110">Send testimonial</button>
            </form>
        @endif

        <p class="mt-8 text-center text-xs text-zinc-400">
            <a href="https://github.com/Arda-dd/praisewall" class="hover:text-zinc-600 dark:hover:text-zinc-300">Powered by Praisewall</a>
        </p>
    </main>
</x-layouts.public>
