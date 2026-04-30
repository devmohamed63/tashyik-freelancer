<x-layouts.dashboard page="create_banner">

    <x-dashboard.breadcrumb :page="__('ui.create_banner')">
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
            action="{{ route('dashboard.banners.store') }}"
            enctype="multipart/form-data"
            class="p-4 max-w-xl flex flex-col gap-5">

            @csrf

            <!-- Image -->
            <x-dashboard.inputs.file.single-image
                class="w-full"
                preview-class="w-full h-36 sm:h-64 aspect-video"
                id="image"
                name="image"
                accept=".webp, .png, .jpg, .jpeg"
                :required="true" />

            <!-- Name ar -->
            <x-dashboard.inputs.default
                name="name"
                locale="ar"
                id="banner-name-ar"
                :required="true" />

            <!-- Name en -->
            <x-dashboard.inputs.default
                name="name"
                locale="en"
                id="banner-name-en"
                :required="true" />

            <!-- URL -->
            <x-dashboard.inputs.default
                name="url"
                id="banner-url"
                type="url" />

            <!-- Status -->
            <div class="w-fit">
                <x-dashboard.inputs.checkbox
                    name="status"
                    :title="__('ui.active')"
                    :checked="old('status', true)" />
            </div>

            <!-- Submit button -->
            <x-dashboard.buttons.primary :name="__('ui.create')" />

        </form>
    </div>

    @vite('resources/js/file-input.js')

</x-layouts.dashboard>
