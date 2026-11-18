<x-layouts.auth title="Log in to Praisewall">
    <form method="POST" action="{{ route('login') }}" class="space-y-4">
        @csrf
        <x-field name="email" label="Email">
            <input id="email" name="email" type="email" class="input" value="{{ old('email') }}" required autofocus autocomplete="email">
        </x-field>
        <x-field name="password" label="Password">
            <input id="password" name="password" type="password" class="input" required autocomplete="current-password">
        </x-field>
        <label class="flex items-center gap-2 text-sm text-zinc-600 dark:text-zinc-400">
            <input type="checkbox" name="remember" value="1" class="size-4 rounded accent-brand-700">
            Remember me
        </label>
        <button class="btn-primary w-full">Log in</button>
    </form>
</x-layouts.auth>
