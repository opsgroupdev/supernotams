@props(['tag'])

@php
    $colorClasses = match ($tag->color()) {
        'blue' => 'bg-blue-100 text-blue-700',
        'violet' => 'bg-violet-100 text-violet-700',
        'red' => 'bg-red-100 text-red-700',
        'yellow' => 'bg-yellow-100 text-yellow-800',
        'green' => 'bg-green-100 text-green-700',
        'fuchsia' => 'bg-fuchsia-100 text-fuchsia-700',
        'orange' => 'bg-orange-100 text-orange-700',
        'zinc' => 'bg-zinc-100 text-zinc-600',
    };
@endphp

<span {{ $attributes->class(['inline-flex items-center rounded-md px-2 py-1 text-xs font-medium', $colorClasses]) }}>
    {{ $tag->fullLabel() }}
</span>
