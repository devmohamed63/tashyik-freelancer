<x-layouts.dashboard page="add_services">

    <x-dashboard.breadcrumb :page="__('ui.add_services')">
        @can('viewAny', App\Models\Service::class)
            <x-dashboard.breadcrumb-back
                :url="route('dashboard.services.index')"
                :name="__('ui.view_services')" />
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
            action="{{ route('dashboard.services.store') }}"
            enctype="multipart/form-data"
            class="p-5">

            @csrf

            <div class="flex flex-col md:grid grid-cols-2 gap-5">
                <div class="flex flex-col gap-5">
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
                        id="service-name-ar"
                        :required="true" />

                    <!-- Price -->
                    <x-dashboard.inputs.default
                        name="price"
                        type="number"
                        id="service-price"
                        autocomplete="off"
                        :description="__('validation.attributes.price_description')" />

                    <!-- Description ar -->
                    <x-dashboard.inputs.textarea
                        name="description"
                        locale="ar"
                        id="category-description-ar"
                        rows="4" />

                    <!-- Category -->
                    <x-dashboard.inputs.select
                        label="category"
                        name="category"
                        :single="true"
                        :children="$categories"
                        child-key="id"
                        :selected="old('category')"
                        :required="true" />

                    <!-- Gallery -->
                    <x-dashboard.inputs.file.default
                        id="gallery"
                        name="gallery"
                        input-name="gallery[]"
                        accept=".webp, .png, .jpg, .jpeg"
                        :image-description="true"
                        multiple="true" />

                    <div class="flex flex-col gap-5 md:grid grid-cols-2">

                        <!-- Warranty days -->
                        <x-dashboard.inputs.default
                            type="number"
                            name="warranty_days"
                            id="service-warranty-days"
                            autocomplete="off"
                            min="0"
                            value="0" />

                        <!-- Warranty months -->
                        <x-dashboard.inputs.default
                            type="number"
                            name="warranty_months"
                            id="service-warranty-months"
                            autocomplete="off"
                            min="0"
                            value="0" />

                    </div>
                </div>

                @include('dashboard.services.partials.highlights')

            </div>

            <div class="mt-5 border-t border-gray-200 dark:border-gray-700 pt-5">
                <h3 class="text-lg font-medium text-gray-800 dark:text-white/90 mb-4">{{ __('ui.seo_settings') }}</h3>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                    <!-- Meta Title ar -->
                    <x-dashboard.inputs.default
                        name="meta_title"
                        locale="ar"
                        id="service-meta-title-ar"
                        :required="false" />

                    <!-- Meta Description ar -->
                    <div>
                        <x-dashboard.label name="meta_description" locale="ar" :required="false" />
                        <textarea
                            name="meta_description[ar]"
                            id="service-meta-description-ar"
                            rows="2"
                            class="dark:bg-dark-900 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 dark:focus:border-brand-800">{{ old('meta_description.ar') }}</textarea>
                        @error('meta_description.ar')
                            <x-dashboard.inputs.error :message="$message" />
                        @enderror
                    </div>
                </div>
            </div>

            <div class="mt-5">
                <!-- Submit button -->
                <x-dashboard.buttons.primary :name="__('ui.add')" />
            </div>

        </form>
    </div>

    @vite('resources/js/file-input.js')

</x-layouts.dashboard>
