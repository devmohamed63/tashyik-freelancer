<div>
    <div class="space-y-5">

        <!-- Modal loader -->
        <x-dashboard.loaders.centered wire:loading.class.remove="hidden" />

        <!-- Modal title -->
        <x-dashboard.modals.title :value="__('ui.add_service_provider')" />

        <form class="flex flex-col gap-5 md:grid grid-cols-2" wire:submit="store" x-data="{ loading: false }">

            <x-dashboard.inputs.livewire-select
                name="institution"
                wire:model="institution"
                :required="true">
                <option value="">{{ __('ui.select_institution') }}</option>
                @foreach ($this->institutions as $id => $name)
                    <option value="{{ $id }}">{{ $name }}</option>
                @endforeach
            </x-dashboard.inputs.livewire-select>

            <!-- Name -->
            <x-dashboard.inputs.default
                name="name"
                wire:model="name"
                :required="true" />

            <!-- Email -->
            <x-dashboard.inputs.default
                name="email"
                wire:model="email"
                type="email" />

            <!-- City -->
            <x-dashboard.inputs.livewire-select
                name="city"
                wire:model="city"
                :required="true">
                <option value="">{{ __('ui.select_city') }}</option>
                @foreach ($this->cities as $id => $name)
                    <option value="{{ $id }}">{{ $name }}</option>
                @endforeach
            </x-dashboard.inputs.livewire-select>

            <!-- Phone -->
            <x-dashboard.inputs.default
                name="phone"
                wire:model="phone"
                :required="true" />

            <!-- Password -->
            <x-dashboard.inputs.default
                name="password"
                wire:model="password"
                :required="true" />

            <!-- Categories -->
            <div class="flex flex-col gap-3 col-span-full p-4 rounded-xl border border-gray-300 dark:border-gray-600 border-dashed">
                <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-400">{{ __('ui.categories') }}</label>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    @foreach ($this->categories as $id => $name)
                        <x-dashboard.inputs.checkbox
                            :id="'category' . $id"
                            :value="$id"
                            wire:model="selectedCategories"
                            :title="$name" />
                    @endforeach
                </div>
                @error('selectedCategories')
                    <x-dashboard.inputs.error :message="$message" />
                @enderror
            </div>

            <!-- Residence name -->
            <x-dashboard.inputs.default
                name="residence_name"
                wire:model="residence_name"
                :required="true" />

            <!-- Residence number -->
            <x-dashboard.inputs.default
                name="residence_number"
                wire:model="residence_number"
                :required="true" />

            <!-- Residence image -->
            <div class="block">
                <x-dashboard.inputs.file.default
                    id="residence_image"
                    name="residence_image"
                    input-name="residence_image"
                    wire:model="residence_image"
                    accept=".webp, .png, .jpg, .jpeg"
                    :image-description="true"
                    :required="true" />

                <x-dashboard.loaders.centered
                    wire:loading.class.remove="hidden"
                    wire:target="residence_image" />
            </div>

            <!-- Bank name -->
            <x-dashboard.inputs.default
                name="bank_name"
                wire:model="bank_name"
                :required="true" />

            <!-- IBAN -->
            <x-dashboard.inputs.default
                name="iban"
                wire:model="iban"
                :required="true" />

            <!-- Submit button -->
            <div class="col-span-full">
                <x-dashboard.buttons.primary :name="__('ui.add')" />
            </div>

        </form>

    </div>
</div>
