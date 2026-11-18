<x-layouts.app :title="$wall->name">
    @include('walls._header', ['active' => 'inbox'])

    <div class="mb-6 flex flex-wrap items-center justify-between gap-3">
        <div class="inline-flex rounded-xl bg-zinc-100 p-1 dark:bg-zinc-900">
            @foreach (\App\Enums\TestimonialStatus::cases() as $tab)
                <a href="{{ route('walls.show', [$wall, 'status' => $tab->value]) }}" @class([
                    'flex items-center gap-2 rounded-lg px-3.5 py-1.5 text-sm font-medium',
                    'bg-white text-zinc-900 shadow-sm dark:bg-zinc-800 dark:text-zinc-100' => $status === $tab,
                    'text-zinc-500 hover:text-zinc-900 dark:text-zinc-400 dark:hover:text-zinc-100' => $status !== $tab,
                ])>
                    {{ $tab->label() }}
                    <span class="rounded-full bg-zinc-200 px-1.5 text-xs dark:bg-zinc-700">{{ $counts[$tab->value] ?? 0 }}</span>
                </a>
            @endforeach
        </div>
        <button type="button" class="btn-secondary" data-toggle="#add-testimonial">+ Add manually</button>
    </div>

    <div id="add-testimonial" @class(['mb-8', 'hidden' => ! $errors->any()])>
        <form method="POST" action="{{ route('testimonials.store', $wall) }}" enctype="multipart/form-data" class="panel p-6">
            @csrf
            <h2 class="font-semibold">Add a testimonial by hand</h2>
            <p class="mb-5 text-sm text-zinc-500 dark:text-zinc-400">From an email, a review site or a social post. It's published right away.</p>
            @include('testimonials._fields', ['testimonial' => new \App\Models\Testimonial(['rating' => 5])])
            <div class="mt-5 flex justify-end">
                <button class="btn-primary">Add to wall</button>
            </div>
        </form>
    </div>

    @if ($testimonials->isEmpty())
        <div class="panel px-6 py-14 text-center">
            @if ($status === \App\Enums\TestimonialStatus::Pending)
                <p class="font-semibold">Nothing to review</p>
                <p class="mx-auto mt-1 max-w-md text-sm text-zinc-500 dark:text-zinc-400">Send the collection link to a few happy customers. New testimonials show up here first, and only the ones you approve go on the wall.</p>
                <button type="button" class="btn-primary mt-5" data-copy="{{ $wall->collectUrl() }}">Copy collection link</button>
            @else
                <p class="text-sm text-zinc-500 dark:text-zinc-400">No {{ strtolower($status->label()) }} testimonials.</p>
            @endif
        </div>
    @else
        <div class="grid gap-4 lg:grid-cols-2">
            @foreach ($testimonials as $testimonial)
                @include('testimonials._card')
            @endforeach
        </div>
        <div class="mt-6">{{ $testimonials->links() }}</div>
    @endif
</x-layouts.app>
