<div {{ $attributes->class('relative') }}>
    <div
        class="absolute inset-0 flex flex-col-reverse overflow-auto scroll-smooth"
        x-data="{ scroll: () => { $el.scrollTo(0, $el.scrollHeight); }}"
        x-init="scroll(); Livewire.hook('morph.updated', () => { scroll(); })"
    >
        @if($slot->hasActualContent())
            <ul role="list" class="mx-auto w-full max-w-2xl px-4 py-5 sm:p-6 space-y-6" x-auto-animate>
                {{ $slot }}
            </ul>
        @else
            <x-playground.empty-state />
        @endif
    </div>
</div>

@pushOnce('scripts')
    <script src="https://cdn.jsdelivr.net/npm/@marcreichel/alpine-auto-animate@latest/dist/alpine-auto-animate.min.js" defer></script>
@endpushonce
