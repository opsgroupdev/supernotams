@props(['size' => 'base'])

@php
    $sizeClasses = match ($size) {
        'base' => 'px-3 py-2',
        'sm' => 'px-2.5 py-1.5',
    };
@endphp

<x-button.base
        {{ $attributes->twMerge([
            'inline-flex items-center rounded-md bg-indigo-900 text-sm font-semibold text-white shadow-sm hover:bg-blue-800 active:bg-blue-700 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-900 transition',
            $sizeClasses,
        ]) }}
>
    {{ $slot }}
</x-button.base>
