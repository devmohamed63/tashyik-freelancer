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

            <div class="mt-5">
                <!-- Submit button -->
                <x-dashboard.buttons.primary :name="__('ui.add')" />
            </div>

        </form>
    </div>

    @vite('resources/js/file-input.js')

</x-layouts.dashboard>
