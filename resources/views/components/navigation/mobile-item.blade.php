@props(['route'])

@use(Illuminate\Support\Facades\Route)

<a
    href="{{ route($route) }}"
    {{ $attributes->class([
        'block border-l-4 py-2 pl-3 pr-4 text-base font-medium',
        'bg-indigo-50 border-indigo-800 text-indigo-900' => Route::is($route),
        'border-transparent text-gray-500 hover:bg-gray-50 hover:border-gray-300 hover:text-gray-700' => ! Route::is($route),
    ]) }}
>
    {{ $slot }}
</a>
