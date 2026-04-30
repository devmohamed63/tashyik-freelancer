<div>
    <div class="space-y-4 p-4 md:p-5">
        <!-- Modal loader -->
        <x-dashboard.loaders.centered wire:loading.class.remove="hidden" />

        <!-- Modal title -->
        <x-dashboard.modals.title :value="'التوزيع السكاني لخدمة: ' . ($service ? $service->name : '')" />

        <!-- Content -->
        <div class="flex-grow overflow-auto relative min-h-[40vh]">
            <div wire:loading class="absolute inset-0 z-10 flex justify-center items-center py-10 bg-white/50 backdrop-blur-sm">
                <div class="h-8 w-8 animate-spin rounded-full border-4 border-brand-500 border-t-transparent"></div>
            </div>

            <div wire:loading.remove>
                @if(count($citiesData) > 0)
                    <div class="overflow-x-auto rounded-lg border border-gray-200 shadow-sm relative z-0">
                        <table class="w-full text-sm text-right">
                            <thead class="bg-gray-50 text-xs uppercase text-gray-700">
                                <tr>
                                    <th scope="col" class="px-6 py-4 font-bold">المدينة</th>
                                    <th scope="col" class="px-6 py-4 font-bold text-center">الفنيين المتوفرين</th>
                                    <th scope="col" class="px-6 py-4 font-bold text-center">الطلبات الحالية</th>
                                    <th scope="col" class="px-6 py-4 font-bold text-center">تحليل الفجوة (Gap)</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200">
                                @foreach($citiesData as $item)
                                    <tr class="hover:bg-gray-50/50 transition-colors">
                                        <td class="px-6 py-4 font-medium text-gray-900 border-r-4 {{ $item['status'] == 'critical' ? 'border-red-500 bg-red-50/30' : ($item['status'] == 'warning' ? 'border-yellow-500' : 'border-transparent') }}">
                                            {{ $item['name'] }}
                                        </td>
                                        <td class="px-6 py-4 text-center border-l">
                                            <span class="inline-flex items-center justify-center rounded-full px-2.5 py-0.5 font-bold {{ $item['providers_count'] == 0 ? 'bg-red-100 text-red-700' : 'bg-brand-100 text-brand-700' }}">
                                                {{ number_format($item['providers_count']) }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 text-center border-l">
                                            <span class="font-semibold text-gray-700">{{ number_format($item['orders_count']) }}</span>
                                        </td>
                                        <td class="px-6 py-4 text-center">
                                            @if($item['status'] == 'critical')
                                                <span class="inline-flex items-center gap-1 rounded bg-red-100 px-2 py-1 text-xs font-semibold text-red-800">
                                                    <svg class="h-3 w-3" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path></svg>
                                                    {{ $item['status_message'] }}
                                                </span>
                                            @elseif($item['status'] == 'warning')
                                                <span class="inline-flex items-center gap-1 rounded bg-yellow-100 px-2 py-1 text-xs font-semibold text-yellow-800">
                                                    ⚠️ {{ $item['status_message'] }}
                                                </span>
                                            @elseif($item['status'] == 'idle')
                                                <span class="inline-flex items-center gap-1 rounded bg-gray-100 px-2 py-1 text-xs font-semibold text-gray-800">
                                                    💤 {{ $item['status_message'] }}
                                                </span>
                                            @else
                                                <span class="inline-flex items-center gap-1 rounded bg-green-100 px-2 py-1 text-xs font-semibold text-green-800">
                                                    <svg class="h-3 w-3" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path></svg>
                                                    {{ $item['status_message'] }}
                                                </span>
                                            @endif
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
