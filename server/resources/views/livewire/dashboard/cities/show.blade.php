<div>
    <div class="space-y-4 p-4 md:p-5">
        <!-- Modal loader -->
        <x-dashboard.loaders.centered wire:loading.class.remove="hidden" />

        <!-- Modal title -->
        <x-dashboard.modals.title :value="__('ui.show') . ' - ' . ($city ? $city->name : '')" />

    <!-- Body -->
    <div>
        <h4 class="text-md font-bold mb-4 text-gray-800 dark:text-gray-200 text-center">{{ __('ui.service_providers') }}</h4>
        
        @if(count($servicesWithCount) > 0)
        <div class="overflow-x-auto relative shadow-sm sm:rounded-lg mb-2">
            <table class="w-full text-sm text-center text-gray-500 dark:text-gray-400">
                <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-800 dark:text-gray-400 border-b border-gray-100 dark:border-gray-700">
                    <tr>
                        <th scope="col" class="px-6 py-3">{{ __('ui.service') }}</th>
                        <th scope="col" class="px-6 py-3">{{ __('ui.category') }}</th>
                        <th scope="col" class="px-6 py-3">{{ __('ui.count') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                    @foreach($servicesWithCount as $item)
                    <tr class="bg-white dark:bg-gray-900 hover:bg-gray-50 dark:hover:bg-gray-800">
                        <td class="px-6 py-4 font-medium text-gray-900 whitespace-nowrap dark:text-white">
                            {{ $item['name'] }}
                        </td>
                        <td class="px-6 py-4">
                            {{ $item['category'] }}
                        </td>
                        <td class="px-6 py-4 font-bold text-primary-600">
                            {{ $item['count'] }}
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @else
        <p class="text-sm text-gray-500 dark:text-gray-400 text-center py-4">{{ __('ui.no_results') }}</p>
        @endif
    </div>

    </div>
</div>
