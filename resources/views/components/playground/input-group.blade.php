<div {{ $attributes->class(['relative']) }}>
    <div class="overflow-hidden rounded-lg pb-12 shadow-sm ring-1 ring-inset ring-gray-300 focus-within:ring-2 focus-within:ring-indigo-800">
        <label for="notam" class="sr-only">Paste in a NOTAM to play with…</label>
        <textarea
            rows="3"
            name="notam"
            id="notam"
            wire:model="form.notam"
            wire:target="parse"
            wire:loading.attr="readonly"
            class="block w-full resize-none border-0 bg-transparent py-2 text-gray-900 placeholder:text-gray-400 focus:ring-0"
            placeholder="Paste in a NOTAM to play with…"
            oninput="let p = this.selectionStart; this.value = this.value.toUpperCase(); this.setSelectionRange(p, p);"
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

        <div class="flex items-center space-x-2">
            {{ $actions }}
        </div>
    </div>
</div>
