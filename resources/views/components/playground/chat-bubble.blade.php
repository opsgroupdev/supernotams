@props(['timestamp' => null])

<li class="flex gap-x-4">
    <div class="mt-2 flex size-8 p-1 flex-none items-center justify-center bg-gray-100">
        {{ $avatar }}
    </div>

    <div class="flex-auto flex flex-col">
        <div class="overflow-hidden rounded-lg bg-white shadow">
            <div class="flex px-5 py-4 sm:p-6">
                {{ $slot }}
            </div>
        </div>

        @if($timestamp)
            <time
                datetime="{{ $timestamp->toAtomString() }}"
                class="ml-auto inline-flex mt-2 py-0.5 px-2 text-xs text-gray-400"
            >
                {{ $timestamp->diffForHumans() }}
            </time>
        @endif
    </div>
</li>
