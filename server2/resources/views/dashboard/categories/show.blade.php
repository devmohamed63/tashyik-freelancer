<x-layouts.dashboard page="show_category">

    <x-dashboard.breadcrumb :page="__('ui.show_category')">
        @can('viewAny', App\Models\Category::class)
            <x-dashboard.breadcrumb-back
                :url="route('dashboard.categories.index')"
                :name="__('ui.view_categories')" />
        @endcan
    </x-dashboard.breadcrumb>

    <div class="p-4 relative overflow-hidden rounded-xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03] flex flex-col gap-5 overflow-x-auto">

        <x-dashboard.info-label :name="__('validation.attributes.image')">
            <img class="rounded-md w-48 h-48 object-center object-cover" src="{{ $image }}">
        </x-dashboard.info-label>

        <div class="max-w-xl grid grid-cols-1 sm:grid-cols-2 gap-5">

            <x-dashboard.info-label
                :name="__('validation.attributes.name') . ' ' . __('ui.ar')"
                :value="$category->getTranslation('name', 'ar')" />

            <x-dashboard.info-label
                :name="__('validation.attributes.description') . ' ' . __('ui.ar')"
                :value="$category->getTranslation('description', 'ar')" />

            @if ($category?->category_id)
                <x-dashboard.info-label
                    :name="__('validation.attributes.parent')"
                    :value="$category->parent?->name" />
            @else
                <x-dashboard.info-label :name="__('validation.attributes.cities')" class="flex flex-wrap gap-2 items-center">

                    @foreach ($cities as $cityName)
                        <x-dashboard.badges.success :name="$cityName" />
                    @endforeach

                </x-dashboard.info-label>
            @endif

            <x-dashboard.info-label
                :name="__('ui.created_at')"
                :value="$category->created_at->isoFormat(config('app.time_format'))" />

        </div>
    </div>

</x-layouts.dashboard>
