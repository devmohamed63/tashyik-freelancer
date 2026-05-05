<x-layouts.dashboard page="show_service">

    <x-dashboard.breadcrumb :page="__('ui.show_service')">
        @can('viewAny', App\Models\Service::class)
            <x-dashboard.breadcrumb-back
                :url="route('dashboard.services.index')"
                :name="__('ui.view_services')" />
        @endcan
    </x-dashboard.breadcrumb>

    <div class="p-4 relative overflow-hidden rounded-xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03] flex flex-col gap-5 overflow-x-auto">

        <x-dashboard.info-label :name="__('validation.attributes.image')">
            <img class="rounded-md w-48 h-48 object-center object-cover" src="{{ $image }}">
        </x-dashboard.info-label>

        <div class="max-w-xl grid grid-cols-1 sm:grid-cols-2 gap-5">

            <x-dashboard.info-label
                :name="__('validation.attributes.name') . ' ' . __('ui.ar')"
                :value="$service->getTranslation('name', 'ar')" />

            <x-dashboard.info-label
                :name="__('validation.attributes.price')"
                :value="$service->getPrice()['after_discount']" />

            <x-dashboard.info-label
                :name="__('validation.attributes.description') . ' ' . __('ui.ar')"
                :value="$service->getTranslation('description', 'ar')" />

            @if ($service?->category_id)
                <x-dashboard.info-label
                    :name="__('validation.attributes.category')"
                    :value="$service->category?->name" />
            @endif

            <x-dashboard.info-label
                :name="__('ui.created_at')"
                :value="$service->created_at->isoFormat(config('app.time_format'))" />

        </div>
    </div>

</x-layouts.dashboard>
