<x-layouts.dashboard page="view_service_providers">

    <x-dashboard.breadcrumb :page="$typeFilter === 'institution' ? __('ui.view_institutions') : ($typeFilter === 'company' ? __('ui.view_companies') : __('ui.view_service_providers'))" />

    @if ($typeFilter === 'institution' || $typeFilter === 'company')
        {{-- Institution/Company Specific Stats --}}
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-5">
            {{-- Institutions Count --}}
            <a href="{{ route('dashboard.users.service_providers', ['typeFilter' => 'institution']) }}"
               class="rounded-xl border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-white/[0.03] transition-all hover:shadow-md hover:border-indigo-300 dark:hover:border-indigo-600 {{ $typeFilter === 'institution' ? 'ring-2 ring-indigo-500 border-indigo-500 dark:ring-indigo-400 dark:border-indigo-400' : '' }}">
                <p class="text-xs text-gray-500 dark:text-gray-400 mb-1">{{ __('ui.view_institutions') }}</p>
                <p class="text-2xl font-bold text-indigo-600 dark:text-indigo-400">{{ number_format($institutionCount) }}</p>
            </a>

            {{-- Companies Count --}}
            <a href="{{ route('dashboard.users.service_providers', ['typeFilter' => 'company']) }}"
               class="rounded-xl border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-white/[0.03] transition-all hover:shadow-md hover:border-teal-300 dark:hover:border-teal-600 {{ $typeFilter === 'company' ? 'ring-2 ring-teal-500 border-teal-500 dark:ring-teal-400 dark:border-teal-400' : '' }}">
                <p class="text-xs text-gray-500 dark:text-gray-400 mb-1">{{ __('ui.view_companies') }}</p>
                <p class="text-2xl font-bold text-teal-600 dark:text-teal-400">{{ number_format($companyCount) }}</p>
            </a>

            {{-- Total Members --}}
            <div class="rounded-xl border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-white/[0.03]">
                <p class="text-xs text-gray-500 dark:text-gray-400 mb-1">{{ __('ui.total_members') }}</p>
                <p class="text-2xl font-bold text-gray-800 dark:text-white">{{ number_format($totalMembers) }}</p>
            </div>

            {{-- All Providers --}}
            <a href="{{ route('dashboard.users.service_providers') }}"
               class="rounded-xl border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-white/[0.03] transition-all hover:shadow-md hover:border-brand-300 dark:hover:border-brand-600">
                <p class="text-xs text-gray-500 dark:text-gray-400 mb-1">{{ __('ui.all') }} {{ __('ui.service_providers') }}</p>
                <p class="text-2xl font-bold text-gray-800 dark:text-white">{{ number_format($totalCount) }}</p>
            </a>
        </div>
    @else
        {{-- Default Stats --}}
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-5">
            {{-- Total --}}
            <a href="{{ route('dashboard.users.service_providers') }}"
               class="rounded-xl border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-white/[0.03] transition-all hover:shadow-md hover:border-brand-300 dark:hover:border-brand-600 {{ !request('statusFilter') ? 'ring-2 ring-brand-500 border-brand-500 dark:ring-brand-400 dark:border-brand-400' : '' }}">
                <p class="text-xs text-gray-500 dark:text-gray-400 mb-1">{{ __('ui.all') }}</p>
                <p class="text-2xl font-bold text-gray-800 dark:text-white">{{ number_format($totalCount) }}</p>
            </a>

            {{-- Pending --}}
            <a href="{{ route('dashboard.users.service_providers', ['statusFilter' => 'pending']) }}"
               class="rounded-xl border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-white/[0.03] transition-all hover:shadow-md hover:border-yellow-300 dark:hover:border-yellow-600 {{ request('statusFilter') === 'pending' ? 'ring-2 ring-yellow-500 border-yellow-500 dark:ring-yellow-400 dark:border-yellow-400' : '' }}">
                <p class="text-xs text-gray-500 dark:text-gray-400 mb-1">{{ __('ui.pending') }}</p>
                <div class="flex items-center gap-2">
                    <p class="text-2xl font-bold text-yellow-600 dark:text-yellow-400">{{ number_format($pendingCount) }}</p>
                    @if ($pendingCount > 0)
                        <span class="inline-flex items-center rounded-full bg-yellow-100 px-2 py-0.5 text-xs font-medium text-yellow-700 dark:bg-yellow-500/20 dark:text-yellow-300 animate-pulse">{{ __('ui.pending_action') }}</span>
                    @endif
                </div>
            </a>

            {{-- Active --}}
            <a href="{{ route('dashboard.users.service_providers', ['statusFilter' => 'active']) }}"
               class="rounded-xl border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-white/[0.03] transition-all hover:shadow-md hover:border-green-300 dark:hover:border-green-600 {{ request('statusFilter') === 'active' ? 'ring-2 ring-green-500 border-green-500 dark:ring-green-400 dark:border-green-400' : '' }}">
                <p class="text-xs text-gray-500 dark:text-gray-400 mb-1">{{ __('ui.active') }}</p>
                <p class="text-2xl font-bold text-green-600 dark:text-green-400">{{ number_format($activeCount) }}</p>
            </a>

            {{-- Inactive --}}
            <a href="{{ route('dashboard.users.service_providers', ['statusFilter' => 'inactive']) }}"
               class="rounded-xl border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-white/[0.03] transition-all hover:shadow-md hover:border-gray-300 dark:hover:border-gray-600 {{ request('statusFilter') === 'inactive' ? 'ring-2 ring-gray-500 border-gray-500 dark:ring-gray-400 dark:border-gray-400' : '' }}">
                <p class="text-xs text-gray-500 dark:text-gray-400 mb-1">{{ __('ui.inactive') }}</p>
                <p class="text-2xl font-bold text-gray-500 dark:text-gray-400">{{ number_format($inactiveCount) }}</p>
            </a>
        </div>
    @endif

    <livewire:dashboard.service-providers-table />

</x-layouts.dashboard>
