<div class="grid gap-6 lg:grid-cols-[1fr_320px]">
    <div class="panel space-y-5 p-6">
        <div class="grid gap-5 sm:grid-cols-2">
            <x-field name="name" label="Name" hint="Your product, service or company.">
                <input id="name" name="name" class="input" value="{{ old('name', $wall->name) }}" required maxlength="80" data-slug-source>
            </x-field>
            <x-field name="slug" label="Link" hint="Used in the collection link and the widget.">
                <div class="flex rounded-lg shadow-sm">
                    <span class="inline-flex items-center rounded-l-lg border border-r-0 border-zinc-300 bg-zinc-50 px-3 font-mono text-xs text-zinc-500 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-400">/c/</span>
                    <input id="slug" name="slug" class="input rounded-l-none font-mono shadow-none" value="{{ old('slug', $wall->slug) }}" maxlength="80" pattern="[a-z0-9_-]*" data-slug-target @if ($wall->exists) data-slug-locked @endif>
                </div>
            </x-field>
        </div>

        <x-field name="headline" label="Form headline">
            <input id="headline" name="headline" class="input" value="{{ old('headline', $wall->headline) }}" required maxlength="120">
        </x-field>

        <x-field name="prompt" label="Question to your customers" hint="Shown above the text box. A specific question gets better answers than 'Leave a review'.">
            <textarea id="prompt" name="prompt" rows="3" class="input" required maxlength="500">{{ old('prompt', $wall->prompt) }}</textarea>
        </x-field>

        <x-field name="thank_you_message" label="Thank-you message">
            <textarea id="thank_you_message" name="thank_you_message" rows="2" class="input" required maxlength="500">{{ old('thank_you_message', $wall->thank_you_message) }}</textarea>
        </x-field>
    </div>

    <div class="space-y-6">
        <div class="panel space-y-5 p-6">
            <x-field name="accent_color" label="Accent color">
                <div class="flex items-center gap-3">
                    <input id="accent_color" name="accent_color" type="color" value="{{ old('accent_color', $wall->accent_color) }}" class="h-10 w-14 cursor-pointer rounded-lg border border-zinc-300 bg-white p-1 dark:border-zinc-700 dark:bg-zinc-900">
                    <span class="font-mono text-sm text-zinc-500" data-color-label>{{ old('accent_color', $wall->accent_color) }}</span>
                </div>
            </x-field>

            <x-field name="logo" label="Logo" hint="PNG, JPG or WebP, up to 1 MB.">
                @if ($wall->logoUrl())
                    <div class="mb-3 flex items-center gap-3">
                        <img src="{{ $wall->logoUrl() }}" alt="" class="size-12 rounded-lg object-cover">
                        <label class="flex items-center gap-2 text-sm text-zinc-600 dark:text-zinc-400">
                            <input type="checkbox" name="remove_logo" value="1" class="size-4 accent-brand-700"> Remove
                        </label>
                    </div>
                @endif
                <input id="logo" name="logo" type="file" accept="image/png,image/jpeg,image/webp" class="block w-full text-sm text-zinc-600 file:mr-3 file:rounded-lg file:border-0 file:bg-zinc-100 file:px-3 file:py-2 file:text-sm file:font-semibold file:text-zinc-800 hover:file:bg-zinc-200 dark:text-zinc-400 dark:file:bg-zinc-800 dark:file:text-zinc-100">
            </x-field>
        </div>

        <div class="panel space-y-4 p-6">
            @foreach ([
                'collecting' => ['Accept new testimonials', 'Turn off to close the form.'],
                'ask_for_rating' => ['Ask for a star rating', 'Required when turned on.'],
                'ask_for_avatar' => ['Let people add a photo', 'Optional for them.'],
            ] as $field => [$label, $hint])
                <label class="flex items-start gap-3">
                    <input type="hidden" name="{{ $field }}" value="0">
                    <input type="checkbox" name="{{ $field }}" value="1" @checked(old($field, $wall->{$field})) class="mt-0.5 size-4 accent-brand-700">
                    <span>
                        <span class="block text-sm font-medium">{{ $label }}</span>
                        <span class="block text-sm text-zinc-500 dark:text-zinc-400">{{ $hint }}</span>
                    </span>
                </label>
            @endforeach
        </div>
    </div>
</div>
