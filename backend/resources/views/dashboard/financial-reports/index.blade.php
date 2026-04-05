<x-layouts.dashboard page="financial_reports">

    <x-dashboard.breadcrumb :page="'التقارير المالية'" />

    {{-- ── Period Filter ── --}}
    <div class="flex items-center gap-3 mt-5 mb-5">
        @foreach ($periodLabels as $key => $label)
            <a href="{{ route('dashboard.financial-reports', ['period' => $key]) }}"
               class="px-4 py-2 rounded-lg text-sm font-medium transition-all
                      {{ $period === $key ? 'bg-brand-500 text-white shadow-md' : 'bg-gray-100 text-gray-600 hover:bg-gray-200 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700' }}">
                {{ $label }}
            </a>
        @endforeach
    </div>

    {{-- ── Summary Cards ── --}}
    <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-5 mb-5">
        <x-dashboard.cards.overview
            style="col"
            :index="3"
            title="إجمالي الإيرادات"
            :count="$totalRevenue . ' ' . __('ui.currency')"
            :authorize="true"
            :link="null">
            <path fill="currentColor" d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10s10-4.48 10-10S17.52 2 12 2m1.41 16.09V20h-2.67v-1.93c-1.71-.36-3.16-1.46-3.27-3.4h1.96c.1 1.05.82 1.87 2.65 1.87c1.96 0 2.4-.98 2.4-1.59c0-.83-.44-1.61-2.67-2.14c-2.48-.6-4.18-1.62-4.18-3.67c0-1.72 1.39-2.84 3.11-3.21V4h2.67v1.95c1.86.45 2.79 1.86 2.85 3.39H14.3c-.05-1.11-.64-1.87-2.22-1.87c-1.5 0-2.4.68-2.4 1.64c0 .84.65 1.39 2.67 1.94s4.18 1.36 4.18 3.85c0 1.89-1.44 2.98-3.12 3.19" />
        </x-dashboard.cards.overview>

        <x-dashboard.cards.overview
            style="col"
            :index="8"
            :title="__('ui.net_profit')"
            :count="$netProfit . ' ' . __('ui.currency')"
            :authorize="true"
            :link="null">
            <path fill="currentColor" d="M11.8 10.9c-2.27-.59-3-1.2-3-2.15c0-1.09 1.01-1.85 2.7-1.85c1.78 0 2.44.85 2.5 2.1h2.21c-.07-1.72-1.12-3.3-3.21-3.81V3h-3v2.16c-1.94.42-3.5 1.68-3.5 3.61c0 2.31 1.91 3.46 4.7 4.13c2.5.6 3 1.48 3 2.41c0 .69-.49 1.79-2.7 1.79c-2.06 0-2.87-.92-2.98-2.1h-2.2c.12 2.19 1.76 3.42 3.68 3.83V21h3v-2.15c1.95-.37 3.5-1.5 3.5-3.55c0-2.84-2.43-3.81-4.7-4.4" />
        </x-dashboard.cards.overview>

        <x-dashboard.cards.overview
            style="col"
            :index="1"
            title="إجمالي الضرائب"
            :count="$totalTax . ' ' . __('ui.currency')"
            :authorize="true"
            :link="null">
            <path fill="currentColor" d="M20 4H4c-1.11 0-1.99.89-1.99 2L2 18c0 1.11.89 2 2 2h16c1.11 0 2-.89 2-2V6c0-1.11-.89-2-2-2m0 14H4v-6h16zm0-10H4V6h16z" />
        </x-dashboard.cards.overview>

        <x-dashboard.cards.overview
            style="col"
            :index="2"
            title="إيرادات الاشتراكات"
            :count="$subscriptionRevenue . ' ' . __('ui.currency')"
            :authorize="true"
            :link="null">
            <path fill="currentColor" d="M12.005 22.003c-5.523 0-10-4.477-10-10s4.477-10 10-10s10 4.477 10 10s-4.477 10-10 10m-3.5-8v2h2.5v2h2v-2h1a2.5 2.5 0 1 0 0-5h-4a.5.5 0 1 1 0-1h5.5v-2h-2.5v-2h-2v2h-1a2.5 2.5 0 1 0 0 5h4a.5.5 0 0 1 0 1z" />
        </x-dashboard.cards.overview>

        <x-dashboard.cards.overview
            style="col"
            :index="6"
            :title="__('ui.completed_orders_in_period')"
            :count="number_format($completedOrdersCount)"
            :authorize="true"
            :link="route('dashboard.orders.index', ['statusFilter' => App\Models\Order::COMPLETED_STATUS])">
            <path fill="currentColor" d="M18 16h-2v-1H8v1H6v-1H2v5h20v-5h-4zm-1-8V4H7v4H2v6h4v-2h2v2h8v-2h2v2h4V8zM9 6h6v2H9z" />
        </x-dashboard.cards.overview>

        <x-dashboard.cards.overview
            style="col"
            :index="4"
            title="أرصدة معلقة (فنيين)"
            :count="$pendingPayouts . ' ' . __('ui.currency') . ' (' . $payoutRequestsCount . ' طلب)'"
            :authorize="Illuminate\Support\Facades\Gate::allows('view users')"
            :link="route('dashboard.users.payout_requests')">
            <path fill="currentColor" d="M21 18v1c0 1.1-.9 2-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h14c1.1 0 2 .9 2 2v1h-9a2 2 0 0 0-2 2v8a2 2 0 0 0 2 2zm-9-2h10V8H12zm4-2.5c-.83 0-1.5-.67-1.5-1.5s.67-1.5 1.5-1.5s1.5.67 1.5 1.5s-.67 1.5-1.5 1.5" />
        </x-dashboard.cards.overview>
    </div>

    {{-- ── Revenue Breakdown Table ── --}}
    <div class="rounded-2xl border border-gray-200 bg-white p-5 md:p-6 mb-5 dark:border-gray-800 dark:bg-white/[0.03]">
        <h3 class="text-lg font-semibold text-gray-800 dark:text-white mb-4">تفصيل الإيرادات والضرائب</h3>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-gray-200 dark:border-gray-700">
                        <th class="py-3 px-4 text-start font-medium text-gray-500">النوع</th>
                        <th class="py-3 px-4 text-start font-medium text-gray-500">الإيرادات</th>
                        <th class="py-3 px-4 text-start font-medium text-gray-500">الضرائب</th>
                        <th class="py-3 px-4 text-start font-medium text-gray-500">{{ __('ui.invoice_count') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                    <tr>
                        <td class="py-3 px-4 text-gray-700 dark:text-gray-300">الطلبات المكتملة</td>
                        <td class="py-3 px-4 font-semibold text-green-600">{{ $orderRevenue }} {{ __('ui.currency') }}</td>
                        <td class="py-3 px-4 text-red-500">{{ $orderTax }} {{ __('ui.currency') }}</td>
                        <td class="py-3 px-4 text-gray-600 dark:text-gray-400">{{ number_format($orderInvoiceCount) }}</td>
                    </tr>
                    <tr>
                        <td class="py-3 px-4 text-gray-700 dark:text-gray-300">الخدمات الإضافية</td>
                        <td class="py-3 px-4 font-semibold text-green-600">{{ $additionalRevenue }} {{ __('ui.currency') }}</td>
                        <td class="py-3 px-4 text-red-500">{{ $additionalTax }} {{ __('ui.currency') }}</td>
                        <td class="py-3 px-4 text-gray-600 dark:text-gray-400">{{ number_format($additionalInvoiceCount) }}</td>
                    </tr>
                    <tr>
                        <td class="py-3 px-4 text-gray-700 dark:text-gray-300">تجديد الاشتراكات</td>
                        <td class="py-3 px-4 font-semibold text-green-600">{{ $subscriptionRevenue }} {{ __('ui.currency') }}</td>
                        <td class="py-3 px-4 text-red-500">{{ $subscriptionTax }} {{ __('ui.currency') }}</td>
                        <td class="py-3 px-4 text-gray-600 dark:text-gray-400">{{ number_format($subscriptionInvoiceCount) }}</td>
                    </tr>
                    <tr class="bg-gray-50 dark:bg-gray-900 font-bold">
                        <td class="py-3 px-4 text-gray-800 dark:text-white">الإجمالي</td>
                        <td class="py-3 px-4 text-green-600">{{ $totalRevenue }} {{ __('ui.currency') }}</td>
                        <td class="py-3 px-4 text-red-500">{{ $totalTax }} {{ __('ui.currency') }}</td>
                        <td class="py-3 px-4 text-gray-600 dark:text-gray-400">{{ number_format($orderInvoiceCount + $additionalInvoiceCount + $subscriptionInvoiceCount) }}</td>
                    </tr>
                    <tr>
                        <td class="py-3 px-4 text-gray-700 dark:text-gray-300">التحويلات البنكية (مدفوعات)</td>
                        <td class="py-3 px-4 font-semibold text-orange-500" colspan="2">{{ $bankTransfers }} {{ __('ui.currency') }}</td>
                        <td class="py-3 px-4 text-gray-600 dark:text-gray-400">{{ number_format($bankTransferCount) }}</td>
                    </tr>
                    <tr>
                        <td class="py-3 px-4 text-gray-700 dark:text-gray-300">{{ __('ui.discounts_given') }}</td>
                        <td class="py-3 px-4 font-semibold text-purple-500" colspan="3">{{ $totalDiscountGiven }} {{ __('ui.currency') }}</td>
                    </tr>
                    <tr class="bg-green-50 dark:bg-green-900/20 font-bold">
                        <td class="py-3 px-4 text-green-800 dark:text-green-300">{{ __('ui.net_profit') }}</td>
                        <td class="py-3 px-4 text-green-700 dark:text-green-400" colspan="3">{{ $netProfit }} {{ __('ui.currency') }}</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

</x-layouts.dashboard>
