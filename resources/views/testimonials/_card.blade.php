@php($status = $testimonial->status)
<article @class([
    'panel p-5',
    'ring-2 ring-brand-600/30' => $testimonial->featured,
])>
    <div class="flex flex-wrap items-start gap-3">
        <x-avatar :testimonial="$testimonial" />
        <div class="min-w-0 flex-1">
            <p class="font-semibold">
                @if ($testimonial->author_url)
                    <a href="{{ $testimonial->author_url }}" target="_blank" rel="noopener nofollow" class="hover:text-brand-700">{{ $testimonial->author_name }} <x-external /></a>
                @else
                    {{ $testimonial->author_name }}
                @endif
            </p>
            <p class="text-sm text-zinc-500 dark:text-zinc-400">
                {{ $testimonial->author_title }}
                @if ($testimonial->author_title && $testimonial->author_email) · @endif
                @if ($testimonial->author_email)
                    <a href="mailto:{{ $testimonial->author_email }}" class="hover:text-zinc-900 dark:hover:text-zinc-100">{{ $testimonial->author_email }}</a>
                @endif
            </p>
        </div>
        <div class="flex items-center gap-2 text-xs text-zinc-500 dark:text-zinc-400">
            @if ($testimonial->featured)
                <span class="rounded-full bg-brand-100 px-2 py-0.5 font-semibold text-brand-700 dark:bg-brand-700/30 dark:text-brand-100">Pinned</span>
            @endif
            @if ($testimonial->source === \App\Enums\TestimonialSource::Manual)
                <span class="rounded-full bg-zinc-100 px-2 py-0.5 font-semibold dark:bg-zinc-800">Added by you</span>
            @endif
            <time datetime="{{ $testimonial->created_at->toIso8601String() }}" title="{{ $testimonial->created_at->toDayDateTimeString() }}">{{ $testimonial->created_at->diffForHumans() }}</time>
        </div>
    </div>

    @if ($testimonial->rating)
        <x-stars :rating="$testimonial->rating" class="mt-4" />
    @endif
    <p class="mt-3 whitespace-pre-line break-words text-[15px] leading-relaxed text-zinc-700 dark:text-zinc-300">{{ $testimonial->body }}</p>

    <div class="mt-5 flex flex-wrap items-center gap-1 border-t border-zinc-100 pt-3 dark:border-zinc-800">
        @if ($status !== \App\Enums\TestimonialStatus::Approved)
            <form method="POST" action="{{ route('testimonials.status', $testimonial) }}">
                @csrf @method('PATCH')
                <input type="hidden" name="status" value="approved">
                <button class="btn bg-emerald-600 px-3 py-1.5 text-white hover:bg-emerald-500">Approve</button>
            </form>
        @endif
        @if ($status === \App\Enums\TestimonialStatus::Approved)
            <form method="POST" action="{{ route('testimonials.feature', $testimonial) }}">
                @csrf @method('PATCH')
                <button class="btn-ghost">{{ $testimonial->featured ? 'Unpin' : 'Pin to top' }}</button>
            </form>
        @endif
        @if ($status !== \App\Enums\TestimonialStatus::Rejected)
            <form method="POST" action="{{ route('testimonials.status', $testimonial) }}">
                @csrf @method('PATCH')
                <input type="hidden" name="status" value="rejected">
                <button class="btn-ghost">{{ $status === \App\Enums\TestimonialStatus::Approved ? 'Unpublish' : 'Reject' }}</button>
            </form>
        @endif
        <a href="{{ route('testimonials.edit', $testimonial) }}" class="btn-ghost">Edit</a>
        <a href="{{ route('testimonials.image', $testimonial) }}" class="btn-ghost">Share as image</a>
        <form method="POST" action="{{ route('testimonials.destroy', $testimonial) }}" class="ml-auto" data-confirm="Delete this testimonial for good?">
            @csrf @method('DELETE')
            <button class="btn-danger">Delete</button>
        </form>
    </div>
</article>
