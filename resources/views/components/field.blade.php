@props(['name', 'label', 'hint' => null])
<div {{ $attributes->class('') }}>
    <label for="{{ $name }}" class="label">{{ $label }}</label>
    {{ $slot }}
    @if ($hint)
        <p class="hint">{{ $hint }}</p>
    @endif
    @error($name)
        <p class="error">{{ $message }}</p>
    @enderror
</div>
