@props(['style', 'accent' => '#6d28d9'])
{{-- A tiny picture of a card in each style, for the style pickers. --}}
<span class="relative grid size-9 flex-none place-items-center overflow-hidden rounded-lg bg-zinc-100 dark:bg-zinc-800" aria-hidden="true" style="--sw: {{ $accent }}">
    @switch($style)
        @case('minimal')
            <span class="flex w-6 flex-col gap-1"><span class="h-0.5 rounded bg-zinc-400"></span><span class="h-0.5 w-4 rounded bg-zinc-400"></span><span class="mt-0.5 h-px bg-zinc-300 dark:bg-zinc-600"></span></span>
            @break
        @case('bubble')
            <span class="relative h-4 w-6 rounded-md border border-zinc-300 bg-white dark:border-zinc-600 dark:bg-zinc-900"><span class="absolute -bottom-1 left-1 size-1.5 rotate-45 border-r border-b border-zinc-300 bg-white dark:border-zinc-600 dark:bg-zinc-900"></span></span>
            @break
        @case('glass')
            <span class="absolute inset-0" style="background: linear-gradient(135deg, var(--sw), #f472b6)"></span><span class="relative h-5 w-6 rounded-md border border-white/60 bg-white/50 backdrop-blur"></span>
            @break
        @case('bold')
            <span class="h-5 w-6 rounded-md" style="background: var(--sw)"></span>
            @break
        @case('elegant')
            <span class="font-serif text-lg leading-none italic" style="color: var(--sw)">&ldquo;</span>
            @break
        @default
            <span class="h-5 w-6 rounded-md border border-zinc-300 bg-white shadow-sm dark:border-zinc-600 dark:bg-zinc-900"></span>
    @endswitch
</span>
