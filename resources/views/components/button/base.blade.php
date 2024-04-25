@if($attributes->hasAny(['href', 'x-bind:href']))
    <a {{ $attributes }}>
        {{ $slot }}
    </a>
@else
    <button
        {{ $attributes->merge(['type' => 'button']) }}
        @if($attributes->has('wire:target')) wire:loading.attr="disabled" @endif
    >
        {{ $slot }}
    </button>
@endif
