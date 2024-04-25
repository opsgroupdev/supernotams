<div
    class="h-full flex flex-col"
    x-bind="playground"
>
    <x-playground.chat-wrapper class="grow">
        @foreach($session->notams as $notam)
            <x-playground.notam :$notam />
            <x-playground.result :$notam />
        @endforeach
    </x-playground.chat-wrapper>

    <x-playground.input-wrapper class="flex gap-x-2">
        <x-heroicon-s-user-circle class="h-8 w-8 flex-none text-gray-500" />
        <form
            wire:submit="parse"
            wire:keydown.meta.enter="parse"
            class="flex-auto"
        >
            <x-playground.input-group>
                <x-slot:actions>
                    <x-button.secondary
                        size="sm"
                        type="submit"
                        wire:target="parse"
                    >
                        <x-spinner
                            class="size-4 mr-2 text-gray-700"
                            wire:target="parse"
                            wire:loading
                        />
                        Parse NOTAM
                        <kbd class="hidden md:inline-flex ml-1.5 items-center font-sans text-xs text-gray-400">⌘⏎</kbd>
                    </x-button.secondary>
                </x-slot:actions>
            </x-playground.input-group>
        </form>
    </x-playground.input-wrapper>

    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.bind('playground', {
                'x-on:session-created'() {
                    let url = window.location.pathname
                    url += url.endsWith('/') ? '' : '/'
                    url += this.$event.detail.session

                    window.history.replaceState(window.history.state, null, url)
                }
            })
        })
    </script>
</div>
