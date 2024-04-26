@php use Illuminate\Support\Facades\Route; @endphp
<nav
    class="z-10 bg-white shadow"
    x-data="{ open: false }"
>
    <div class="mx-auto max-w-7xl px-2 sm:px-6 lg:px-8">
        <div class="relative flex h-16 justify-between">
            <div class="absolute inset-y-0 left-0 flex items-center sm:hidden">
                <!-- Mobile menu button -->
                <button
                    type="button"
                    class="relative inline-flex items-center justify-center rounded-md p-2 text-gray-400 hover:bg-gray-100 hover:text-gray-500 focus:outline-none focus:ring-2 focus:ring-inset focus:ring-indigo-800"
                    x-on:click="open = !open"
                    aria-controls="mobile-menu"
                    aria-expanded="false"
                >
                    <span class="absolute -inset-0.5"></span>
                    <span class="sr-only">Open navigation menu</span>
                    <x-heroicon-o-bars-3 x-show="!open" class="block h-6 w-6" />
                    <x-heroicon-o-x-mark x-cloak x-show="open" class="block h-6 w-6" />
                </button>
            </div>

            <div class="flex flex-1 items-center justify-center sm:items-stretch sm:justify-start">
                <div class="flex flex-shrink-0 items-center">
                    <a href="{{ route('notam.index') }}">
                        <img
                            class="h-10 w-auto"
                            src="{{ asset('images/supernotams-logo.svg') }}"
                            alt="SuperNOTAMS"
                        >
                    </a>
                </div>

                <div class="hidden sm:ml-6 sm:flex sm:space-x-8">
                    <x-navigation.item route="notam.index">
                        App
                    </x-navigation.item>
                    <x-navigation.item route="playground">
                        Playground
                    </x-navigation.item>
                </div>
            </div>
        </div>
    </div>

    <!-- Mobile menu, show/hide based on menu state. -->
    <div
        x-cloak
        x-collapse
        x-show="open"
        class="sm:hidden"
        id="mobile-menu"
    >
        <div class="space-y-1 pb-4 pt-2">
            <x-navigation.mobile-item route="notam.index">
                App
            </x-navigation.mobile-item>
            <x-navigation.mobile-item route="playground">
                Playground
            </x-navigation.mobile-item>
        </div>
    </div>
</nav>

@prependonce('scripts')
    <script defer src="https://cdn.jsdelivr.net/npm/@alpinejs/collapse@3.x.x/dist/cdn.min.js"></script>
@endprependonce

