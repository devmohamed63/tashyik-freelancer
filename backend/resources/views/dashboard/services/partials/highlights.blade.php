<div class="flex flex-col gap-2 rounded-lg border border-gray-300 dark:border-gray-600 border-dashed p-4">
    <x-dashboard.label name="highlights" locale="ar" :required="true" />

    @error('highlights')
        <x-dashboard.inputs.error :message="$message" />
    @enderror

    <div x-data="{ items: [{{ $highlights ?? "''" }}] }" class="contents">
        <template x-for="(item, index) in items" x-bind:key="index">
            <div class="relative">
                <x-dashboard.inputs.default
                    name="highlights[]"
                    x-bind:id="'highlight-title-' + index"
                    x-bind:value="item"
                    :required="true" />

                <button type="button" x-on:click="items.splice(index, 1)" class="absolute end-2 bottom-2 w-7 h-7 rounded-md bg-red-50 text-red-600 flex items-center justify-center">
                    <!-- uil:trash-alt -->
                    <svg class="w-4 h-4" xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24"><!-- Icon from Unicons by Iconscout - https://github.com/Iconscout/unicons/blob/master/LICENSE -->
                        <path fill="currentColor" d="M10 18a1 1 0 0 0 1-1v-6a1 1 0 0 0-2 0v6a1 1 0 0 0 1 1M20 6h-4V5a3 3 0 0 0-3-3h-2a3 3 0 0 0-3 3v1H4a1 1 0 0 0 0 2h1v11a3 3 0 0 0 3 3h8a3 3 0 0 0 3-3V8h1a1 1 0 0 0 0-2M10 5a1 1 0 0 1 1-1h2a1 1 0 0 1 1 1v1h-4Zm7 14a1 1 0 0 1-1 1H8a1 1 0 0 1-1-1V8h10Zm-3-1a1 1 0 0 0 1-1v-6a1 1 0 0 0-2 0v6a1 1 0 0 0 1 1" />
                    </svg>
                </button>
            </div>
        </template>
        <div class="col-span-full mt-3">
            <x-dashboard.buttons.secondary :name="__('ui.add_more')" x-on:click="items.push('')" type="button" />
        </div>
    </div>
</div>
