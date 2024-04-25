@use(App\Enum\NotamStatus)

<div
    class="h-full flex flex-col"
    x-bind="playground"
>
    <x-playground.chat-wrapper class="grow">
        @foreach($session->notams as $notam)
            <x-playground.chat-bubble :timestamp="$notam->created_at">
                <x-slot:avatar>
                    <x-heroicon-s-user-circle class="text-gray-500" />
                </x-slot:avatar>

                <pre class="flex-auto text-xs text-gray-500 whitespace-pre-wrap">{{ $notam->text }}</pre>
            </x-playground.chat-bubble>

            <x-playground.chat-bubble :timestamp="$notam->processed_at">
                <x-slot:avatar>
                    <img src="{{ asset('images/supernotams-mark.svg') }}" alt="SuperNOTAMs" />
                </x-slot:avatar>

                @if($notam->status === NotamStatus::ERROR)
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <x-heroicon-s-x-circle class="size-5 text-red-400" />
                        </div>
                        <div class="ml-3">
                            <h3 class="text-sm font-medium text-red-700">There was an error tagging the NOTAM.</h3>
                        </div>
                    </div>
                @else
                    <dl class="flex-auto flex flex-wrap text-sm space-y-3">
                        <div class="flex w-full flex-none gap-x-3">
                            <dt class="flex-none py-0.5">
                                <span class="sr-only">Tag</span>
                                <x-heroicon-o-tag class="size-4 text-gray-500" />
                            </dt>
                            <dd>
                                @if(in_array($notam->status, [NotamStatus::UNTAGGED, NotamStatus::PROCESSING], true))
                                    <span class="sr-only" aria-busy="true">Processing...</span>
                                    <div class="animate-pulse h-5 w-32 bg-slate-200 rounded"></div>
                                @else
                                    <x-notam-tag class="-ml-0.5 -my-0.5" :tag="$notam->tag" />
                                @endif
                            </dd>
                        </div>
                        <div class="flex w-full flex-none gap-x-3">
                            <dt class="flex-none py-0.5">
                                <span class="sr-only">Summary</span>
                                <x-heroicon-o-chat-bubble-left-ellipsis class="size-4 text-gray-500" />
                            </dt>
                            <dd class="grow text-gray-700">
                                @if(in_array($notam->status, [NotamStatus::UNTAGGED, NotamStatus::PROCESSING], true))
                                    <span class="sr-only" aria-busy="true">Processing...</span>
                                    <div class="animate-pulse h-3 w-full max-w-64 bg-slate-200 rounded"></div>
                                @else
                                    {{ $notam->summary }}
                                @endif
                            </dd>
                        </div>
                    </dl>
                @endif
            </x-playground.chat-bubble>
        @endforeach
    </x-playground.chat-wrapper>

    <!-- NOTAM form -->
    <div class="bg-white w-full border-t border-gray-100 py-4 md:py-6">
        <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8 flex gap-x-2">
            <x-heroicon-s-user-circle class="h-8 w-8 flex-none text-gray-500" />
            <form
                wire:submit="parse"
                class="flex-auto"
                x-data
            >
                <div class="relative">
                    <div class="overflow-hidden rounded-lg pb-12 shadow-sm ring-1 ring-inset ring-gray-300 focus-within:ring-2 focus-within:ring-indigo-800">
                        <label for="notam" class="sr-only">Paste in a NOTAM to play with…</label>
                        <textarea
                            rows="2"
                            name="notam"
                            id="notam"
                            wire:model="form.notam"
                            wire:target="parse"
                            wire:loading.attr="readonly"
                            class="block w-full resize-none border-0 bg-transparent py-2 text-gray-900 placeholder:text-gray-400 focus:ring-0"
                            placeholder="Paste in a NOTAM to play with…"
                            oninput="this.value = this.value.toUpperCase()"
                            x-ref="input"
                            @error('form.notam')
                            aria-invalid="true" aria-describedby="notam-error"
                            @enderror
                        ></textarea>
                    </div>
                    <div
                        class="absolute inset-x-0 bottom-0 flex items-end justify-between pl-3 pr-2 py-2"
                        x-on:click.self="$refs.input.focus()"
                    >
                        <div class="flex items-center space-x-5">
                            @error('form.notam')
                            <p class="text-sm text-red-600" id="notam-error">{{ $message }}</p>
                            @enderror
                        </div>
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
                    </div>
                </div>
            </form>
        </div>
    </div>

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
