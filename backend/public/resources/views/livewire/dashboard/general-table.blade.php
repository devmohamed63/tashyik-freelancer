<div>
    <div class="relative overflow-hidden rounded-xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03] overflow-x-auto">

        <!-- Table loader -->
        <x-dashboard.loaders.centered wire:loading.class.remove="hidden" />

        <div class="p-4 pb-0 flex flex-row justify-between gap-4 text-nowrap">
            <div class="flex flex-row gap-4 items-center">
                @foreach ($buttons as $button)
                    @if ($button['url'])
                        <a href="{{ $button['url'] }}" @if ($button['target']) target="{{ $button['target'] }}" @endif>
                            @if ($button['view'])
                                {!! $button['view'] !!}
                            @endif
                        </a>
                    @endif
                    @if ($button['wireAction'])
                        <div wire:click="{{ $button['wireAction'] }}">
                            @if ($button['view'])
                                {!! $button['view'] !!}
                            @endif
                        </div>
                    @endif
                    @if ($button['type'] == 'trash')
                        <div x-data="{ switcherToggle: {{ $trashMode ? 'true' : 'false' }} }">
                            {!! $button['view'] !!}
                        </div>
                    @endif
                @endforeach
            </div>
            <div class="flex flex-row gap-4 items-center">
                @foreach ($dropdowns as $dropdown)
                    <x-dashboard.tables.dropdown :dropdown="$dropdown" />
                @endforeach
                @if ($searchableColumns)
                    <x-dashboard.tables.inputs.text :placeholder="__('ui.results_search')" wire:model.live="searchQuery" />
                @endif
                @if ($exportableTable)
                    <x-dashboard.tables.buttons.export wire:click="exportAsExcel" />
                @endif
            </div>
        </div>
        @if (count($selected) > 0)
            <hr class="my-4 border-gray-100 dark:border-gray-800">
            <div class="w-full px-4 text-gray-600 dark:text-gray-400 flex flex-row items-center gap-2">
                <span>{{ __('ui.selected_info', ['count' => count($selected)]) }}.</span>
                @foreach ($buttons as $button)
                    @if ($button['type'] == 'restore')
                        <div wire:click="restore([{{ implode(',', $selected) }}])">
                            {!! $button['view'] !!}
                        </div>
                    @endif
                    @if ($button['type'] == 'delete')
                        <div wire:click="delete([{{ implode(',', $selected) }}])">
                            {!! $button['view'] !!}
                        </div>
                    @endif
                @endforeach
            </div>
        @endif
        <hr class="mt-4 border-gray-100 dark:border-gray-800">
        <div class="max-w-full">
            <table class="min-w-full">
                <!-- table header start -->
                <thead>
                    <tr class="border-b border-gray-100 dark:border-gray-800 bg-gray-50 dark:bg-gray-900/50">
                        @foreach ($fields as $field)
                            <x-dashboard.tables.field
                                :field="$field"
                                :order-column="$orderColumn"
                                :order-type="$orderType" />
                        @endforeach
                    </tr>
                </thead>
                <!-- table header end -->
                <!-- table body start -->
                <tbody
                    @if ($draggableItems) x-sort="(item, newIndex) => $wire.call('reorder', [{ value: item, order: newIndex }])" @endif
                    class="divide-y divide-gray-100 dark:divide-gray-800">
                    @forelse ($results as $result)
                        <tr @if ($draggableItems) x-sort:item="{{ $result->id }}" @endif class="@if ($draggableItems) cursor-grab @endif hover:bg-gray-50 dark:hover:bg-gray-900/50">
                            @foreach ($fields as $field)
                                @if (!$field['authorize'] || ($field['authorize'] && $field['authorize']($result)))
                                    <td class="px-5 py-4 sm:px-6">
                                        @if ($field['type'] == 'checkbox')
                                            <div class="flex items-center">
                                                <x-dashboard.tables.inputs.checkbox :name="'select-result-' . $result->id" :value="$result->id" wire:model.live="selected" />
                                            </div>
                                        @elseif ($field['relation'])
                                            <p class="text-gray-500 text-theme-sm dark:text-gray-400 font-medium">
                                                {{ $result->{$field['relation'][0]}?->{$field['relation'][1]} ?? __('ui.not_exists') }}
                                            </p>
                                        @elseif ($field['customValue'])
                                            <p class="text-gray-500 text-theme-sm dark:text-gray-400 font-medium">
                                                {{ $field['customValue']($result) }}
                                            </p>
                                        @elseif ($field['dateFormat'])
                                            <p class="text-gray-500 text-theme-sm dark:text-gray-400 font-medium">
                                                {{ $result->{$field['name']}->isoFormat(config('app.time_format')) }}
                                            </p>
                                        @elseif ($field['callback'])
                                            {!! $field['callback']($result) !!}
                                        @elseif ($field['type'] == 'action')
                                            @if ($field['url'])
                                                <a class="block w-fit" href="{{ $field['url']($result) }}" @if ($field['target']) target="{{ $field['target'] }}" @endif>
                                                    {!! $field['view'] !!}
                                                </a>
                                            @else
                                                @if ($field['wireAction'])
                                                    <div class="block w-fit" wire:click="{{ $field['wireAction'] }}({{ $result->id }})">
                                                        {!! $field['view'] !!}
                                                    </div>
                                                @else
                                                    {!! $field['view'] !!}
                                                @endif
                                            @endif
                                        @else
                                            <div class="flex items-center">
                                                <p class="text-gray-500 text-theme-sm dark:text-gray-400 font-medium">
                                                    {{ $result->{$field['name']} }}
                                                </p>
                                            </div>
                                        @endif
                                    </td>
                                @endif
                            @endforeach
                        </tr>
                    @empty
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-900/50">
                            <td class="px-5 py-8 sm:px-6 text-center text-gray-500 dark:text-gray-400" colspan="999">
                                {{ __('ui.no_results') }}
                            </td>
                        </tr>
                    @endforelse
                </tbody>
                <!-- table footer start -->
                <tfoot>
                    <tr class="border-b border-gray-100 dark:border-gray-800 bg-gray-50 dark:bg-gray-900/50">
                        @foreach ($fields as $field)
                            <x-dashboard.tables.field :field="$field" :footerFields="true" :trash-mode="$trashMode" />
                        @endforeach
                    </tr>
                </tfoot>
                <!-- table footer end -->
            </table>
        </div>
        <div class="p-2">
            {{ $results->onEachSide(1)->links() }}
        </div>
    </div>

    <x-dashboard.modals.delete :id="$confirmDeleteModalId" />

    @foreach ($modals as $modal)
        <div wire:ignore>
            <x-dashboard.modals.default :id="$modal['id']">
                @livewire($modal['view'])
            </x-dashboard.modals.default>
        </div>
    @endforeach

</div>
