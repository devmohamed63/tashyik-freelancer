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

            <div class="border-t border-gray-200 dark:border-gray-700 pt-5 mt-2">
                <h3 class="text-lg font-medium text-gray-800 dark:text-white/90 mb-4">{{ __('ui.seo_settings') }}</h3>

                <!-- Meta Title ar -->
                <x-dashboard.inputs.default
                    name="meta_title"
                    locale="ar"
                    id="category-meta-title-ar"
                    :required="false" />

                <!-- Meta Description ar -->
                <div class="mt-5">
                    <x-dashboard.label name="meta_description" locale="ar" :required="false" />
                    <textarea
                        name="meta_description[ar]"
                        id="category-meta-description-ar"
                        rows="2"
                        class="dark:bg-dark-900 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 dark:focus:border-brand-800">{{ old('meta_description.ar') }}</textarea>
                    @error('meta_description.ar')
                        <x-dashboard.inputs.error :message="$message" />
                    @enderror
                </div>
            </div>

            <!-- Submit button -->
            <x-dashboard.buttons.primary :name="__('ui.add')" />

        </form>
    </div>

    @vite('resources/js/file-input.js')

</x-layouts.dashboard>
