<x-layouts.dashboard page="show_banner">

    <x-dashboard.breadcrumb :page="__('ui.show_banner')">
        @can('viewAny', App\Models\Banner::class)
            <x-dashboard.breadcrumb-back
                :url="route('dashboard.banners.index')"
                :name="__('ui.view_banners')" />
        @endcan
    </x-dashboard.breadcrumb>

    <div class="p-4 relative overflow-hidden rounded-xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03] flex flex-col gap-5 overflow-x-auto">

        <x-dashboard.info-label :name="__('validation.attributes.image')">
            <img class="rounded-md max-w-xl w-full h-36 sm:h-64 aspect-video object-center object-cover" src="{{ $image }}">
        </x-dashboard.info-label>

        <div class="max-w-xl grid grid-cols-1 sm:grid-cols-2 gap-5">

            <x-dashboard.info-label
                :name="__('validation.attributes.name[ar]')"
                :value="$banner->getTranslation('name', 'ar')" />

            <x-dashboard.info-label
                :name="__('validation.attributes.name[en]')"
                :value="$banner->getTranslation('name', 'en')" />

            @if ($banner->url)
                <x-dashboard.info-label :name="__('validation.attributes.url')">
                    <x-dashboard.ui.link :href="$banner->url" :name="$banner->url" target="_blank" />
                </x-dashboard.info-label>
            @endif

            <x-dashboard.info-label :name="__('validation.attributes.status')">
                @if ($banner->isActive())
                    <x-dashboard.badges.success :name="__('ui.active')" />
                @else
                    <x-dashboard.badges.light :name="__('ui.inactive')" />
                @endif
            </x-dashboard.info-label>

            <x-dashboard.info-label
                :name="__('ui.created_at')"
                :value="$date" />

        </div>
    </div>

</x-layouts.dashboard>
