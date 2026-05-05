<div>
    <div class="space-y-5">

        @if ($fullPage)
            <x-dashboard.breadcrumb :page="__('ui.add_service_provider')" />

            {{-- Entity Type Selector --}}
            <div class="grid grid-cols-2 gap-4 mb-6">
                <a href="{{ route('dashboard.users.create_service_provider', ['type' => 'institution']) }}"
                   class="rounded-xl border-2 p-5 text-center transition-all hover:shadow-md {{ $entityTypeFilter === 'institution' ? 'border-indigo-500 bg-indigo-50 dark:bg-indigo-900/20 dark:border-indigo-400' : 'border-gray-200 dark:border-gray-700 bg-white dark:bg-white/[0.03] hover:border-indigo-300 dark:hover:border-indigo-600' }}">
                    <svg class="w-10 h-10 mx-auto mb-2 {{ $entityTypeFilter === 'institution' ? 'text-indigo-600 dark:text-indigo-400' : 'text-gray-400' }}" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path fill="currentColor" d="M12 7V3H2v18h20V7zM6 19H4v-2h2zm0-4H4v-2h2zm0-4H4V9h2zm0-4H4V5h2zm4 12H8v-2h2zm0-4H8v-2h2zm0-4H8V9h2zm0-4H8V5h2zm10 12h-8v-2h2v-2h-2v-2h2v-2h-2V9h8zm-2-8h-2v2h2zm0 4h-2v2h2z"/></svg>
                    <p class="font-semibold {{ $entityTypeFilter === 'institution' ? 'text-indigo-700 dark:text-indigo-300' : 'text-gray-700 dark:text-gray-300' }}">{{ __('ui.institution') }}</p>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">{{ __('ui.add_to_institution') }}</p>
                </a>
                <a href="{{ route('dashboard.users.create_service_provider', ['type' => 'company']) }}"
                   class="rounded-xl border-2 p-5 text-center transition-all hover:shadow-md {{ $entityTypeFilter === 'company' ? 'border-teal-500 bg-teal-50 dark:bg-teal-900/20 dark:border-teal-400' : 'border-gray-200 dark:border-gray-700 bg-white dark:bg-white/[0.03] hover:border-teal-300 dark:hover:border-teal-600' }}">
                    <svg class="w-10 h-10 mx-auto mb-2 {{ $entityTypeFilter === 'company' ? 'text-teal-600 dark:text-teal-400' : 'text-gray-400' }}" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path fill="currentColor" d="M18 15h-2v2h2m0-6h-2v2h2m2 6h-8v-2h2v-2h-2v-2h2v-2h-2V9h8M10 7H8V5h2m0 6H8V9h2m0 6H8v-2h2m0 6H8v-2h2M6 7H4V5h2m0 6H4V9h2m0 6H4v-2h2m0 6H4v-2h2m6-10V3H2v18h20V7z"/></svg>
                    <p class="font-semibold {{ $entityTypeFilter === 'company' ? 'text-teal-700 dark:text-teal-300' : 'text-gray-700 dark:text-gray-300' }}">{{ __('ui.company') }}</p>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">{{ __('ui.add_to_company') }}</p>
                </a>
            </div>
        @else
            <!-- Modal loader -->
            <x-dashboard.loaders.centered wire:loading.class.remove="hidden" />

            <!-- Modal title -->
            <x-dashboard.modals.title :value="__('ui.add_service_provider')" />
        @endif

        @if ($fullPage && !$entityTypeFilter)
            {{-- Show message to select type first --}}
            <div class="rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-white/[0.03] p-8 text-center">
                <svg class="w-16 h-16 mx-auto mb-4 text-gray-300 dark:text-gray-600" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path fill="currentColor" d="M12 5.5A3.5 3.5 0 0 1 15.5 9a3.5 3.5 0 0 1-3.5 3.5A3.5 3.5 0 0 1 8.5 9A3.5 3.5 0 0 1 12 5.5M5 8c.56 0 1.08.15 1.53.42c-.15 1.43.27 2.85 1.13 3.96C7.16 13.34 6.16 14 5 14a3 3 0 0 1-3-3a3 3 0 0 1 3-3m14 0a3 3 0 0 1 3 3a3 3 0 0 1-3 3c-1.16 0-2.16-.66-2.66-1.62a5.54 5.54 0 0 0 1.13-3.96c.45-.27.97-.42 1.53-.42M5.5 18.25c0-2.07 2.91-3.75 6.5-3.75s6.5 1.68 6.5 3.75V20h-13zM0 20v-1.5c0-1.39 1.89-2.56 4.45-2.9c-.59.68-.95 1.62-.95 2.65V20zm24 0h-3.5v-1.75c0-1.03-.36-1.97-.95-2.65c2.56.34 4.45 1.51 4.45 2.9z"/></svg>
                <p class="text-gray-500 dark:text-gray-400 text-lg">{{ __('ui.select_entity_type_first') }}</p>
            </div>
        @else
            <form class="flex flex-col gap-5 md:grid grid-cols-2 {{ $fullPage ? 'rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-white/[0.03] p-6' : '' }}" wire:submit="store" x-data="{ loading: false }">

                @if ($fullPage)
                    <h3 class="col-span-full text-lg font-semibold text-gray-800 dark:text-white border-b border-gray-200 dark:border-gray-700 pb-3 mb-2">
                        {{ $entityTypeFilter === 'institution' ? __('ui.add_to_institution') : __('ui.add_to_company') }}
                    </h3>
                @endif

                <x-dashboard.inputs.livewire-select
                    name="institution"
                    wire:model="institution"
                    :required="true">
                    <option value="">{{ $entityTypeFilter === 'company' ? __('ui.select_company') : __('ui.select_institution') }}</option>
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
        @endif

    </div>
</div>
