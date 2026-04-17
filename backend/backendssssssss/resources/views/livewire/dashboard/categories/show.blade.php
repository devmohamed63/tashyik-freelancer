<div>
    <div class="space-y-4 p-4 md:p-5">
        <!-- Modal loader -->
        <x-dashboard.loaders.centered wire:loading.class.remove="hidden" />

        <!-- Modal title -->
        <x-dashboard.modals.title :value="'تفاصيل القسم: ' . ($category ? $category->name : '')" />

        <!-- Content (Table) -->
        <div class="flex-grow overflow-auto relative min-h-[40vh]">
            <div wire:loading class="absolute inset-0 z-10 flex justify-center items-center py-10 bg-white/50 backdrop-blur-sm">
                <div class="h-8 w-8 animate-spin rounded-full border-4 border-brand-500 border-t-transparent"></div>
            </div>

            <div wire:loading.remove>
                @if(count($servicesData) > 0)
                    <div class="overflow-x-auto rounded-lg border border-gray-200 shadow-sm relative z-0">
                        <table class="w-full text-sm text-right">
                            <thead class="bg-gray-50 text-xs uppercase text-gray-700">
                                <tr>
                                    <th scope="col" class="px-6 py-4 font-bold">{{ __('ui.service') }}</th>
                                    <th scope="col" class="px-6 py-4 font-bold text-center">{{ __('ui.service_providers') }}</th>
                                    <th scope="col" class="px-6 py-4 font-bold text-center">{{ __('ui.orders') }}</th>
                                    <th scope="col" class="px-6 py-4 font-bold text-center">نسبة النجاح</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200">
                                @foreach($servicesData as $item)
                                    <tr class="hover:bg-gray-50/50 transition-colors">
                                        <td class="px-6 py-4 font-medium text-gray-900">
                                            {{ $item['name'] }}
                                        </td>
                                        <td class="px-6 py-4 text-center border-l border-r">
                                            <span class="inline-flex items-center justify-center rounded-full px-2.5 py-0.5 font-bold {{ $item['providers_count'] == 0 ? 'bg-red-100 text-red-700' : 'bg-brand-100 text-brand-700' }}">
                                                {{ number_format($item['providers_count']) }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 text-center border-l">
                                            <span class="font-semibold text-gray-700">{{ number_format($item['orders_count']) }}</span>
                                        </td>
                                        <td class="px-6 py-4 text-center">
                                            <span class="inline-flex items-center justify-center rounded-full px-2.5 py-0.5 text-xs font-bold {{ $item['success_rate'] >= 80 ? 'bg-green-100 text-green-700' : ($item['success_rate'] >= 50 ? 'bg-yellow-100 text-yellow-700' : 'bg-red-100 text-red-700') }}">
                                                {{ $item['success_rate'] }}%
                                            </span>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="py-8 text-center text-gray-500">
                        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                        </svg>
                        <p class="mt-4 font-medium">{{ __('ui.no_results') }}</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
