<x-layouts.dashboard page="view_categories">

    <x-dashboard.breadcrumb :page="__('ui.view_categories')" />

    @php
        $totalCategories = \App\Models\Category::isParent()->count();
        
        $cashCow = \App\Models\Category::isParent()
            ->addSelect([
                'revenue' => \App\Models\Order::selectRaw('COALESCE(sum(subtotal), 0)')
                    ->where('orders.status', \App\Models\Order::COMPLETED_STATUS)
                    ->whereExists(function (\Illuminate\Database\Query\Builder $query) {
                        $query->select(\Illuminate\Support\Facades\DB::raw(1))
                              ->from('services')
                              ->whereColumn('services.id', 'orders.service_id')
                              ->join('categories as subcats', 'subcats.id', '=', 'services.category_id')
                              ->whereColumn('subcats.category_id', 'categories.id');
                    })
            ])
            ->orderBy('revenue', 'desc')
            ->first();

        $trendingCategory = \App\Models\Category::isParent()
            ->addSelect([
                'total_orders' => \App\Models\Order::selectRaw('COUNT(orders.id)')
                    ->whereExists(function (\Illuminate\Database\Query\Builder $query) {
                        $query->select(\Illuminate\Support\Facades\DB::raw(1))
                              ->from('services')
                              ->whereColumn('services.id', 'orders.service_id')
                              ->join('categories as subcats', 'subcats.id', '=', 'services.category_id')
                              ->whereColumn('subcats.category_id', 'categories.id');
                    })
            ])
            ->orderBy('total_orders', 'desc')
            ->first();
    @endphp

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-5 mb-5 mt-5">
        <x-dashboard.cards.overview
            style="col"
            :index="1"
            title="إجمالي الأقسام الرئيسية"
            :count="$totalCategories">
            <!-- mdi:format-list-bulleted-type -->
            <!-- Icon from Material Design Icons by Pictogrammers - https://github.com/Templarian/MaterialDesign/blob/master/LICENSE -->
            <path fill="currentColor" d="M3 4v4h4V4zm6 1v2h12V5zm-6 6v4h4v-4zm6 1v2h12v-2zm-6 6v4h4v-4zm6 1v2h12v-2z"/>
        </x-dashboard.cards.overview>

        <x-dashboard.cards.overview
            style="col"
            :index="2"
            title="القسم الذهبي (الأعلى إيرادات)"
            :count="$cashCow && $cashCow->revenue ? $cashCow->name . ' (' . number_format($cashCow->revenue) . ' ' . __('ui.currency') . ')' : '-'">
            <!-- mdi:cash-multiple -->
            <!-- Icon from Material Design Icons by Pictogrammers - https://github.com/Templarian/MaterialDesign/blob/master/LICENSE -->
            <path fill="currentColor" d="M12 3a9 9 0 0 0 0 18a9 9 0 0 0 0-18m0 16c-3.86 0-7-3.14-7-7s3.14-7 7-7s7 3.14 7 7s-3.14 7-7 7m-1.5-3.5v2h3v-2h1.5a1.5 1.5 0 0 0 1.5-1.5v-3a1.5 1.5 0 0 0-1.5-1.5h-4.5v-2h6V7.5h-3v-2h-3v2H8.5A1.5 1.5 0 0 0 7 9v3a1.5 1.5 0 0 0 1.5 1.5h4.5v2h-6V17h3.5z" />
        </x-dashboard.cards.overview>

        <x-dashboard.cards.overview
            style="col"
            :index="8"
            title="القسم الأعلى طلباً"
            :count="$trendingCategory && $trendingCategory->total_orders > 0 ? $trendingCategory->name . ' (' . number_format($trendingCategory->total_orders) . ' طلب)' : '-'">
            <!-- heroicons:arrow-trending-up-solid -->
            <!-- Icon from HeroIcons by Refactoring UI Inc - https://github.com/tailwindlabs/heroicons/blob/master/LICENSE -->
            <path fill="currentColor" fill-rule="evenodd" d="M2.22 10.22a.75.75 0 0 1 1.06 0l4.25 4.25l3.47-3.47a.75.75 0 0 1 1.06 0l5.83 5.83A.75.75 0 0 1 16.83 17.9L11.53 12.6L8.06 16.03a.75.75 0 0 1-1.06 0L2.22 11.28a.75.75 0 0 1 0-1.06Zm12.03-3.97a.75.75 0 0 1 .75-.75h3.5a.75.75 0 0 1 .75.75v3.5a.75.75 0 0 1-1.5 0V7.56l-5.47 5.47a.75.75 0 0 1-1.06 0L8.06 9.56L3.28 14.34a.75.75 0 0 1-1.06-1.06l5.3-5.3a.75.75 0 0 1 1.06 0l3.16 3.16l4.94-4.94h-1.63a.75.75 0 0 1-.75-.75Z" clip-rule="evenodd"/>
        </x-dashboard.cards.overview>
    </div>

    <livewire:dashboard.categories-table />

</x-layouts.dashboard>
