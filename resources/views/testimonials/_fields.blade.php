<div class="grid gap-5 sm:grid-cols-2">
    <x-field name="author_name" label="Name">
        <input id="author_name" name="author_name" class="input" value="{{ old('author_name', $testimonial->author_name) }}" required maxlength="80">
    </x-field>
    <x-field name="author_title" label="Title and company" hint="For example: Founder at Acme">
        <input id="author_title" name="author_title" class="input" value="{{ old('author_title', $testimonial->author_title) }}" maxlength="120">
    </x-field>
    <x-field name="author_email" label="Email" hint="Private. Never shown on the wall.">
        <input id="author_email" name="author_email" type="email" class="input" value="{{ old('author_email', $testimonial->author_email) }}" maxlength="255">
    </x-field>
    <x-field name="author_url" label="Link" hint="Their website or profile, shown on their name.">
        <input id="author_url" name="author_url" type="url" class="input" value="{{ old('author_url', $testimonial->author_url) }}" maxlength="255" placeholder="https://">
    </x-field>
    <x-field name="body" label="Testimonial" class="sm:col-span-2">
        <textarea id="body" name="body" rows="4" class="input" required maxlength="2000">{{ old('body', $testimonial->body) }}</textarea>
    </x-field>
    <x-field name="rating" label="Rating">
        <select id="rating" name="rating" class="input">
            <option value="">No rating</option>
            @for ($i = 5; $i >= 1; $i--)
                <option value="{{ $i }}" @selected((string) old('rating', $testimonial->rating) === (string) $i)>{{ str_repeat('★', $i) }}{{ str_repeat('☆', 5 - $i) }}</option>
            @endfor
        </select>
    </x-field>
    <x-field name="avatar" label="Photo">
        @if ($testimonial->avatarUrl())
            <div class="mb-2 flex items-center gap-3">
                <x-avatar :testimonial="$testimonial" />
                <label class="flex items-center gap-2 text-sm text-zinc-600 dark:text-zinc-400">
                    <input type="checkbox" name="remove_avatar" value="1" class="size-4 accent-brand-700"> Remove
                </label>
            </div>
        @endif
        <input id="avatar" name="avatar" type="file" accept="image/png,image/jpeg,image/webp" class="block w-full text-sm text-zinc-600 file:mr-3 file:rounded-lg file:border-0 file:bg-zinc-100 file:px-3 file:py-2 file:text-sm file:font-semibold file:text-zinc-800 hover:file:bg-zinc-200 dark:text-zinc-400 dark:file:bg-zinc-800 dark:file:text-zinc-100">
    </x-field>
</div>
