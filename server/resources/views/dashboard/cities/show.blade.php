<x-layouts.dashboard page="show_city">

    <x-dashboard.breadcrumb :page="__('ui.show_city') . ' - ' . $city->name">
        @can('viewAny', App\Models\City::class)
            <x-dashboard.breadcrumb-back
                :url="route('dashboard.cities.index')"
                :name="__('ui.back_to_cities')" />
        @endcan
    </x-dashboard.breadcrumb>

    {{-- ═══════════════════════════════════════════════════════
         Section 1: City Header
    ═══════════════════════════════════════════════════════ --}}
    <div class="rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03] p-5 md:p-6 mb-6">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h2 class="text-2xl font-bold text-gray-900 dark:text-white flex items-center gap-3">
                    {{-- material-symbols:location-on-rounded --}}
                    <svg class="w-7 h-7 text-brand-500" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path fill="currentColor" d="M12 21.325q-.35 0-.7-.125t-.625-.375Q9.05 19.325 7.8 17.9t-2.087-2.762t-1.275-2.575T4 10.2q0-3.75 2.413-5.975T12 2t5.588 2.225T20 10.2q0 1.125-.437 2.363t-1.275 2.575T16.2 17.9t-2.875 2.925q-.275.25-.625.375t-.7.125M12 12q.825 0 1.413-.587T14 10t-.587-1.412T12 8t-1.412.588T10 10t.588 1.413T12 12" /></svg>
                    {{ $city->name }}
                </h2>
                <div class="mt-2 flex flex-wrap gap-4 text-sm text-gray-500 dark:text-gray-400">
                    <span>{{ __('validation.attributes.name') }} {{ __('ui.ar') }}: <strong class="text-gray-700 dark:text-gray-300">{{ $city->getTranslation('name', 'ar') }}</strong></span>
                    <span>{{ __('validation.attributes.name') }} {{ __('ui.en') }}: <strong class="text-gray-700 dark:text-gray-300">{{ $city->getTranslation('name', 'en') }}</strong></span>
                    <span>{{ __('ui.created_at') }}: <strong class="text-gray-700 dark:text-gray-300">{{ $city->created_at->isoFormat(config('app.time_format')) }}</strong></span>
                </div>
            </div>
        </div>
    </div>

    {{-- ═══════════════════════════════════════════════════════
         Section 2: Summary Stats Cards
    ═══════════════════════════════════════════════════════ --}}
    <div class="grid grid-cols-2 md:grid-cols-3 xl:grid-cols-6 gap-4 mb-6">
        {{-- Service Providers --}}
        <div class="rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03] p-4 text-center">
            <div class="mx-auto w-10 h-10 rounded-xl bg-brand-100 dark:bg-brand-900/40 flex items-center justify-center mb-3">
                <svg class="w-5 h-5 text-brand-600 dark:text-brand-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path fill="currentColor" d="M16.675 18.7a2.65 2.65 0 0 1-1.26 2.48c-.418.257-.9.392-1.39.39H4.652a2.63 2.63 0 0 1-1.39-.39A2.62 2.62 0 0 1 2.01 18.7a2.6 2.6 0 0 1 .5-1.35a8.8 8.8 0 0 1 6.812-3.51a8.78 8.78 0 0 1 6.842 3.5a2.7 2.7 0 0 1 .51 1.36M14.245 7.32a4.92 4.92 0 0 1-4.902 4.91a4.903 4.903 0 0 1-4.797-5.858a4.9 4.9 0 0 1 6.678-3.57a4.9 4.9 0 0 1 3.03 4.518z" /></svg>
            </div>
            <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ number_format($totalServiceProviders) }}</p>
            <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">{{ __('ui.service_providers') }}</p>
        </div>

        {{-- Users --}}
        <div class="rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03] p-4 text-center">
            <div class="mx-auto w-10 h-10 rounded-xl bg-sky-100 dark:bg-sky-900/40 flex items-center justify-center mb-3">
                <svg class="w-5 h-5 text-sky-600 dark:text-sky-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path fill="currentColor" d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4s-4 1.79-4 4s1.79 4 4 4m0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4" /></svg>
            </div>
            <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ number_format($totalUsers) }}</p>
            <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">{{ __('ui.customers') }}</p>
        </div>

        {{-- Total Orders --}}
        <div class="rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03] p-4 text-center">
            <div class="mx-auto w-10 h-10 rounded-xl bg-emerald-100 dark:bg-emerald-900/40 flex items-center justify-center mb-3">
                <svg class="w-5 h-5 text-emerald-600 dark:text-emerald-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path fill="currentColor" d="M18 16h-2v-1H8v1H6v-1H2v5h20v-5h-4zm-1-8V4H7v4H2v6h4v-2h2v2h8v-2h2v2h4V8zM9 6h6v2H9z" /></svg>
            </div>
            <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ number_format($totalOrders) }}</p>
            <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">{{ __('ui.orders') }}</p>
        </div>

        {{-- Total Revenue --}}
        <div class="rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03] p-4 text-center">
            <div class="mx-auto w-10 h-10 rounded-xl bg-green-100 dark:bg-green-900/40 flex items-center justify-center mb-3">
                <svg class="w-5 h-5 text-green-600 dark:text-green-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path fill="currentColor" d="M12.005 22.003c-5.523 0-10-4.477-10-10s4.477-10 10-10s10 4.477 10 10s-4.477 10-10 10m-3.5-8v2h2.5v2h2v-2h1a2.5 2.5 0 1 0 0-5h-4a.5.5 0 1 1 0-1h5.5v-2h-2.5v-2h-2v2h-1a2.5 2.5 0 1 0 0 5h4a.5.5 0 0 1 0 1z" /></svg>
            </div>
            <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ $revenueTotal }}</p>
            <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">{{ __('ui.revenue') }} ({{ __('ui.currency') }})</p>
        </div>

        {{-- Avg Order Value --}}
        <div class="rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03] p-4 text-center">
            <div class="mx-auto w-10 h-10 rounded-xl bg-violet-100 dark:bg-violet-900/40 flex items-center justify-center mb-3">
                <svg class="w-5 h-5 text-violet-600 dark:text-violet-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path fill="currentColor" d="M17.45 15.18L22 7.31V21H2V3h2v14.54L9.5 8L16 12l5.61-9.75l1.74 1l-6.69 11.6l-6.53-3.84z" /></svg>
            </div>
            <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ $avgOrderValue }}</p>
            <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">{{ __('ui.avg_order_value') }} ({{ __('ui.currency') }})</p>
        </div>

        {{-- Pending Providers --}}
        <div class="rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03] p-4 text-center">
            <div class="mx-auto w-10 h-10 rounded-xl bg-amber-100 dark:bg-amber-900/40 flex items-center justify-center mb-3">
                <svg class="w-5 h-5 text-amber-600 dark:text-amber-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path fill="currentColor" d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10s10-4.48 10-10S17.52 2 12 2m1 15h-2v-2h2zm0-4h-2V7h2z" /></svg>
            </div>
            <p class="text-2xl font-bold text-gray-900 dark:text-white {{ $pendingProviders > 0 ? 'text-amber-600 dark:text-amber-400' : '' }}">{{ number_format($pendingProviders) }}</p>
            <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">{{ __('ui.pending_providers') }}</p>
        </div>
    </div>

    {{-- ═══════════════════════════════════════════════════════
         Section 3: Provider Status & Entity Breakdown
    ═══════════════════════════════════════════════════════ --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">

        {{-- Providers by Status --}}
        <div class="rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03] p-5 md:p-6">
            <h3 class="text-sm font-semibold text-gray-800 dark:text-white/90 mb-4 flex items-center gap-2">
                <svg class="w-4 h-4 text-brand-500" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path fill="currentColor" d="M11 21v-2H5V5h6V3H5q-.825 0-1.412.588T3 5v14q0 .825.588 1.413T5 21zm2-4l-1.4-1.45L14.15 13H9v-2h5.15L11.6 8.45L13 7l5 5z" /></svg>
                {{ __('ui.providers_by_status') }}
            </h3>
            <div class="grid grid-cols-3 gap-3">
                <div class="text-center p-4 rounded-xl bg-green-50 dark:bg-green-900/20 border border-green-100 dark:border-green-800/30">
                    <div class="w-8 h-8 mx-auto rounded-full bg-green-500/20 flex items-center justify-center mb-2">
                        <span class="w-3 h-3 rounded-full bg-green-500"></span>
                    </div>
                    <p class="text-2xl font-bold text-green-700 dark:text-green-400">{{ number_format($activeProviders) }}</p>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">{{ __('ui.active') }}</p>
                </div>
                <div class="text-center p-4 rounded-xl bg-amber-50 dark:bg-amber-900/20 border border-amber-100 dark:border-amber-800/30">
                    <div class="w-8 h-8 mx-auto rounded-full bg-amber-500/20 flex items-center justify-center mb-2">
                        <span class="w-3 h-3 rounded-full bg-amber-500"></span>
                    </div>
                    <p class="text-2xl font-bold text-amber-700 dark:text-amber-400">{{ number_format($pendingProviders) }}</p>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">{{ __('ui.pending') }}</p>
                </div>
                <div class="text-center p-4 rounded-xl bg-red-50 dark:bg-red-900/20 border border-red-100 dark:border-red-800/30">
                    <div class="w-8 h-8 mx-auto rounded-full bg-red-500/20 flex items-center justify-center mb-2">
                        <span class="w-3 h-3 rounded-full bg-red-500"></span>
                    </div>
                    <p class="text-2xl font-bold text-red-700 dark:text-red-400">{{ number_format($inactiveProviders) }}</p>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">{{ __('ui.inactive') }}</p>
                </div>
            </div>
        </div>

        {{-- Providers by Entity Type --}}
        <div class="rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03] p-5 md:p-6">
            <h3 class="text-sm font-semibold text-gray-800 dark:text-white/90 mb-4 flex items-center gap-2">
                <svg class="w-4 h-4 text-brand-500" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path fill="currentColor" d="M12 7V3H2v18h20V7zM6 19H4v-2h2zm0-4H4v-2h2zm0-4H4V9h2zm0-4H4V5h2zm4 12H8v-2h2zm0-4H8v-2h2zm0-4H8V9h2zm0-4H8V5h2zm10 12h-8v-2h2v-2h-2v-2h2v-2h-2V9h8zm-2-8h-2v2h2zm0 4h-2v2h2z" /></svg>
                {{ __('ui.providers_by_entity_type') }}
            </h3>
            <div class="grid grid-cols-3 gap-3">
                <div class="text-center p-4 rounded-xl bg-brand-50 dark:bg-brand-900/20 border border-brand-100 dark:border-brand-800/30">
                    <div class="w-8 h-8 mx-auto rounded-full bg-brand-500/20 flex items-center justify-center mb-2">
                        <span class="w-3 h-3 rounded-full bg-brand-500"></span>
                    </div>
                    <p class="text-2xl font-bold text-brand-700 dark:text-brand-400">{{ number_format($individualProviders) }}</p>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">{{ __('ui.individual') }}</p>
                </div>
                <div class="text-center p-4 rounded-xl bg-indigo-50 dark:bg-indigo-900/20 border border-indigo-100 dark:border-indigo-800/30">
                    <div class="w-8 h-8 mx-auto rounded-full bg-indigo-500/20 flex items-center justify-center mb-2">
                        <span class="w-3 h-3 rounded-full bg-indigo-500"></span>
                    </div>
                    <p class="text-2xl font-bold text-indigo-700 dark:text-indigo-400">{{ number_format($institutionProviders) }}</p>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">{{ __('ui.institution') }}</p>
                </div>
                <div class="text-center p-4 rounded-xl bg-teal-50 dark:bg-teal-900/20 border border-teal-100 dark:border-teal-800/30">
                    <div class="w-8 h-8 mx-auto rounded-full bg-teal-500/20 flex items-center justify-center mb-2">
                        <span class="w-3 h-3 rounded-full bg-teal-500"></span>
                    </div>
                    <p class="text-2xl font-bold text-teal-700 dark:text-teal-400">{{ number_format($companyProviders) }}</p>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">{{ __('ui.company') }}</p>
                </div>
            </div>
        </div>

    </div>

    {{-- ═══════════════════════════════════════════════════════
         Section 4: Orders Status Breakdown
    ═══════════════════════════════════════════════════════ --}}
    <div class="rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03] p-5 md:p-6 mb-6">
        <h3 class="text-sm font-semibold text-gray-800 dark:text-white/90 mb-4 flex items-center gap-2">
            <svg class="w-4 h-4 text-brand-500" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path fill="currentColor" d="M18 16h-2v-1H8v1H6v-1H2v5h20v-5h-4zm-1-8V4H7v4H2v6h4v-2h2v2h8v-2h2v2h4V8zM9 6h6v2H9z" /></svg>
            {{ __('ui.orders_by_status') }}
        </h3>
        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-4">
            <div class="text-center p-3 rounded-xl bg-blue-50 dark:bg-blue-900/20">
                <p class="text-2xl font-bold text-blue-600 dark:text-blue-400">{{ number_format($newOrders) }}</p>
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">{{ __('ui.new') }}</p>
            </div>
            <div class="text-center p-3 rounded-xl bg-cyan-50 dark:bg-cyan-900/20">
                <p class="text-2xl font-bold text-cyan-600 dark:text-cyan-400">{{ number_format($onTheWayOrders) }}</p>
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">{{ __('ui.service-provider-on-the-way') }}</p>
            </div>
            <div class="text-center p-3 rounded-xl bg-indigo-50 dark:bg-indigo-900/20">
                <p class="text-2xl font-bold text-indigo-600 dark:text-indigo-400">{{ number_format($arrivedOrders) }}</p>
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">{{ __('ui.service-provider-arrived') }}</p>
            </div>
            <div class="text-center p-3 rounded-xl bg-amber-50 dark:bg-amber-900/20">
                <p class="text-2xl font-bold text-amber-600 dark:text-amber-400">{{ number_format($startedOrders) }}</p>
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">{{ __('ui.started') }}</p>
            </div>
            <div class="text-center p-3 rounded-xl bg-green-50 dark:bg-green-900/20">
                <p class="text-2xl font-bold text-green-600 dark:text-green-400">{{ number_format($completedOrders) }}</p>
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">{{ __('ui.completed') }}</p>
            </div>
            <div class="text-center p-3 rounded-xl bg-gray-50 dark:bg-gray-800/40">
                <p class="text-2xl font-bold text-gray-700 dark:text-gray-300">{{ number_format($totalOrders) }}</p>
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">{{ __('ui.total') }}</p>
            </div>
        </div>
    </div>

    {{-- ═══════════════════════════════════════════════════════
         Section 5: Revenue Breakdown
    ═══════════════════════════════════════════════════════ --}}
    <div class="rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03] p-5 md:p-6 mb-6">
        <h3 class="text-sm font-semibold text-gray-800 dark:text-white/90 mb-4 flex items-center gap-2">
            <svg class="w-4 h-4 text-brand-500" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path fill="currentColor" d="M12.005 22.003c-5.523 0-10-4.477-10-10s4.477-10 10-10s10 4.477 10 10s-4.477 10-10 10m-3.5-8v2h2.5v2h2v-2h1a2.5 2.5 0 1 0 0-5h-4a.5.5 0 1 1 0-1h5.5v-2h-2.5v-2h-2v2h-1a2.5 2.5 0 1 0 0 5h4a.5.5 0 0 1 0 1z" /></svg>
            {{ __('ui.revenue_breakdown') }}
        </h3>
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
            <div class="text-center p-4 rounded-xl bg-gradient-to-br from-green-50 to-emerald-50 dark:from-green-900/20 dark:to-emerald-900/20 border border-green-100 dark:border-green-800/30">
                <p class="text-lg font-bold text-green-700 dark:text-green-400">{{ $revenueToday }}</p>
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">{{ __('ui.today') }}</p>
                <p class="text-[10px] text-gray-400 dark:text-gray-500">{{ __('ui.currency') }}</p>
            </div>
            <div class="text-center p-4 rounded-xl bg-gradient-to-br from-blue-50 to-sky-50 dark:from-blue-900/20 dark:to-sky-900/20 border border-blue-100 dark:border-blue-800/30">
                <p class="text-lg font-bold text-blue-700 dark:text-blue-400">{{ $revenueWeek }}</p>
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">{{ __('ui.this_week') }}</p>
                <p class="text-[10px] text-gray-400 dark:text-gray-500">{{ __('ui.currency') }}</p>
            </div>
            <div class="text-center p-4 rounded-xl bg-gradient-to-br from-violet-50 to-purple-50 dark:from-violet-900/20 dark:to-purple-900/20 border border-violet-100 dark:border-violet-800/30">
                <p class="text-lg font-bold text-violet-700 dark:text-violet-400">{{ $revenueMonth }}</p>
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">{{ __('ui.this_month') }}</p>
                <p class="text-[10px] text-gray-400 dark:text-gray-500">{{ __('ui.currency') }}</p>
            </div>
            <div class="text-center p-4 rounded-xl bg-gradient-to-br from-amber-50 to-orange-50 dark:from-amber-900/20 dark:to-orange-900/20 border border-amber-100 dark:border-amber-800/30">
                <p class="text-lg font-bold text-amber-700 dark:text-amber-400">{{ $revenueTotal }}</p>
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">{{ __('ui.total') }}</p>
                <p class="text-[10px] text-gray-400 dark:text-gray-500">{{ __('ui.currency') }}</p>
            </div>
        </div>
    </div>

    {{-- ═══════════════════════════════════════════════════════
         Section 6: Top Service Providers Table
    ═══════════════════════════════════════════════════════ --}}
    <div class="rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03] p-5 md:p-6 mb-6">
        <h3 class="text-sm font-semibold text-gray-800 dark:text-white/90 mb-4 flex items-center gap-2">
            <svg class="w-4 h-4 text-brand-500" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path fill="currentColor" d="M12 17.27L18.18 21l-1.64-7.03L22 9.24l-7.19-.61L12 2L9.19 8.63L2 9.24l5.46 4.73L5.82 21z" /></svg>
            {{ __('ui.top_service_providers_city') }}
        </h3>

        @if($topProviders->count() > 0)
        <div class="overflow-x-auto">
            <table class="w-full text-sm text-center text-gray-500 dark:text-gray-400">
                <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-800 dark:text-gray-400 border-b border-gray-100 dark:border-gray-700">
                    <tr>
                        <th class="px-4 py-3">#</th>
                        <th class="px-4 py-3">{{ __('validation.attributes.name') }}</th>
                        <th class="px-4 py-3">{{ __('validation.attributes.phone') }}</th>
                        <th class="px-4 py-3">{{ __('ui.account_type') }}</th>
                        <th class="px-4 py-3">{{ __('ui.status') }}</th>
                        <th class="px-4 py-3">{{ __('ui.completed_orders') }}</th>
                        <th class="px-4 py-3">{{ __('ui.revenue') }} ({{ __('ui.currency') }})</th>
                        <th class="px-4 py-3">{{ __('ui.categories') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                    @foreach($topProviders as $index => $provider)
                    <tr class="bg-white dark:bg-gray-900 hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors">
                        <td class="px-4 py-3 text-gray-400 font-mono">{{ $index + 1 }}</td>
                        <td class="px-4 py-3 font-medium text-gray-900 dark:text-white">{{ $provider->name }}</td>
                        <td class="px-4 py-3 font-mono" dir="ltr">{{ $provider->phone }}</td>
                        <td class="px-4 py-3">
                            @if($provider->entity_type === 'individual')
                                <span class="inline-flex items-center px-2 py-1 text-xs font-medium rounded-full bg-brand-100 text-brand-700 dark:bg-brand-900/40 dark:text-brand-400">{{ __('ui.individual') }}</span>
                            @elseif($provider->entity_type === 'institution')
                                <span class="inline-flex items-center px-2 py-1 text-xs font-medium rounded-full bg-indigo-100 text-indigo-700 dark:bg-indigo-900/40 dark:text-indigo-400">{{ __('ui.institution') }}</span>
                            @else
                                <span class="inline-flex items-center px-2 py-1 text-xs font-medium rounded-full bg-teal-100 text-teal-700 dark:bg-teal-900/40 dark:text-teal-400">{{ __('ui.company') }}</span>
                            @endif
                        </td>
                        <td class="px-4 py-3">
                            @if($provider->status === 'active')
                                <span class="inline-flex items-center px-2 py-1 text-xs font-medium rounded-full bg-green-100 text-green-700 dark:bg-green-900/40 dark:text-green-400">{{ __('ui.active') }}</span>
                            @elseif($provider->status === 'pending')
                                <span class="inline-flex items-center px-2 py-1 text-xs font-medium rounded-full bg-amber-100 text-amber-700 dark:bg-amber-900/40 dark:text-amber-400">{{ __('ui.pending') }}</span>
                            @else
                                <span class="inline-flex items-center px-2 py-1 text-xs font-medium rounded-full bg-red-100 text-red-700 dark:bg-red-900/40 dark:text-red-400">{{ __('ui.inactive') }}</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 font-bold text-emerald-600 dark:text-emerald-400">{{ number_format($provider->completed_orders_count) }}</td>
                        <td class="px-4 py-3 font-bold text-green-600 dark:text-green-400">{{ number_format($provider->revenue ?? 0, config('app.decimal_places')) }}</td>
                        <td class="px-4 py-3">
                            <div class="flex flex-wrap gap-1 justify-center">
                                @foreach($provider->categories as $cat)
                                    <span class="inline-block px-2 py-0.5 text-[10px] font-medium rounded-full bg-gray-100 text-gray-600 dark:bg-gray-800 dark:text-gray-400">{{ $cat->name }}</span>
                                @endforeach
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @else
            <p class="text-sm text-gray-500 dark:text-gray-400 text-center py-6">{{ __('ui.no_results') }}</p>
        @endif
    </div>

    {{-- ═══════════════════════════════════════════════════════
         Section 7: Top Services Table
    ═══════════════════════════════════════════════════════ --}}
    <div class="rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03] p-5 md:p-6 mb-6">
        <h3 class="text-sm font-semibold text-gray-800 dark:text-white/90 mb-4 flex items-center gap-2">
            <svg class="w-4 h-4 text-brand-500" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path fill="currentColor" fill-rule="evenodd" d="M12 6.75a5.25 5.25 0 0 1 6.775-5.025a.75.75 0 0 1 .313 1.248l-3.32 3.319a2.25 2.25 0 0 0 1.941 1.939l3.318-3.319a.75.75 0 0 1 1.248.313a5.25 5.25 0 0 1-5.472 6.756c-1.018-.086-1.87.1-2.309.634L7.344 21.3A3.298 3.298 0 1 1 2.7 16.657l8.684-7.151c.533-.44.72-1.291.634-2.309A5 5 0 0 1 12 6.75" clip-rule="evenodd" /></svg>
            {{ __('ui.top_services_city') }}
        </h3>

        @if($topServices->count() > 0)
        <div class="overflow-x-auto">
            <table class="w-full text-sm text-center text-gray-500 dark:text-gray-400">
                <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-800 dark:text-gray-400 border-b border-gray-100 dark:border-gray-700">
                    <tr>
                        <th class="px-4 py-3">#</th>
                        <th class="px-4 py-3">{{ __('ui.service') }}</th>
                        <th class="px-4 py-3">{{ __('ui.category') }}</th>
                        <th class="px-4 py-3">{{ __('ui.order_count') }}</th>
                        <th class="px-4 py-3">{{ __('ui.revenue') }} ({{ __('ui.currency') }})</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                    @foreach($topServices as $index => $item)
                    <tr class="bg-white dark:bg-gray-900 hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors">
                        <td class="px-4 py-3 text-gray-400 font-mono">{{ $index + 1 }}</td>
                        <td class="px-4 py-3 font-medium text-gray-900 dark:text-white">{{ $item->service?->name ?? '-' }}</td>
                        <td class="px-4 py-3">
                            @if($item->service?->category)
                                <span class="inline-flex items-center px-2 py-1 text-xs font-medium rounded-full bg-brand-100 text-brand-700 dark:bg-brand-900/40 dark:text-brand-400">{{ $item->service->category->name }}</span>
                            @else
                                -
                            @endif
                        </td>
                        <td class="px-4 py-3 font-bold text-brand-600 dark:text-brand-400">{{ number_format($item->orders_count) }}</td>
                        <td class="px-4 py-3 font-bold text-green-600 dark:text-green-400">{{ number_format($item->total_revenue ?? 0, config('app.decimal_places')) }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @else
            <p class="text-sm text-gray-500 dark:text-gray-400 text-center py-6">{{ __('ui.no_results') }}</p>
        @endif
    </div>

    {{-- ═══════════════════════════════════════════════════════
         Section 8: Categories in City
    ═══════════════════════════════════════════════════════ --}}
    <div class="rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03] p-5 md:p-6">
        <h3 class="text-sm font-semibold text-gray-800 dark:text-white/90 mb-4 flex items-center gap-2">
            <svg class="w-4 h-4 text-brand-500" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path fill="currentColor" d="M5.5 7A1.5 1.5 0 0 1 4 5.5A1.5 1.5 0 0 1 5.5 4A1.5 1.5 0 0 1 7 5.5A1.5 1.5 0 0 1 5.5 7m15.91 4.58l-9-9C12.05 2.22 11.55 2 11 2H4c-1.11 0-2 .89-2 2v7c0 .55.22 1.05.59 1.41l8.99 9c.37.36.87.59 1.42.59s1.05-.23 1.41-.59l7-7c.37-.36.59-.86.59-1.41c0-.56-.23-1.06-.59-1.42" /></svg>
            {{ __('ui.categories_in_city') }}
        </h3>

        @if($categories->count() > 0)
            <div class="flex flex-wrap gap-2">
                @foreach($categories as $cat)
                    <span class="inline-flex items-center px-3 py-1.5 text-sm font-medium rounded-lg bg-brand-50 text-brand-700 dark:bg-brand-900/30 dark:text-brand-400 border border-brand-200 dark:border-brand-800/40">
                        {{ $cat->name }}
                    </span>
                @endforeach
            </div>
        @else
            <p class="text-sm text-gray-500 dark:text-gray-400 text-center py-6">{{ __('ui.no_results') }}</p>
        @endif
    </div>

</x-layouts.dashboard>
