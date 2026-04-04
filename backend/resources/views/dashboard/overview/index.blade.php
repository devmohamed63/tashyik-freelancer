<x-layouts.dashboard page="overview">

    <div class="flex flex-col gap-5">
        <p class="text-gray-500 dark:text-gray-400">{{ __('ui.data_updates_houlry') }}</p>

        {{-- ── Row 1: Core Counts ── --}}
        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-5 gap-5">
            <x-dashboard.cards.overview
                style="col"
                :index="1"
                :title="__('ui.customers')"
                :count="$usersCount"
                :authorize="Illuminate\Support\Facades\Gate::allows('view users')"
                :link="route('dashboard.users.index')">
                <!-- mage:users-fill -->
                <!-- Icon from Mage Icons by MageIcons - https://github.com/Mage-Icons/mage-icons/blob/main/License.txt -->
                <path fill="currentColor" d="M21.987 18.73a2 2 0 0 1-.34.85a1.9 1.9 0 0 1-1.56.8h-1.651a.74.74 0 0 1-.6-.31a.76.76 0 0 1-.11-.67c.37-1.18.29-2.51-3.061-4.64a.77.77 0 0 1-.32-.85a.76.76 0 0 1 .72-.54a7.61 7.61 0 0 1 6.792 4.39a2 2 0 0 1 .13.97M19.486 7.7a4.43 4.43 0 0 1-4.421 4.42a.76.76 0 0 1-.65-1.13a6.16 6.16 0 0 0 0-6.53a.75.75 0 0 1 .61-1.18a4.3 4.3 0 0 1 3.13 1.34a4.46 4.46 0 0 1 1.291 3.12z" />
                <path fill="currentColor" d="M16.675 18.7a2.65 2.65 0 0 1-1.26 2.48c-.418.257-.9.392-1.39.39H4.652a2.63 2.63 0 0 1-1.39-.39A2.62 2.62 0 0 1 2.01 18.7a2.6 2.6 0 0 1 .5-1.35a8.8 8.8 0 0 1 6.812-3.51a8.78 8.78 0 0 1 6.842 3.5a2.7 2.7 0 0 1 .51 1.36M14.245 7.32a4.92 4.92 0 0 1-4.902 4.91a4.903 4.903 0 0 1-4.797-5.858a4.9 4.9 0 0 1 6.678-3.57a4.9 4.9 0 0 1 3.03 4.518z" />
            </x-dashboard.cards.overview>

            <x-dashboard.cards.overview
                style="col"
                :index="2"
                :title="__('ui.service_providers')"
                :count="$serviceProvidersCount"
                :authorize="Illuminate\Support\Facades\Gate::allows('view users')"
                :link="route('dashboard.users.service_providers')">
                <!-- mage:wrench-fill -->
                <!-- Icon from Mage Icons by MageIcons - https://github.com/Mage-Icons/mage-icons/blob/main/License.txt -->
                <path fill="currentColor" d="M21.763 11.382a7.57 7.57 0 0 1-3.47 4.693a7.56 7.56 0 0 1-5.772.827a1.27 1.27 0 0 0-.67 0a1.2 1.2 0 0 0-.57.31l-4.266 4.29a1.9 1.9 0 0 1-.56.37a1.7 1.7 0 0 1-.669.13a1.65 1.65 0 0 1-.659-.13a1.8 1.8 0 0 1-.56-.37L2.5 19.432a1.6 1.6 0 0 1-.37-.56a1.77 1.77 0 0 1 0-1.33a1.6 1.6 0 0 1 .37-.56l4.277-4.28a1.17 1.17 0 0 0 .32-.56c.06-.209.06-.43 0-.64a7.59 7.59 0 0 1 2.117-7.42a7.5 7.5 0 0 1 3.497-1.88a7.43 7.43 0 0 1 3.997.15a.74.74 0 0 1 .31 1.24L14.1 6.522a2.13 2.13 0 0 0-.56 1.41a.9.9 0 0 0 .21.63l1.719 1.73a1.1 1.1 0 0 0 .91.18a2.13 2.13 0 0 0 1.138-.53l2.918-2.93a.78.78 0 0 1 .71-.2a.75.75 0 0 1 .539.51a7.6 7.6 0 0 1 .08 4.06" />
            </x-dashboard.cards.overview>

            <x-dashboard.cards.overview
                style="col"
                :index="3"
                :title="__('ui.active_subscriptions')"
                :count="$activeSubscriptionsCount"
                :authorize="Illuminate\Support\Facades\Gate::allows('manage subscriptions')"
                :link="route('dashboard.subscriptions.index', ['statusFilter' => App\Models\Subscription::ACTIVE_STATUS])">
                <!-- ri:money-dollar-circle-fill -->
                <!-- Icon from Remix Icon by Remix Design - https://github.com/Remix-Design/RemixIcon/blob/master/License -->
                <path fill="currentColor" d="M12.005 22.003c-5.523 0-10-4.477-10-10s4.477-10 10-10s10 4.477 10 10s-4.477 10-10 10m-3.5-8v2h2.5v2h2v-2h1a2.5 2.5 0 1 0 0-5h-4a.5.5 0 1 1 0-1h5.5v-2h-2.5v-2h-2v2h-1a2.5 2.5 0 1 0 0 5h4a.5.5 0 0 1 0 1z" />
            </x-dashboard.cards.overview>

            <x-dashboard.cards.overview
                style="col"
                :index="4"
                :title="__('ui.inactive_subscriptions')"
                :count="$inactiveSubscriptionsCount"
                :authorize="Illuminate\Support\Facades\Gate::allows('manage subscriptions')"
                :link="route('dashboard.subscriptions.index', ['statusFilter' => App\Models\Subscription::INACTIVE_STATUS])">
                <!-- ri:money-dollar-circle-fill -->
                <path fill="currentColor" d="M12.005 22.003c-5.523 0-10-4.477-10-10s4.477-10 10-10s10 4.477 10 10s-4.477 10-10 10m-3.5-8v2h2.5v2h2v-2h1a2.5 2.5 0 1 0 0-5h-4a.5.5 0 1 1 0-1h5.5v-2h-2.5v-2h-2v2h-1a2.5 2.5 0 1 0 0 5h4a.5.5 0 0 1 0 1z" />
            </x-dashboard.cards.overview>

            <x-dashboard.cards.overview
                style="col"
                :index="5"
                :title="__('ui.payout_requests')"
                :count="$payoutRequestsCount"
                :authorize="Illuminate\Support\Facades\Gate::allows('view users')"
                :link="route('dashboard.users.payout_requests')">
                <!-- ic:round-account-balance -->
                <path fill="currentColor" d="M4 11.5v4c0 .83.67 1.5 1.5 1.5S7 16.33 7 15.5v-4c0-.83-.67-1.5-1.5-1.5S4 10.67 4 11.5m6 0v4c0 .83.67 1.5 1.5 1.5s1.5-.67 1.5-1.5v-4c0-.83-.67-1.5-1.5-1.5s-1.5.67-1.5 1.5M3.5 22h16c.83 0 1.5-.67 1.5-1.5s-.67-1.5-1.5-1.5h-16c-.83 0-1.5.67-1.5 1.5S2.67 22 3.5 22M16 11.5v4c0 .83.67 1.5 1.5 1.5s1.5-.67 1.5-1.5v-4c0-.83-.67-1.5-1.5-1.5s-1.5.67-1.5 1.5M10.57 1.49l-7.9 4.16c-.41.21-.67.64-.67 1.1C2 7.44 2.56 8 3.25 8h16.51C20.44 8 21 7.44 21 6.75c0-.46-.26-.89-.67-1.1l-7.9-4.16c-.58-.31-1.28-.31-1.86 0" />
            </x-dashboard.cards.overview>

            <x-dashboard.cards.overview
                style="col"
                :index="6"
                :title="__('ui.new_orders')"
                :count="$newOrdersCount"
                :authorize="Illuminate\Support\Facades\Gate::allows('manage orders')"
                :link="route('dashboard.orders.index', ['statusFilter' => App\Models\Order::NEW_STATUS])">
                <!-- ic:sharp-home-repair-service -->
                <path fill="currentColor" d="M18 16h-2v-1H8v1H6v-1H2v5h20v-5h-4zm-1-8V4H7v4H2v6h4v-2h2v2h8v-2h2v2h4V8zM9 6h6v2H9z" />
            </x-dashboard.cards.overview>

            <x-dashboard.cards.overview
                style="col"
                :index="7"
                :title="__('ui.on_progress_orders')"
                :count="$onProgressOrdersCount"
                :authorize="Illuminate\Support\Facades\Gate::allows('manage orders')"
                :link="route('dashboard.orders.index', ['statusFilter' => App\Models\Order::STARTED_STATUS])">
                <!-- streamline-ultimate:house-4-bold -->
                <path fill="currentColor" d="m2.64 10.59l9-7.48a.48.48 0 0 1 .62 0l9 7.48a1.45 1.45 0 0 0 2.06-.22a1.52 1.52 0 0 0-.21-2.11l-9-7.49a3.41 3.41 0 0 0-4.32 0l-9 7.49a1.52 1.52 0 0 0-.21 2.11a1.45 1.45 0 0 0 2.06.22" />
                <path fill="currentColor" d="M22.28 22v-7a2.22 2.22 0 0 0-.73-1.59l-8.3-7a1.94 1.94 0 0 0-2.5 0l-8.3 7A2.22 2.22 0 0 0 1.72 15v7a2 2 0 0 0 2 2h5.34a1 1 0 0 0 1-1v-5.5a1 1 0 0 1 1-1h2a1 1 0 0 1 1 1V23a1 1 0 0 0 1 1h5.38a2 2 0 0 0 1.84-2" />
            </x-dashboard.cards.overview>

            <x-dashboard.cards.overview
                style="col"
                :index="8"
                :title="__('ui.completed_orders')"
                :count="$completedOrdersCount"
                :authorize="Illuminate\Support\Facades\Gate::allows('manage orders')"
                :link="route('dashboard.orders.index', ['statusFilter' => App\Models\Order::COMPLETED_STATUS])">
                <!-- material-symbols:star-shine-rounded -->
                <path fill="currentColor" d="M19 15q.3-.3.713-.3t.712.3L22 16.6q.3.3.3.7t-.3.7t-.7.3t-.7-.3L19 16.425q-.3-.3-.3-.712T19 15m1-12q.3.3.3.713t-.3.712L18.425 6q-.3.3-.712.3T17 6t-.3-.712t.3-.713L18.6 3q.3-.3.7-.3t.7.3M4 3q.3-.3.713-.3t.712.3L7 4.6q.3.3.3.7T7 6t-.712.3t-.713-.3L4 4.425q-.3-.3-.3-.712T4 3m1 12q.3.3.3.713t-.3.712L3.425 18q-.3.3-.712.3T2 18t-.3-.712t.3-.713L3.6 15q.3-.3.7-.3t.7.3m7 2.275l-4.15 2.5q-.275.175-.575.15t-.525-.2t-.35-.437t-.05-.588l1.1-4.725L3.775 10.8q-.25-.225-.312-.513t.037-.562t.3-.45t.55-.225l4.85-.425l1.875-4.45q.125-.3.388-.45t.537-.15t.537.15t.388.45l1.875 4.45l4.85.425q.35.05.55.225t.3.45t.038.563t-.313.512l-3.675 3.175l1.1 4.725q.075.325-.05.588t-.35.437t-.525.2t-.575-.15z" />
            </x-dashboard.cards.overview>

            <x-dashboard.cards.overview
                style="col"
                :index="9"
                :title="__('ui.contact_requests')"
                :count="$contactRequestCount"
                viewBox="0 0 56 56"
                :authorize="Illuminate\Support\Facades\Gate::allows('manage contact requests')"
                :link="route('dashboard.contacts.index')">
                <!-- f7:envelope-fill -->
                <path fill="currentColor" d="M28.047 30.707c.984 0 1.875-.445 2.883-1.477L51.32 9.05c-.867-.843-2.484-1.241-4.804-1.241H8.78c-1.969 0-3.351.375-4.125 1.148l20.508 20.274c1.008 1.007 1.922 1.476 2.883 1.476M2.71 44.418l16.57-16.383L2.664 11.652c-.352.657-.54 1.782-.54 3.399v25.875c0 1.664.212 2.836.587 3.492m50.625-.023c.351-.68.54-1.829.54-3.47V15.052c0-1.57-.165-2.696-.517-3.328L36.812 28.035ZM9.484 48.19h37.734c1.97 0 3.329-.375 4.102-1.125L34.445 30.332l-1.57 1.57c-1.594 1.547-3.117 2.25-4.828 2.25s-3.235-.703-4.828-2.25l-1.57-1.57L4.796 47.043c.89.773 2.46 1.148 4.687 1.148" />
            </x-dashboard.cards.overview>
        </div>

        {{-- ── Row 2: Revenue KPIs ── --}}
        @can('manage orders')
        <div class="grid grid-cols-2 md:grid-cols-4 gap-5">
            <x-dashboard.cards.overview
                style="col"
                :index="3"
                title="إيرادات اليوم"
                :count="$revenueToday . ' ' . __('ui.currency')"
                :authorize="true"
                :link="route('dashboard.orders.index', ['statusFilter' => App\Models\Order::COMPLETED_STATUS])">
                <path fill="currentColor" d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10s10-4.48 10-10S17.52 2 12 2m1.41 16.09V20h-2.67v-1.93c-1.71-.36-3.16-1.46-3.27-3.4h1.96c.1 1.05.82 1.87 2.65 1.87c1.96 0 2.4-.98 2.4-1.59c0-.83-.44-1.61-2.67-2.14c-2.48-.6-4.18-1.62-4.18-3.67c0-1.72 1.39-2.84 3.11-3.21V4h2.67v1.95c1.86.45 2.79 1.86 2.85 3.39H14.3c-.05-1.11-.64-1.87-2.22-1.87c-1.5 0-2.4.68-2.4 1.64c0 .84.65 1.39 2.67 1.94s4.18 1.36 4.18 3.85c0 1.89-1.44 2.98-3.12 3.19" />
            </x-dashboard.cards.overview>

            <x-dashboard.cards.overview
                style="col"
                :index="6"
                title="إيرادات الأسبوع"
                :count="$revenueWeek . ' ' . __('ui.currency')"
                :authorize="true"
                :link="route('dashboard.orders.index', ['statusFilter' => App\Models\Order::COMPLETED_STATUS])">
                <path fill="currentColor" d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10s10-4.48 10-10S17.52 2 12 2m1.41 16.09V20h-2.67v-1.93c-1.71-.36-3.16-1.46-3.27-3.4h1.96c.1 1.05.82 1.87 2.65 1.87c1.96 0 2.4-.98 2.4-1.59c0-.83-.44-1.61-2.67-2.14c-2.48-.6-4.18-1.62-4.18-3.67c0-1.72 1.39-2.84 3.11-3.21V4h2.67v1.95c1.86.45 2.79 1.86 2.85 3.39H14.3c-.05-1.11-.64-1.87-2.22-1.87c-1.5 0-2.4.68-2.4 1.64c0 .84.65 1.39 2.67 1.94s4.18 1.36 4.18 3.85c0 1.89-1.44 2.98-3.12 3.19" />
            </x-dashboard.cards.overview>

            <x-dashboard.cards.overview
                style="col"
                :index="7"
                title="إيرادات الشهر"
                :count="$revenueMonth . ' ' . __('ui.currency')"
                :authorize="true"
                :link="route('dashboard.orders.index', ['statusFilter' => App\Models\Order::COMPLETED_STATUS])">
                <path fill="currentColor" d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10s10-4.48 10-10S17.52 2 12 2m1.41 16.09V20h-2.67v-1.93c-1.71-.36-3.16-1.46-3.27-3.4h1.96c.1 1.05.82 1.87 2.65 1.87c1.96 0 2.4-.98 2.4-1.59c0-.83-.44-1.61-2.67-2.14c-2.48-.6-4.18-1.62-4.18-3.67c0-1.72 1.39-2.84 3.11-3.21V4h2.67v1.95c1.86.45 2.79 1.86 2.85 3.39H14.3c-.05-1.11-.64-1.87-2.22-1.87c-1.5 0-2.4.68-2.4 1.64c0 .84.65 1.39 2.67 1.94s4.18 1.36 4.18 3.85c0 1.89-1.44 2.98-3.12 3.19" />
            </x-dashboard.cards.overview>

            <x-dashboard.cards.overview
                style="col"
                :index="8"
                title="إجمالي الإيرادات"
                :count="$revenueTotal . ' ' . __('ui.currency')"
                :authorize="true"
                :link="route('dashboard.orders.index', ['statusFilter' => App\Models\Order::COMPLETED_STATUS])">
                <path fill="currentColor" d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10s10-4.48 10-10S17.52 2 12 2m1.41 16.09V20h-2.67v-1.93c-1.71-.36-3.16-1.46-3.27-3.4h1.96c.1 1.05.82 1.87 2.65 1.87c1.96 0 2.4-.98 2.4-1.59c0-.83-.44-1.61-2.67-2.14c-2.48-.6-4.18-1.62-4.18-3.67c0-1.72 1.39-2.84 3.11-3.21V4h2.67v1.95c1.86.45 2.79 1.86 2.85 3.39H14.3c-.05-1.11-.64-1.87-2.22-1.87c-1.5 0-2.4.68-2.4 1.64c0 .84.65 1.39 2.67 1.94s4.18 1.36 4.18 3.85c0 1.89-1.44 2.98-3.12 3.19" />
            </x-dashboard.cards.overview>
        </div>
        @endcan

        {{-- ── Row 3: Tax, Payouts, and Orders by Time ── --}}
        @can('manage orders')
        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-5 gap-5">
            <x-dashboard.cards.overview
                style="col"
                :index="1"
                title="الضرائب المحصلة"
                :count="$taxCollected . ' ' . __('ui.currency')"
                :authorize="true"
                :link="null">
                <path fill="currentColor" d="M20 4H4c-1.11 0-1.99.89-1.99 2L2 18c0 1.11.89 2 2 2h16c1.11 0 2-.89 2-2V6c0-1.11-.89-2-2-2m0 14H4v-6h16zm0-10H4V6h16z" />
            </x-dashboard.cards.overview>

            <x-dashboard.cards.overview
                style="col"
                :index="4"
                title="أرصدة معلقة (فنيين)"
                :count="$pendingPayouts . ' ' . __('ui.currency')"
                :authorize="Illuminate\Support\Facades\Gate::allows('view users')"
                :link="route('dashboard.users.payout_requests')">
                <path fill="currentColor" d="M21 18v1c0 1.1-.9 2-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h14c1.1 0 2 .9 2 2v1h-9a2 2 0 0 0-2 2v8a2 2 0 0 0 2 2zm-9-2h10V8H12zm4-2.5c-.83 0-1.5-.67-1.5-1.5s.67-1.5 1.5-1.5s1.5.67 1.5 1.5s-.67 1.5-1.5 1.5" />
            </x-dashboard.cards.overview>

            <x-dashboard.cards.overview
                style="col"
                :index="6"
                title="طلبات اليوم"
                :count="$ordersToday"
                :authorize="true"
                :link="route('dashboard.orders.index')">
                <path fill="currentColor" d="M19 4h-1V2h-2v2H8V2H6v2H5c-1.11 0-1.99.9-1.99 2L3 20a2 2 0 0 0 2 2h14c1.1 0 2-.9 2-2V6c0-1.1-.9-2-2-2m0 16H5V10h14zm0-12H5V6h14zm-7 5h5v5h-5z" />
            </x-dashboard.cards.overview>

            <x-dashboard.cards.overview
                style="col"
                :index="7"
                title="طلبات الأسبوع"
                :count="$ordersWeek"
                :authorize="true"
                :link="route('dashboard.orders.index')">
                <path fill="currentColor" d="M19 4h-1V2h-2v2H8V2H6v2H5c-1.11 0-1.99.9-1.99 2L3 20a2 2 0 0 0 2 2h14c1.1 0 2-.9 2-2V6c0-1.1-.9-2-2-2m0 16H5V10h14zm0-12H5V6h14zm-7 5h5v5h-5z" />
            </x-dashboard.cards.overview>

            <x-dashboard.cards.overview
                style="col"
                :index="9"
                title="طلبات الشهر"
                :count="$ordersMonth"
                :authorize="true"
                :link="route('dashboard.orders.index')">
                <path fill="currentColor" d="M19 4h-1V2h-2v2H8V2H6v2H5c-1.11 0-1.99.9-1.99 2L3 20a2 2 0 0 0 2 2h14c1.1 0 2-.9 2-2V6c0-1.1-.9-2-2-2m0 16H5V10h14zm0-12H5V6h14zm-7 5h5v5h-5z" />
            </x-dashboard.cards.overview>
        </div>
        @endcan

    </div>
</x-layouts.dashboard>

