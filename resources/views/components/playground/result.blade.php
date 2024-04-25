@use(App\Enum\NotamStatus)

@props([
    /** @var \App\Models\PlaygroundNotam */
    'notam',
])

@php
    $isProcessing = in_array($notam->status, [NotamStatus::UNTAGGED, NotamStatus::PROCESSING], true);
@endphp

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
                    @if($isProcessing)
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
                    @if($isProcessing)
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
