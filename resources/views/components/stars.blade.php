@props(['rating'])
<span {{ $attributes->class('inline-flex gap-0.5') }} role="img" aria-label="{{ $rating }} out of 5 stars">
    @for ($i = 1; $i <= 5; $i++)
        <svg viewBox="0 0 20 20" class="size-4 {{ $i <= $rating ? 'fill-amber-400' : 'fill-zinc-200 dark:fill-zinc-700' }}" aria-hidden="true">
            <path d="M10 1.5l2.6 5.3 5.9.9-4.25 4.1 1 5.8L10 14.9l-5.25 2.7 1-5.8L1.5 7.7l5.9-.9L10 1.5z" />
        </svg>
    @endfor
</span>
