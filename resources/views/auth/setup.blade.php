<x-layouts.auth title="Welcome to Praisewall" intro="Create the admin account for this install. You only do this once.">
    <form method="POST" action="{{ route('setup') }}" class="space-y-4">
        @csrf
        <x-field name="name" label="Your name">
            <input id="name" name="name" class="input" value="{{ old('name') }}" required autofocus autocomplete="name">
        </x-field>
        <x-field name="email" label="Email">
            <input id="email" name="email" type="email" class="input" value="{{ old('email') }}" required autocomplete="email">
        </x-field>
        <x-field name="password" label="Password" hint="At least 8 characters.">
            <input id="password" name="password" type="password" class="input" required autocomplete="new-password">
        </x-field>
        <x-field name="password_confirmation" label="Confirm password">
            <input id="password_confirmation" name="password_confirmation" type="password" class="input" required autocomplete="new-password">
        </x-field>
        <button class="btn-primary w-full">Create account</button>
    </form>
</x-layouts.auth>
