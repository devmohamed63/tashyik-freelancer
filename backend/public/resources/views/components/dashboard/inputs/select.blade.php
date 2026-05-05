<div>
    <x-dashboard.label :name="$label" :for="$label" :required="$required ?? false" />

    @php
        $jsId = isset($id) ? str_replace('-', '_', $id) : '';
    @endphp

    <div
        class="relative"
        x-data="{
            search: '',
            selected_{{ $jsId }}: [
                @if (is_array($selected)) @foreach ($selected ?? [] as $selectedChild) '{{ $selectedChild }}', @endforeach
            @else
            {{ $selected }} @endif
            ],
            items: [
                @foreach ($children ?? [] as $child) { id: '{{ $child->id }}', value: '{{ isset($childKey) ? $child->{$childKey} : $child->id }}', name: '{{ $child->name }}' }, @endforeach
            ],
            get filteredItems() {
                return this.items.filter(i => i.name.toLowerCase().includes(this.search) || i.name.includes(this.search))
            },
            get selectedItems() {
                return @isset($single)
                this.items.filter(i => i.id == this.selected_{{ $jsId }})
                @else
                this.items.filter(i => this.selected_{{ $jsId }}.includes((i.value).toString()))
                @endisset
            }
            @isset($single),
            removeSelected() {
                this.selected_{{ $jsId }} = ''
            }
            @endisset
        }"
        @isset($globalValue)
            x-init="$watch('selected_{{ $jsId }}', value => {{ $globalValue }} = !selected_{{ $jsId }}.length > 0)"
        @endisset>

        <div class="flex flex-col gap-2 border border-gray-200 dark:border-gray-700 rounded-lg p-2 shadow-theme-xs">

            <span x-show="selected_{{ $jsId }}.length > 0" class="text-gray-500 my-1">
                <div class="flex flex-wrap gap-2">
                    <template x-for="selectedItem in selectedItems" :key="selectedItem.id">
                        <div class="inline-flex items-center justify-center gap-1 rounded-full bg-success-50 px-2.5 py-0.5 text-xs font-medium text-success-600 dark:bg-success-500/15 dark:text-success-500">
                            <span x-text="selectedItem.name"></span>
                            @isset($single)
                                <span @click="removeSelected" class="bg-success-200 dark:bg-success-500/15 w-4 h-4 rounded-full hover:bg-success-300 dark:hover:bg-success-600/15 cursor-pointer flex items-center justify-center -me-2">X</span>
                            @endisset
                        </div>
                    </template>
                </div>
            </span>

            <input
                class="dark:bg-dark-900 focus:border-brand-300 focus:ring-brand-500/10 dark:focus:border-brand-800 h-11 w-full rounded-md border border-gray-200 bg-transparent px-4 py-2.5 text-sm text-gray-800 placeholder:text-gray-400 focus:ring-3 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 @error($name) input-error @else @if (old($name)) input-success @endif @enderror"
                type="text"
                id="{{ $label }}"
                placeholder="{{ __('ui.results_search') }}"
                x-model="search"
                autocomplete="off" />

            <div class="max-h-72 overflow-auto light-scrollbar p-2 flex flex-col md:grid grid-cols-2 gap-1">
                <template x-for="item in filteredItems" :key="item.id">
                    <label x-bind:for="`{{ $id ?? 'item' }}-${item.id}`" class="p-2 hover:bg-gray-100 dark:hover:bg-gray-900 rounded-md">
                        <div class="flex items-center">
                            <input
                                x-bind:id="`{{ $id ?? 'item' }}-${item.id}`"
                                x-bind:value="item.value"
                                @isset($single) type="radio" @else type="checkbox" @endisset
                                x-model="selected_{{ $jsId }}"
                                name="{{ $name }}"
                                {{ $attributes->only(['wire:model', 'wire:model.live', 'wire:change']) }}
                                class="w-4 h-4 text-primary bg-gray-100 border-gray-300 rounded focus:ring-primary-ultra-light focus:ring-2 dark:bg-gray-700 dark:border-gray-600 dark:focus:ring-primary dark:ring-offset-gray-800">
                            <label x-bind:for="`{{ $id ?? 'item' }}-${item.id}`" class="ms-2 text-sm font-medium text-gray-500 dark:text-gray-300" x-text="item.name"></label>
                        </div>
                    </label>
                </template>
            </div>
        </div>

        @error($label)
            <span class="absolute top-1/2 end-3.5 -translate-y-1/2">
                <svg width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path fill-rule="evenodd" clip-rule="evenodd"
                        d="M2.58325 7.99967C2.58325 5.00813 5.00838 2.58301 7.99992 2.58301C10.9915 2.58301 13.4166 5.00813 13.4166 7.99967C13.4166 10.9912 10.9915 13.4163 7.99992 13.4163C5.00838 13.4163 2.58325 10.9912 2.58325 7.99967ZM7.99992 1.08301C4.17995 1.08301 1.08325 4.17971 1.08325 7.99967C1.08325 11.8196 4.17995 14.9163 7.99992 14.9163C11.8199 14.9163 14.9166 11.8196 14.9166 7.99967C14.9166 4.17971 11.8199 1.08301 7.99992 1.08301ZM7.09932 5.01639C7.09932 5.51345 7.50227 5.91639 7.99932 5.91639H7.99999C8.49705 5.91639 8.89999 5.51345 8.89999 5.01639C8.89999 4.51933 8.49705 4.11639 7.99999 4.11639H7.99932C7.50227 4.11639 7.09932 4.51933 7.09932 5.01639ZM7.99998 11.8306C7.58576 11.8306 7.24998 11.4948 7.24998 11.0806V7.29627C7.24998 6.88206 7.58576 6.54627 7.99998 6.54627C8.41419 6.54627 8.74998 6.88206 8.74998 7.29627V11.0806C8.74998 11.4948 8.41419 11.8306 7.99998 11.8306Z"
                        fill="#F04438"></path>
                </svg>
            </span>
        @else
            @if (old($label))
                <span class="absolute top-1/2 end-3.5 -translate-y-1/2">
                    <svg width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path fill-rule="evenodd" clip-rule="evenodd"
                            d="M2.61792 8.00034C2.61792 5.02784 5.0276 2.61816 8.00009 2.61816C10.9726 2.61816 13.3823 5.02784 13.3823 8.00034C13.3823 10.9728 10.9726 13.3825 8.00009 13.3825C5.0276 13.3825 2.61792 10.9728 2.61792 8.00034ZM8.00009 1.11816C4.19917 1.11816 1.11792 4.19942 1.11792 8.00034C1.11792 11.8013 4.19917 14.8825 8.00009 14.8825C11.801 14.8825 14.8823 11.8013 14.8823 8.00034C14.8823 4.19942 11.801 1.11816 8.00009 1.11816ZM10.5192 7.266C10.8121 6.97311 10.8121 6.49823 10.5192 6.20534C10.2264 5.91245 9.75148 5.91245 9.45858 6.20534L7.45958 8.20434L6.54162 7.28638C6.24873 6.99349 5.77385 6.99349 5.48096 7.28638C5.18807 7.57927 5.18807 8.05415 5.48096 8.34704L6.92925 9.79533C7.0699 9.93599 7.26067 10.015 7.45958 10.015C7.6585 10.015 7.84926 9.93599 7.98991 9.79533L10.5192 7.266Z"
                            fill="#12B76A"></path>
                    </svg>
                </span>
            @endif
        @enderror
    </div>

    @error($label)
        <x-dashboard.inputs.error :message="$message" />
    @enderror
</div>
