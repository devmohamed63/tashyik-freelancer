<div>
    <div class="space-y-5">

        <!-- Modal loader -->
        <x-dashboard.loaders.centered wire:loading.class.remove="hidden" />

        <!-- Modal title -->
        <x-dashboard.modals.title :value="__('ui.create_service_collection')" />

        <form class="grid md:grid-cols-2 gap-5" wire:submit="store" x-data="{ loading: false }">

            <!-- Title -->
            <x-dashboard.inputs.default
                name="title"
                locale="ar"
                wire:model="title"
                :required="true"
                autocomplete="off" />

            <!-- Target -->
            <div class="flex flex-col gap-2 col-span-full">
                <x-dashboard.label name="generate_from" :required="true" />
                <div class="inline-flex gap-4 items-center w-full">
                    @foreach ($availableTargets as $item)
                        <x-dashboard.inputs.checkbox
                            :id="$item['value']"
                            name="target"
                            wire:model.live="target"
                            type="radio"
                            :value="$item['value']"
                            :title="$item['label']"
                            required />
                    @endforeach
                </div>
            </div>

            <div class="col col-span-full">

                @switch($target)
                    @case(\App\Models\Coupon::CATEGORIES_TARGET_TYPE)
                        <div class="flex flex-col gap-4 p-4 border border-dashed border-gray-300 mb-5 rounded-lg">
                            <x-dashboard.label name="categories" :required="true" />
                            <div class="flex flex-wrap gap-4">
                                @foreach ($categories as $category)
                                    <x-dashboard.inputs.checkbox
                                        :id="'category-' . $category->id"
                                        :title="$category->name"
                                        name="selectedCategories"
                                        wire:model="selectedCategories"
                                        :value="$category->id" />
                                @endforeach
                            </div>
                            @error('selectedCategories')
                                <x-dashboard.inputs.error :message="$message" />
                            @enderror
                        </div>
                    @break

                    @case(\App\Models\Coupon::SUBCATEGORIES_TARGET_TYPE)
                        <div class="flex flex-col gap-4 p-4 border border-dashed border-gray-300 mb-5 rounded-lg">
                            <x-dashboard.label name="category" :required="true" />
                            <div class="flex flex-wrap gap-4">
                                @foreach ($categories as $category)
                                    <x-dashboard.inputs.checkbox
                                        :id="'categories1-' . $category->id"
                                        :title="$category->name"
                                        name="selectedCategory"
                                        wire:model.live="selectedCategory"
                                        type="radio"
                                        :value="$category->id"
                                        required />
                                @endforeach
                            </div>
                        </div>

                        @if ($selectedCategory)
                            <div class="flex flex-col gap-4 p-4 border border-dashed border-gray-300 mb-5 rounded-lg">
                                <x-dashboard.label name="subcategories" :required="true" />
                                <div class="flex flex-wrap gap-4">
                                    @foreach ($subcategories as $subcategory)
                                        <x-dashboard.inputs.checkbox
                                            :id="'subcategory1-' . $subcategory->id"
                                            :title="$subcategory->name"
                                            name="selectedSubcategories"
                                            wire:model="selectedSubcategories"
                                            :value="$subcategory->id" />
                                    @endforeach
                                </div>
                                @error('selectedSubcategories')
                                    <x-dashboard.inputs.error :message="$message" />
                                @enderror
                            </div>
                        @endif
                    @break

                    @case(\App\Models\Coupon::SERVICES_TARGET_TYPE)
                        <div class="flex flex-col gap-4 p-4 border border-dashed border-gray-300 mb-5 rounded-lg">
                            <x-dashboard.label name="category" :required="true" />
                            <div class="flex flex-wrap gap-4">
                                @foreach ($categories as $category)
                                    <x-dashboard.inputs.checkbox
                                        :id="'categories2-' . $category->id"
                                        :title="$category->name"
                                        name="selectedCategory"
                                        wire:model.live="selectedCategory"
                                        type="radio"
                                        :value="$category->id"
                                        required />
                                @endforeach
                            </div>
                        </div>

                        @if ($selectedCategory)
                            <div class="flex flex-col gap-4 p-4 border border-dashed border-gray-300 mb-5 rounded-lg">
                                <x-dashboard.label name="subcategory" :required="true" />
                                <div class="flex flex-wrap gap-4">
                                    @foreach ($subcategories as $subcategory)
                                        <x-dashboard.inputs.checkbox
                                            :id="'subcategories2-' . $subcategory->id"
                                            :title="$subcategory->name"
                                            name="selectedSubcategory"
                                            wire:model.live="selectedSubcategory"
                                            type="radio"
                                            :value="$subcategory->id"
                                            required />
                                    @endforeach
                                </div>
                            </div>
                        @endif

                        @if ($selectedSubcategory)
                            <div class="flex flex-col gap-4 p-4 border border-dashed border-gray-300 mb-5 rounded-lg">
                                <x-dashboard.label name="services" :required="true" />
                                <div class="flex flex-wrap gap-4">
                                    @foreach ($services as $service)
                                        <x-dashboard.inputs.checkbox
                                            :id="'services3-' . $service->id"
                                            :title="$service->name"
                                            name="selectedServices"
                                            wire:model="selectedServices"
                                            :value="$service->id" />
                                    @endforeach
                                </div>
                                @error('selectedServices')
                                    <x-dashboard.inputs.error :message="$message" />
                                @enderror
                            </div>
                        @endif
                    @break

                @endswitch

            </div>

            <div class="col-span-full">
                <!-- Submit button -->
                <x-dashboard.buttons.primary type="submit" :name="__('ui.create')" />
            </div>

        </form>

    </div>
</div>
