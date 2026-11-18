<x-layouts.app :title="$wall->name.' settings'">
    @include('walls._header', ['active' => 'settings'])

    <form method="POST" action="{{ route('walls.update', $wall) }}" enctype="multipart/form-data">
        @csrf
        @method('PUT')
        @include('walls._form')
        <div class="mt-6 flex justify-end">
            <button class="btn-primary">Save settings</button>
        </div>
    </form>

    <div class="panel mt-10 flex flex-wrap items-center justify-between gap-4 border-red-200 p-6 dark:border-red-900/60">
        <div>
            <h2 class="font-semibold">Delete this wall</h2>
            <p class="text-sm text-zinc-500 dark:text-zinc-400">Deletes all {{ $wall->testimonials()->count() }} testimonials and stops the widget. This can't be undone.</p>
        </div>
        <form method="POST" action="{{ route('walls.destroy', $wall) }}" data-confirm="Delete {{ $wall->name }} and all its testimonials?">
            @csrf
            @method('DELETE')
            <button class="btn border border-red-200 text-red-600 hover:bg-red-50 dark:border-red-900 dark:text-red-400 dark:hover:bg-red-950">Delete wall</button>
        </form>
    </div>
</x-layouts.app>
