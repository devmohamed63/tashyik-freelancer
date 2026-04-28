<x-layouts.dashboard page="create_push_ad">

    <x-dashboard.breadcrumb :page="__('ui.create_push_ad')">
        @can('viewAny', App\Models\Banner::class)
            <x-dashboard.breadcrumb-back
                :url="route('dashboard.push-ads.index')"
                :name="__('ui.view_push_ads')" />
        @endcan
    </x-dashboard.breadcrumb>

    @session('status')
        <div class="mb-5">
            <x-dashboard.alerts.success :title="$value" />
        </div>
    @endsession

    <div class="rounded-xl border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-white/[0.03] md:p-6">
        <div class="mb-5 border-b border-gray-200 pb-4 dark:border-gray-800">
            <h1 class="text-lg font-semibold text-gray-900 dark:text-white">{{ __('ui.create_push_ad') }}</h1>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ __('ui.push_ads_intro') }}</p>
        </div>

        <livewire:dashboard.banners.create-ad :embed-in-modal="false" />
    </div>

</x-layouts.dashboard>
