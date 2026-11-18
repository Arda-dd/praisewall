@props(['testimonial', 'size' => 'size-10'])
@if ($testimonial->avatarUrl())
    <img src="{{ $testimonial->avatarUrl() }}" alt="" {{ $attributes->class([$size, 'flex-none rounded-full object-cover']) }}>
@else
    <span {{ $attributes->class([$size, 'grid flex-none place-items-center rounded-full bg-brand-100 text-sm font-bold text-brand-700 dark:bg-brand-700/30 dark:text-brand-100']) }} aria-hidden="true">{{ $testimonial->initials() }}</span>
@endif
