<div>
    <div class="space-y-5">

        <!-- Modal loader -->
        <x-dashboard.loaders.centered wire:loading.class.remove="hidden" />

        <!-- Modal title -->
        <x-dashboard.modals.title :value="__('ui.edit_plan')" />

        <form class="flex flex-col md:grid grid-cols-2 gap-5" wire:submit="update" x-data="{ loading: false }">

            <!-- Name -->
            <x-dashboard.inputs.default
                name="name"
                id="edit-plan-name"
                wire:model="name"
                :required="true" />

            <!-- Target group -->
            <x-dashboard.inputs.livewire-select
                name="target_group"
                wire:model="target_group"
                :required="true">
                <option value="">{{ __('ui.select_target_group') }}</option>
                @foreach ($this->targetGroups as $targetGroup)
                    <option value="{{ $targetGroup }}">{{ __('ui.' . $targetGroup) }}</option>
                @endforeach
            </x-dashboard.inputs.livewire-select>

            <!-- Price -->
            <x-dashboard.inputs.default
                name="price"
                id="edit-plan-price"
                wire:model="price"
                type="number"
                min="0"
                :required="true" />

            <!-- Duration in months -->
            <x-dashboard.inputs.default
                name="duration_in_months"
                id="edit-plan-duration_in_months"
                wire:model="duration_in_months"
                type="number"
                min="1"
                :required="true" />

            <!-- Badge -->
            <x-dashboard.inputs.livewire-select
                name="badge"
                wire:model="badge"
                :required="false">
                <option value="">{{ __('ui.no_badge') }}</option>
                @foreach (App\Models\Plan::BADGES as $key => $title)
                    <option value="{{ $key }}">{{ $title }}</option>
                @endforeach
            </x-dashboard.inputs.livewire-select>

            <!-- Selected Categories -->
            <div class="col-span-full">
                <x-dashboard.inputs.select
                    label="categories"
                    id="edit-plan-categories"
                    name="selectedCategories[]"
                    wire:model="selectedCategories"
                    :children="$this->categoriesList"
                    child-key="id"
                    :selected="$selectedCategories"
                    :required="false" />
            </div>

            <!-- Features -->
            <div class="col-span-full mt-2 flex flex-col gap-2 rounded-lg border border-gray-300 dark:border-gray-600 border-dashed p-4">
                <x-dashboard.label name="features" locale="ar" :required="false" />

                @error('features')
                    <x-dashboard.inputs.error :message="$message" />
                @enderror

                @foreach($features as $index => $feature)
                    <div class="relative">
                        <x-dashboard.inputs.default
                            name="features.{{ $index }}"
                            id="edit-plan-feature-{{ $index }}"
                            wire:model="features.{{ $index }}"
                            :required="false"
                            :label="false" />
                        
                        <button type="button" wire:click="removeFeature({{ $index }})" class="absolute end-2 bottom-2 w-7 h-7 rounded-md bg-red-50 text-red-600 flex items-center justify-center">
                            <!-- uil:trash-alt -->
                            <svg class="w-4 h-4" xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24"><path fill="currentColor" d="M10 18a1 1 0 0 0 1-1v-6a1 1 0 0 0-2 0v6a1 1 0 0 0 1 1M20 6h-4V5a3 3 0 0 0-3-3h-2a3 3 0 0 0-3 3v1H4a1 1 0 0 0 0 2h1v11a3 3 0 0 0 3 3h8a3 3 0 0 0 3-3V8h1a1 1 0 0 0 0-2M10 5a1 1 0 0 1 1-1h2a1 1 0 0 1 1 1v1h-4Zm7 14a1 1 0 0 1-1 1H8a1 1 0 0 1-1-1V8h10Zm-3-1a1 1 0 0 0 1-1v-6a1 1 0 0 0-2 0v6a1 1 0 0 0 1 1" /></svg>
                        </button>
                    </div>
                @endforeach
                
                <div class="mt-3">
                    <x-dashboard.buttons.secondary :name="__('ui.add_more')" wire:click="addFeature" type="button" />
                </div>
            </div>

            <!-- Submit button -->
            <x-dashboard.buttons.primary :name="__('ui.edit')" />

        </form>

    </div>
</div>
