<x-layouts.dashboard page="view_services">

    <x-dashboard.breadcrumb :page="__('ui.view_services')" />

    @php
        $totalServices = \App\Models\Service::count();
        
        $trendingService = \App\Models\Service::withCount('orders')
            ->orderByDesc('orders_count')
            ->first();

        $totalRevenue = \App\Models\Order::completed()->sum('subtotal');
    @endphp

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-5 mb-5 mt-5">
        <x-dashboard.cards.overview
            style="col"
            :index="1"
            title="إجمالي الخدمات"
            :count="$totalServices">
            <!-- heroicons:wrench-screwdriver-solid -->
            <!-- Icon from HeroIcons by Refactoring UI Inc - https://github.com/tailwindlabs/heroicons/blob/master/LICENSE -->
            <g fill="currentColor">
                <path fill-rule="evenodd" d="M11.986 3H12a2 2 0 0 1 2 2v6h-4V5a2 2 0 0 1 1.986-2m-3 8.718V4.809c-.19.06-.375.14-.549.237l-4.74 2.656a2 2 0 0 0-.964 1.488l-.34 2.443a2 2 0 0 0 .546 1.705l3.87 3.966l3.18-3.582ZM15 11.718v-6.91c.19.06.375.14.549.237l4.74 2.656a2 2 0 0 1 .964 1.488l.34 2.443a2 2 0 0 1-.546 1.705l-3.87 3.966l-3.18-3.582Z" clip-rule="evenodd" />
                <path d="m11.594 13.921l-3.324 3.743A2 2 0 0 0 8 19.336L8.434 22h7.132l.434-2.664a2 2 0 0 0-.27-1.672l-3.324-3.743a1 1 0 0 1-.812 0" />
            </g>
        </x-dashboard.cards.overview>

        <x-dashboard.cards.overview
            style="col"
            :index="2"
            title="متوسط الإيراد الكلي (جميع الخدمات)"
            :count="number_format($totalRevenue) . ' ' . __('ui.currency')">
            <!-- mdi:wallet-bifold -->
            <!-- Icon from Material Design Icons by Pictogrammers - https://github.com/Templarian/MaterialDesign/blob/master/LICENSE -->
            <path fill="currentColor" d="M18 7H6v1h12zm2-2H4a2 2 0 0 0-2 2v10a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2V7a2 2 0 0 0-2-2m0 8h-4a2 2 0 0 1-2-2v-1a2 2 0 0 1 2-2h4v5m0-7h-4V4.5a2.5 2.5 0 0 1 2.5 2.5V8A4 4 0 0 0 16 12M6 4h8v3H4z" />
        </x-dashboard.cards.overview>

        <x-dashboard.cards.overview
            style="col"
            :index="8"
            title="الخدمة الأكثر نشاطاً"
            :count="$trendingService && $trendingService->orders_count > 0 ? $trendingService->name . ' (' . number_format($trendingService->orders_count) . ')' : '-'">
            <!-- heroicons:fire-solid -->
            <!-- Icon from HeroIcons by Refactoring UI Inc - https://github.com/tailwindlabs/heroicons/blob/master/LICENSE -->
            <path fill="currentColor" fill-rule="evenodd" d="M12.963 2.286a.75.75 0 0 0-1.071-.136l-5.612 4.908c-.732.64-1.28 1.517-1.611 2.482a4.42 4.42 0 0 0 .5 3.738A5.5 5.5 0 0 0 3 17.5A6.5 6.5 0 0 0 9.5 24h5A6.5 6.5 0 0 0 21 17.5a5.5 5.5 0 0 0-2.618-4.664a4.42 4.42 0 0 0-1.391-4.707L12.963 2.286ZM10 16.5a1.5 1.5 0 1 1 3 0v4H10v-4Z" clip-rule="evenodd"/>
        </x-dashboard.cards.overview>
    </div>

    <livewire:dashboard.services-table />

</x-layouts.dashboard>
