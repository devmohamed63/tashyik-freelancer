<x-layouts.dashboard page="add_categories">

    <x-dashboard.breadcrumb :page="__('ui.add_categories')">
        @can('viewAny', App\Models\Category::class)
            <x-dashboard.breadcrumb-back
                :url="route('dashboard.categories.index')"
                :name="__('ui.view_categories')" />
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
            action="{{ route('dashboard.categories.store') }}"
            enctype="multipart/form-data"
            class="p-4 max-w-xl flex flex-col gap-5">

            @csrf

            <!-- Image -->
            <x-dashboard.inputs.file.single-image
                class="w-48"
                preview-class="w-48 h-48"
                id="image"
                name="image"
                accept=".webp, .png, .jpg, .jpeg"
                :required="true" />

            <!-- Name ar -->
            <x-dashboard.inputs.default
                name="name"
                locale="ar"
                id="category-name-ar"
                :required="true" />

            <!-- Description ar -->
            <x-dashboard.inputs.textarea
                name="description"
                locale="ar"
                id="category-description-ar"
                rows="4" />

            <!-- Gallery -->
            <x-dashboard.inputs.file.default
                id="gallery"
                name="gallery"
                input-name="gallery[]"
                accept=".webp, .png, .jpg, .jpeg"
                :image-description="true"
                multiple="true" />

            <div x-data="{ showCities: {{ old('parent') ? 'false' : 'true' }} }">
                <!-- Parent -->
                <x-dashboard.inputs.select
                    label="parent"
                    name="parent"
                    :single="true"
                    global-value="showCities"
                    :children="$categories"
                    child-key="id"
                    :selected="old('parent')" />

                <div class="pt-5" x-show="showCities">
                    <!-- Cities -->
                    <x-dashboard.inputs.select
                        id="cities"
                        label="cities"
                        name="cities[]"
                        :children="$cities"
                        child-key="id"
                        :selected="old('cities')"
                        :required="true" />
                </div>
            </div>

            <!-- Submit button -->
            <x-dashboard.buttons.primary :name="__('ui.add')" />

        </form>
    </div>

    @vite('resources/js/file-input.js')

</x-layouts.dashboard>
