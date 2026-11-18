<x-layouts.app title="Edit testimonial">
    <div class="mx-auto max-w-3xl">
        <a href="{{ route('walls.show', [$testimonial->wall, 'status' => $testimonial->status->value]) }}" class="text-sm text-zinc-500 hover:text-zinc-900 dark:text-zinc-400 dark:hover:text-zinc-100">← {{ $testimonial->wall->name }}</a>
        <h1 class="mt-2 text-2xl font-semibold tracking-tight">Edit testimonial</h1>
        <p class="mt-1 mb-8 text-sm text-zinc-500 dark:text-zinc-400">Fix typos or formatting, but keep the customer's words. Their trust is why the wall works.</p>

        <form method="POST" action="{{ route('testimonials.update', $testimonial) }}" enctype="multipart/form-data" class="panel p-6">
            @csrf
            @method('PUT')
            @include('testimonials._fields')
            <div class="mt-6 flex justify-end gap-3">
                <a href="{{ route('walls.show', [$testimonial->wall, 'status' => $testimonial->status->value]) }}" class="btn-secondary">Cancel</a>
                <button class="btn-primary">Save</button>
            </div>
        </form>
    </div>
</x-layouts.app>
