@props([
    /** @var \App\Models\PlaygroundNotam */
    'notam',
])

<x-playground.chat-bubble :timestamp="$notam->created_at">
    <x-slot:avatar>
        <x-heroicon-s-user-circle class="text-gray-500" />
    </x-slot:avatar>

    <pre class="flex-auto text-xs text-gray-500 whitespace-pre-wrap">{{ $notam->text }}</pre>
</x-playground.chat-bubble>
