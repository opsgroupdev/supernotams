@props(['route'])

@use(Illuminate\Support\Facades\Route)

<a
    href="{{ route($route) }}"
    {{ $attributes->class([
        'inline-flex items-center border-b-2 px-1 pt-1 text-sm font-medium',
        'border-indigo-800 text-gray-900' => Route::is($route),
        'border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700' => ! Route::is($route),
    ]) }}
>
    {{ $slot }}
</a>
