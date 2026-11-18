<x-layouts.app title="New wall">
    <div class="mb-8">
        <a href="{{ route('walls.index') }}" class="text-sm text-zinc-500 hover:text-zinc-900 dark:text-zinc-400 dark:hover:text-zinc-100">← Walls</a>
        <h1 class="mt-2 text-2xl font-semibold tracking-tight">New wall</h1>
    </div>

    <form method="POST" action="{{ route('walls.store') }}" enctype="multipart/form-data">
        @csrf
        @include('walls._form')
        <div class="mt-6 flex justify-end gap-3">
            <a href="{{ route('walls.index') }}" class="btn-secondary">Cancel</a>
            <button class="btn-primary">Create wall</button>
        </div>
    </form>
</x-layouts.app>
