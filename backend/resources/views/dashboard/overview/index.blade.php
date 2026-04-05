<x-layouts.dashboard page="overview">

    <div class="flex flex-col gap-5">
        <p class="text-gray-500 dark:text-gray-400">{{ __('ui.data_updates_houlry') }}</p>

        {{-- ── Row 0: Alert Cards (only show if there are alerts) ── --}}
        @if ($pendingProvidersCount > 0 || $expiringSubscriptionsCount > 0 || $unreadContactsCount > 0 || $staleNewOrdersCount > 0)
        <div class="grid grid-cols-2 md:grid-cols-4 gap-5">
            @if ($pendingProvidersCount > 0)
            <x-dashboard.cards.overview
                style="col"
                :index="2"
                :title="__('ui.pending_providers')"
                :count="$pendingProvidersCount"
                :authorize="Illuminate\Support\Facades\Gate::allows('view users')"
                :link="route('dashboard.users.service_providers', ['statusFilter' => App\Models\User::PENDING_STATUS])">
                <path fill="currentColor" d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2m1 15h-2v-2h2zm0-4h-2V7h2z" />
            </x-dashboard.cards.overview>
            @endif

            @if ($expiringSubscriptionsCount > 0)
            <x-dashboard.cards.overview
                style="col"
                :index="7"
                :title="__('ui.expiring_subscriptions')"
                :count="$expiringSubscriptionsCount . ' (' . __('ui.within_7_days') . ')'"
                :authorize="Illuminate\Support\Facades\Gate::allows('manage subscriptions')"
                :link="route('dashboard.subscriptions.index')">
                <path fill="currentColor" d="M11.99 2C6.47 2 2 6.48 2 12s4.47 10 9.99 10C17.52 22 22 17.52 22 12S17.52 2 11.99 2M12 20c-4.42 0-8-3.58-8-8s3.58-8 8-8 8 3.58 8 8-3.58 8-8 8m.5-13H11v6l5.25 3.15.75-1.23-4.5-2.67z" />
            </x-dashboard.cards.overview>
            @endif

            @if ($unreadContactsCount > 0)
            <x-dashboard.cards.overview
                style="col"
                :index="9"
                :title="__('ui.unread_contacts')"
                :count="$unreadContactsCount"
                :authorize="Illuminate\Support\Facades\Gate::allows('manage contact requests')"
                :link="route('dashboard.contacts.index')">
                <path fill="currentColor" d="M20 4H4c-1.1 0-1.99.9-1.99 2L2 18c0 1.1.9 2 2 2h16c1.1 0 2-.9 2-2V6c0-1.1-.9-2-2-2m0 4-8 5-8-5V6l8 5 8-5z" />
            </x-dashboard.cards.overview>
            @endif

            @if ($staleNewOrdersCount > 0)
            <x-dashboard.cards.overview
                style="col"
                :index="1"
                :title="__('ui.stale_orders')"
                :count="$staleNewOrdersCount . ' (' . __('ui.more_than_hour') . ')'"
                :authorize="Illuminate\Support\Facades\Gate::allows('manage orders')"
                :link="route('dashboard.orders.index', ['statusFilter' => App\Models\Order::NEW_STATUS])">
                <path fill="currentColor" d="M1 21h22L12 2zm12-3h-2v-2h2zm0-4h-2v-4h2z" />
            </x-dashboard.cards.overview>
            @endif
        </div>
        @endif

        {{-- ── Row 1: Core Counts ── --}}
        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-5 gap-5">
            <x-dashboard.cards.overview
                style="col"
                :index="1"
                :title="__('ui.customers')"
                :count="$usersCount"
                :authorize="Illuminate\Support\Facades\Gate::allows('view users')"
                :link="route('dashboard.users.index')">
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
                <path fill="currentColor" d="M21.763 11.382a7.57 7.57 0 0 1-3.47 4.693a7.56 7.56 0 0 1-5.772.827a1.27 1.27 0 0 0-.67 0a1.2 1.2 0 0 0-.57.31l-4.266 4.29a1.9 1.9 0 0 1-.56.37a1.7 1.7 0 0 1-.669.13a1.65 1.65 0 0 1-.659-.13a1.8 1.8 0 0 1-.56-.37L2.5 19.432a1.6 1.6 0 0 1-.37-.56a1.77 1.77 0 0 1 0-1.33a1.6 1.6 0 0 1 .37-.56l4.277-4.28a1.17 1.17 0 0 0 .32-.56c.06-.209.06-.43 0-.64a7.59 7.59 0 0 1 2.117-7.42a7.5 7.5 0 0 1 3.497-1.88a7.43 7.43 0 0 1 3.997.15a.74.74 0 0 1 .31 1.24L14.1 6.522a2.13 2.13 0 0 0-.56 1.41a.9.9 0 0 0 .21.63l1.719 1.73a1.1 1.1 0 0 0 .91.18a2.13 2.13 0 0 0 1.138-.53l2.918-2.93a.78.78 0 0 1 .71-.2a.75.75 0 0 1 .539.51a7.6 7.6 0 0 1 .08 4.06" />
            </x-dashboard.cards.overview>

            <x-dashboard.cards.overview
                style="col"
                :index="3"
                :title="__('ui.active_subscriptions')"
                :count="$activeSubscriptionsCount"
                :authorize="Illuminate\Support\Facades\Gate::allows('manage subscriptions')"
                :link="route('dashboard.subscriptions.index', ['statusFilter' => App\Models\Subscription::ACTIVE_STATUS])">
                <path fill="currentColor" d="M12.005 22.003c-5.523 0-10-4.477-10-10s4.477-10 10-10s10 4.477 10 10s-4.477 10-10 10m-3.5-8v2h2.5v2h2v-2h1a2.5 2.5 0 1 0 0-5h-4a.5.5 0 1 1 0-1h5.5v-2h-2.5v-2h-2v2h-1a2.5 2.5 0 1 0 0 5h4a.5.5 0 0 1 0 1z" />
            </x-dashboard.cards.overview>

            <x-dashboard.cards.overview
                style="col"
                :index="4"
                :title="__('ui.inactive_subscriptions')"
                :count="$inactiveSubscriptionsCount"
                :authorize="Illuminate\Support\Facades\Gate::allows('manage subscriptions')"
                :link="route('dashboard.subscriptions.index', ['statusFilter' => App\Models\Subscription::INACTIVE_STATUS])">
                <path fill="currentColor" d="M12.005 22.003c-5.523 0-10-4.477-10-10s4.477-10 10-10s10 4.477 10 10s-4.477 10-10 10m-3.5-8v2h2.5v2h2v-2h1a2.5 2.5 0 1 0 0-5h-4a.5.5 0 1 1 0-1h5.5v-2h-2.5v-2h-2v2h-1a2.5 2.5 0 1 0 0 5h4a.5.5 0 0 1 0 1z" />
            </x-dashboard.cards.overview>

            <x-dashboard.cards.overview
                style="col"
                :index="5"
                :title="__('ui.payout_requests')"
                :count="$payoutRequestsCount"
                :authorize="Illuminate\Support\Facades\Gate::allows('view users')"
                :link="route('dashboard.users.payout_requests')">
                <path fill="currentColor" d="M4 11.5v4c0 .83.67 1.5 1.5 1.5S7 16.33 7 15.5v-4c0-.83-.67-1.5-1.5-1.5S4 10.67 4 11.5m6 0v4c0 .83.67 1.5 1.5 1.5s1.5-.67 1.5-1.5v-4c0-.83-.67-1.5-1.5-1.5s-1.5.67-1.5 1.5M3.5 22h16c.83 0 1.5-.67 1.5-1.5s-.67-1.5-1.5-1.5h-16c-.83 0-1.5.67-1.5 1.5S2.67 22 3.5 22M16 11.5v4c0 .83.67 1.5 1.5 1.5s1.5-.67 1.5-1.5v-4c0-.83-.67-1.5-1.5-1.5s-1.5.67-1.5 1.5M10.57 1.49l-7.9 4.16c-.41.21-.67.64-.67 1.1C2 7.44 2.56 8 3.25 8h16.51C20.44 8 21 7.44 21 6.75c0-.46-.26-.89-.67-1.1l-7.9-4.16c-.58-.31-1.28-.31-1.86 0" />
            </x-dashboard.cards.overview>

            <x-dashboard.cards.overview
                style="col"
                :index="6"
                :title="__('ui.new_orders')"
                :count="$newOrdersCount"
                :authorize="Illuminate\Support\Facades\Gate::allows('manage orders')"
                :link="route('dashboard.orders.index', ['statusFilter' => App\Models\Order::NEW_STATUS])">
                <path fill="currentColor" d="M18 16h-2v-1H8v1H6v-1H2v5h20v-5h-4zm-1-8V4H7v4H2v6h4v-2h2v2h8v-2h2v2h4V8zM9 6h6v2H9z" />
            </x-dashboard.cards.overview>

            <x-dashboard.cards.overview
                style="col"
                :index="7"
                :title="__('ui.on_progress_orders')"
                :count="$onProgressOrdersCount"
                :authorize="Illuminate\Support\Facades\Gate::allows('manage orders')"
                :link="route('dashboard.orders.index', ['statusFilter' => App\Models\Order::STARTED_STATUS])">
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
                :index="3"
                :title="__('ui.avg_order_value')"
                :count="$avgOrderValue . ' ' . __('ui.currency')"
                :authorize="true"
                :link="null">
                <path fill="currentColor" d="M19 14V6c0-1.1-.9-2-2-2H3c-1.1 0-2 .9-2 2v8c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2m-2 0H3V6h14zm-7-7c-1.66 0-3 1.34-3 3s1.34 3 3 3 3-1.34 3-3-1.34-3-3-3m13 0v11c0 1.1-.9 2-2 2H4v-2h17V7z" />
            </x-dashboard.cards.overview>

            <x-dashboard.cards.overview
                style="col"
                :index="6"
                :title="__('ui.coupons_used')"
                :count="$couponsUsedCount"
                :authorize="Illuminate\Support\Facades\Gate::allows('manage coupons')"
                :link="route('dashboard.coupons.index')">
                <path fill="currentColor" d="M21.41 11.58l-9-9C12.05 2.22 11.55 2 11 2H4c-1.1 0-2 .9-2 2v7c0 .55.22 1.05.59 1.42l9 9c.36.36.86.58 1.41.58s1.05-.22 1.41-.59l7-7c.37-.36.59-.86.59-1.41s-.23-1.06-.59-1.42M5.5 7C4.67 7 4 6.33 4 5.5S4.67 4 5.5 4S7 4.67 7 5.5S6.33 7 5.5 7" />
            </x-dashboard.cards.overview>

            <x-dashboard.cards.overview
                style="col"
                :index="9"
                :title="__('ui.total_discounts')"
                :count="$totalDiscountGiven . ' ' . __('ui.currency')"
                :authorize="true"
                :link="null">
                <path fill="currentColor" d="M12.79 21L3 11.21v2c0 .53.21 1.04.59 1.41l7.79 7.79c.78.78 2.05.78 2.83 0l6.21-6.21c.78-.78.78-2.05 0-2.83z" />
                <path fill="currentColor" d="M11.38 17.41c.78.78 2.05.78 2.83 0l6.21-6.21c.78-.78.78-2.05 0-2.83L12.63.58C12.25.21 11.74 0 11.21 0H5C3.9 0 3 .9 3 2v6.21c0 .53.21 1.04.59 1.41zM7.25 3a1.25 1.25 0 1 1 0 2.5a1.25 1.25 0 0 1 0-2.5" />
            </x-dashboard.cards.overview>
        </div>

        <div class="grid grid-cols-3 gap-5">
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

        {{-- ── Row 4: Data Tables ── --}}
        @can('manage orders')
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-5">

            {{-- Latest 5 Orders --}}
            <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03]">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-semibold text-gray-800 dark:text-white">{{ __('ui.latest_orders') }}</h3>
                    <a href="{{ route('dashboard.orders.index') }}" class="text-sm text-brand-500 hover:text-brand-600">{{ __('ui.view_all') }} →</a>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="border-b border-gray-200 dark:border-gray-700">
                                <th class="py-2 px-3 text-start font-medium text-gray-500">#</th>
                                <th class="py-2 px-3 text-start font-medium text-gray-500">{{ __('ui.customer') }}</th>
                                <th class="py-2 px-3 text-start font-medium text-gray-500">{{ __('ui.service') }}</th>
                                <th class="py-2 px-3 text-start font-medium text-gray-500">{{ __('ui.status') }}</th>
                                <th class="py-2 px-3 text-start font-medium text-gray-500">{{ __('ui.total') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                            @forelse ($latestOrders as $order)
                            <tr>
                                <td class="py-2 px-3 text-gray-700 dark:text-gray-300 font-mono">{{ $order->id }}</td>
                                <td class="py-2 px-3 text-gray-700 dark:text-gray-300">{{ $order->customer?->name ?? '-' }}</td>
                                <td class="py-2 px-3 text-gray-700 dark:text-gray-300">{{ $order->service?->name ?? '-' }}</td>
                                <td class="py-2 px-3">
                                    @switch($order->status)
                                        @case(App\Models\Order::NEW_STATUS)
                                            <x-dashboard.badges.primary :name="__('ui.' . $order->status)" />
                                            @break
                                        @case(App\Models\Order::COMPLETED_STATUS)
                                            <x-dashboard.badges.success :name="__('ui.' . $order->status)" />
                                            @break
                                        @default
                                            <x-dashboard.badges.warning :name="__('ui.' . $order->status)" />
                                    @endswitch
                                </td>
                                <td class="py-2 px-3 font-semibold text-gray-700 dark:text-gray-300">{{ number_format($order->subtotal, config('app.decimal_places')) }} {{ __('ui.currency') }}</td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="5" class="py-4 text-center text-gray-400">{{ __('ui.no_results') }}</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- Top 5 Services --}}
            <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03]">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-semibold text-gray-800 dark:text-white">{{ __('ui.top_services') }}</h3>
                    <a href="{{ route('dashboard.services.index') }}" class="text-sm text-brand-500 hover:text-brand-600">{{ __('ui.view_all') }} →</a>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="border-b border-gray-200 dark:border-gray-700">
                                <th class="py-2 px-3 text-start font-medium text-gray-500">#</th>
                                <th class="py-2 px-3 text-start font-medium text-gray-500">{{ __('ui.service_name') }}</th>
                                <th class="py-2 px-3 text-start font-medium text-gray-500">{{ __('ui.order_count') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                            @forelse ($topServices as $index => $service)
                            <tr>
                                <td class="py-2 px-3 text-gray-400 font-mono">{{ $index + 1 }}</td>
                                <td class="py-2 px-3 text-gray-700 dark:text-gray-300">{{ $service->name }}</td>
                                <td class="py-2 px-3">
                                    <span class="inline-flex items-center rounded-full bg-brand-50 px-2.5 py-0.5 text-xs font-medium text-brand-700 dark:bg-brand-500/10 dark:text-brand-400">
                                        {{ number_format($service->orders_count) }}
                                    </span>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="3" class="py-4 text-center text-gray-400">{{ __('ui.no_results') }}</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- Top 5 Providers --}}
        <div x-data="{ showProvider: false, provider: null }" class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03]">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold text-gray-800 dark:text-white">{{ __('ui.top_providers') }}</h3>
                <a href="{{ route('dashboard.users.service_providers') }}" class="text-sm text-brand-500 hover:text-brand-600">{{ __('ui.view_all') }} →</a>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-gray-200 dark:border-gray-700">
                            <th class="py-2 px-3 text-start font-medium text-gray-500">#</th>
                            <th class="py-2 px-3 text-start font-medium text-gray-500">{{ __('ui.provider_name') }}</th>
                            <th class="py-2 px-3 text-start font-medium text-gray-500">{{ __('ui.categories') }}</th>
                            <th class="py-2 px-3 text-start font-medium text-gray-500">{{ __('ui.order_count') }}</th>
                            <th class="py-2 px-3 text-start font-medium text-gray-500">{{ __('ui.revenue') }}</th>
                            <th class="py-2 px-3 text-start font-medium text-gray-500">{{ __('ui.details') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                        @forelse ($topProviders as $index => $provider)
                        <tr>
                            <td class="py-2 px-3 text-gray-400 font-mono">{{ $index + 1 }}</td>
                            <td class="py-2 px-3 text-gray-700 dark:text-gray-300">{{ $provider->name }}</td>
                            <td class="py-2 px-3">
                                <div class="flex flex-wrap gap-1">
                                    @forelse ($provider->categories as $cat)
                                        <span class="inline-flex items-center rounded-full bg-green-50 px-2 py-0.5 text-xs font-medium text-green-700 dark:bg-green-500/10 dark:text-green-400">{{ $cat->name }}</span>
                                    @empty
                                        <span class="text-gray-400 text-xs">-</span>
                                    @endforelse
                                </div>
                            </td>
                            <td class="py-2 px-3">
                                <span class="inline-flex items-center rounded-full bg-brand-50 px-2.5 py-0.5 text-xs font-medium text-brand-700 dark:bg-brand-500/10 dark:text-brand-400">
                                    {{ number_format($provider->service_provider_orders_count) }}
                                </span>
                            </td>
                            <td class="py-2 px-3 font-semibold text-green-600">{{ number_format($provider->revenue ?? 0, config('app.decimal_places')) }} {{ __('ui.currency') }}</td>
                            <td class="py-2 px-3">
                                <button
                                    @click="provider = {
                                        name: '{{ addslashes($provider->name) }}',
                                        phone: '{{ $provider->phone }}',
                                        entity_type: '{{ __('ui.' . $provider->entity_type) }}',
                                        status: '{{ __('ui.' . $provider->status) }}',
                                        status_raw: '{{ $provider->status }}',
                                        city: '{{ $provider->city?->name ?? __('ui.none') }}',
                                        balance: '{{ number_format($provider->balance ?? 0, config('app.decimal_places')) }} {{ __('ui.currency') }}',
                                        orders_count: '{{ number_format($provider->service_provider_orders_count) }}',
                                        revenue: '{{ number_format($provider->revenue ?? 0, config('app.decimal_places')) }} {{ __('ui.currency') }}',
                                        categories: '{{ $provider->categories->pluck('name')->join('، ') ?: __('ui.none') }}',
                                        avatar: '{{ $provider->getAvatarUrl('sm') }}'
                                    }; showProvider = true"
                                    class="inline-flex items-center gap-1 text-brand-500 hover:text-brand-600 transition-colors cursor-pointer"
                                    title="{{ __('ui.show') }}"
                                >
                                    {{-- ic:round-remove-red-eye --}}
                                    <svg class="w-5 h-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">
                                        <path fill="currentColor" d="M12 4.5C7 4.5 2.73 7.61 1 12c1.73 4.39 6 7.5 11 7.5s9.27-3.11 11-7.5c-1.73-4.39-6-7.5-11-7.5M12 17c-2.76 0-5-2.24-5-5s2.24-5 5-5s5 2.24 5 5s-2.24 5-5 5m0-8c-1.66 0-3 1.34-3 3s1.34 3 3 3s3-1.34 3-3s-1.34-3-3-3"/>
                                    </svg>
                                </button>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="py-4 text-center text-gray-400">{{ __('ui.no_results') }}</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- Provider Details Modal --}}
            <div x-show="showProvider" x-cloak class="fixed inset-0 z-99999 flex items-center justify-center p-5 overflow-y-auto" style="display:none">
                {{-- Backdrop --}}
                <div @click="showProvider = false" class="fixed inset-0 h-full w-full bg-gray-400/50 backdrop-blur-[10px]" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"></div>

                {{-- Dialog --}}
                <div x-show="showProvider" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100" x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-95" class="relative flex w-full max-w-[550px] flex-col rounded-3xl bg-white p-6 dark:bg-gray-900 lg:p-8 max-h-[calc(100dvh-3rem)]">
                    {{-- Close button --}}
                    <button @click="showProvider = false" class="transition-color absolute end-5 top-5 z-999 flex h-11 w-11 items-center justify-center rounded-full bg-gray-100 text-gray-400 hover:bg-gray-200 hover:text-gray-600 dark:bg-gray-700 dark:bg-white/[0.05] dark:text-gray-400 dark:hover:bg-white/[0.07] dark:hover:text-gray-300">
                        <svg class="fill-current" width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path fill-rule="evenodd" clip-rule="evenodd" d="M6.04289 16.5418C5.65237 16.9323 5.65237 17.5655 6.04289 17.956C6.43342 18.3465 7.06658 18.3465 7.45711 17.956L11.9987 13.4144L16.5408 17.9565C16.9313 18.347 17.5645 18.347 17.955 17.9565C18.3455 17.566 18.3455 16.9328 17.955 16.5423L13.4129 12.0002L17.955 7.45808C18.3455 7.06756 18.3455 6.43439 17.955 6.04387C17.5645 5.65335 16.9313 5.65335 16.5408 6.04387L11.9987 10.586L7.45711 6.04439C7.06658 5.65386 6.43342 5.65386 6.04289 6.04439C5.65237 6.43491 5.65237 7.06808 6.04289 7.4586L10.5845 12.0002L6.04289 16.5418Z" />
                        </svg>
                    </button>

                    {{-- Modal Content --}}
                    <div class="overflow-y-auto custom-scrollbar">
                        <h4 class="text-lg font-semibold text-gray-800 dark:text-white mb-6">{{ __('ui.provider_details') }}</h4>

                        <template x-if="provider">
                            <div class="space-y-5">
                                {{-- Avatar + Name --}}
                                <div class="flex items-center gap-4">
                                    <img :src="provider.avatar" class="w-16 h-16 rounded-full object-cover border-2 border-gray-200 dark:border-gray-700" :alt="provider.name">
                                    <div>
                                        <p class="text-base font-semibold text-gray-800 dark:text-white" x-text="provider.name"></p>
                                        <p class="text-sm text-gray-500 dark:text-gray-400" x-text="provider.entity_type"></p>
                                    </div>
                                </div>

                                <hr class="border-gray-200 dark:border-gray-700">

                                {{-- Info Grid --}}
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                    {{-- Phone --}}
                                    <div>
                                        <p class="mb-1 text-xs text-gray-500 dark:text-gray-400">{{ __('validation.attributes.phone') }}</p>
                                        <a :href="'tel:' + provider.phone" class="text-sm font-medium text-brand-500 hover:text-brand-600 dark:text-brand-400" x-text="provider.phone" dir="ltr"></a>
                                    </div>

                                    {{-- City --}}
                                    <div>
                                        <p class="mb-1 text-xs text-gray-500 dark:text-gray-400">{{ __('ui.city') }}</p>
                                        <p class="text-sm font-medium text-gray-800 dark:text-white/90" x-text="provider.city"></p>
                                    </div>

                                    {{-- Status --}}
                                    <div>
                                        <p class="mb-1 text-xs text-gray-500 dark:text-gray-400">{{ __('ui.status') }}</p>
                                        <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium"
                                              :class="{
                                                  'bg-green-50 text-green-700 dark:bg-green-500/10 dark:text-green-400': provider.status_raw === 'active',
                                                  'bg-yellow-50 text-yellow-700 dark:bg-yellow-500/10 dark:text-yellow-400': provider.status_raw === 'pending',
                                                  'bg-gray-100 text-gray-600 dark:bg-gray-500/10 dark:text-gray-400': provider.status_raw === 'inactive'
                                              }"
                                              x-text="provider.status"></span>
                                    </div>

                                    {{-- Balance --}}
                                    <div>
                                        <p class="mb-1 text-xs text-gray-500 dark:text-gray-400">{{ __('ui.balance') }}</p>
                                        <p class="text-sm font-medium text-gray-800 dark:text-white/90" x-text="provider.balance"></p>
                                    </div>

                                    {{-- Orders --}}
                                    <div>
                                        <p class="mb-1 text-xs text-gray-500 dark:text-gray-400">{{ __('ui.orders') }}</p>
                                        <span class="inline-flex items-center rounded-full bg-brand-50 px-2.5 py-0.5 text-xs font-medium text-brand-700 dark:bg-brand-500/10 dark:text-brand-400" x-text="provider.orders_count"></span>
                                    </div>

                                    {{-- Revenue --}}
                                    <div>
                                        <p class="mb-1 text-xs text-gray-500 dark:text-gray-400">{{ __('ui.revenue') }}</p>
                                        <p class="text-sm font-semibold text-green-600" x-text="provider.revenue"></p>
                                    </div>

                                    {{-- Categories --}}
                                    <div class="sm:col-span-2">
                                        <p class="mb-1 text-xs text-gray-500 dark:text-gray-400">{{ __('ui.categories') }}</p>
                                        <p class="text-sm font-medium text-gray-800 dark:text-white/90" x-text="provider.categories"></p>
                                    </div>
                                </div>
                            </div>
                        </template>
                    </div>
                </div>
            </div>
        </div>
        @endcan

    </div>
</x-layouts.dashboard>
