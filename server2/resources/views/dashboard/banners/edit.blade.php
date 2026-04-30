<x-layouts.dashboard page="edit_banner">

    <x-dashboard.breadcrumb :page="__('ui.edit_banner')">
        @can('viewAny', App\Models\Banner::class)
            <x-dashboard.breadcrumb-back
                :url="route('dashboard.banners.index')"
                :name="__('ui.view_banners')" />
        @endcan
    </x-dashboard.breadcrumb>

    @session('status')
        <div class="mb-5">
            <x-dashboard.alerts.success :title="$value" />
        </div>
    @endsession

    <div class="relative overflow-hidden rounded-xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03] overflow-x-auto">
        <form
            x-data="{ loading: false }"
            x-on:submit="loading = true"
            method="POST"
            action="{{ route('dashboard.banners.update', ['banner' => $banner->id]) }}"
            enctype="multipart/form-data"
            class="p-4 max-w-xl flex flex-col gap-5">

            @method('PUT')
            @csrf

            <!-- Image -->
            <x-dashboard.inputs.file.single-image
                class="w-full"
                preview-class="w-full h-36 sm:h-64 aspect-video"
                id="image"
                name="image"
                :image-url="$image"
                accept=".webp, .png, .jpg, .jpeg" />

            <!-- Name ar -->
            <x-dashboard.inputs.default
                name="name"
                locale="ar"
                :value="old('name.ar', $banner->getTranslation('name', 'ar'))"
                id="banner-name-ar"
                :required="true" />

            <!-- Name en -->
            <x-dashboard.inputs.default
                name="name"
                locale="en"
                :value="old('name.en', $banner->getTranslation('name', 'en'))"
                id="banner-name-en"
                :required="true" />

            <!-- URL -->
            <x-dashboard.inputs.default
                name="url"
                :value="old('url', $banner->url)"
                id="banner-url"
                type="url" />

            <!-- Status -->
            <div class="w-fit">
                <x-dashboard.inputs.checkbox
                    name="status"
                    :title="__('ui.active')"
                    :checked="old('status', $banner->isActive())" />
            </div>

            <!-- Submit button -->
            <x-dashboard.buttons.primary :name="__('ui.update')" />

        </form>
    </div>

    @vite('resources/js/file-input.js')

</x-layouts.dashboard>
