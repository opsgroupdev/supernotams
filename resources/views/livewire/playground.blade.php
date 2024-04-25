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
            class="flex-auto"
        >
            <x-playground.input-group>
                <x-slot:actions>
                    <button
                        type="submit"
                        wire:target="parse"
                        wire:loading.attr="disabled"
                        class="shrink-0 inline-flex items-center rounded-md bg-white px-2.5 py-1.5 text-sm font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50"
                    >
                        <x-spinner
                            class="size-4 mr-2 text-gray-700"
                            wire:target="parse"
                            wire:loading
                        />
                        Parse NOTAM
                    </button>
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
