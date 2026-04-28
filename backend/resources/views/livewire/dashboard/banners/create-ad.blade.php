<div>
    <div class="space-y-5">

        <!-- Modal loader -->
        <x-dashboard.loaders.centered wire:loading.class.remove="hidden" />

        @if ($embedInModal)
            <!-- Modal title -->
            <x-dashboard.modals.title :value="__('ui.create_ad')" />
        @endif

        @if ($error)
            <x-dashboard.alerts.error :title="$error" />
        @endif

        <form class="space-y-5" wire:submit="publish" x-data="{ loading: false }">
            <!-- Audience (one or more) -->
            <div class="flex flex-col gap-2">
                <span class="mb-1.5 text-sm font-medium text-gray-700 dark:text-gray-400 inline-flex items-center">
                    {{ __('ui.target_audiences') }}<span class="text-error-500">*</span>
                </span>

                <div class="flex flex-wrap gap-4 items-center">
                    <x-dashboard.inputs.checkbox
                        id="audience_customers"
                        name="audiences[]"
                        wire:model="audiences"
                        type="checkbox"
                        value="customers"
                        :title="__('ui.customers')" />

                    <x-dashboard.inputs.checkbox
                        id="audience_service_providers"
                        name="audiences[]"
                        wire:model="audiences"
                        type="checkbox"
                        value="service_providers"
                        :title="__('ui.service_providers')" />

                    <x-dashboard.inputs.checkbox
                        id="audience_guests"
                        name="audiences[]"
                        wire:model="audiences"
                        type="checkbox"
                        value="guests"
                        :title="$this->guestsAudienceLabel()" />
                </div>

                @error('audiences')
                    <x-dashboard.inputs.error :message="$message" />
                @enderror
                @foreach ($errors->keys() as $errorKey)
                    @if (str_starts_with($errorKey, 'audiences.'))
                        @error($errorKey)
                            <x-dashboard.inputs.error :message="$message" />
                        @enderror
                    @endif
                @endforeach
            </div>

            <!-- Title -->
            <x-dashboard.inputs.default
                name="title"
                wire:model="title"
                :required="true" />

            <!-- Description -->
            <x-dashboard.inputs.textarea
                name="description"
                wire:model="description" />

            <!-- Image -->
            <div class="flex flex-col gap-2 relative">
                @if ($image && !$errors->has('image'))
                    <img src="{{ $image->temporaryUrl() }}" class="rounded-lg w-full aspect-video object-contain bg-gray-100">
                @endif

                <x-dashboard.inputs.file.default
                    name="image"
                    input-name="image"
                    wire:model="image"
                    accept=".webp, .png, .jpg, .jpeg"
                    :image-description="true" />

                <x-dashboard.loaders.centered wire:loading.class.remove="hidden" wire:target="image" />
            </div>

            <!-- Submit button -->
            <x-dashboard.buttons.primary :name="__('ui.publish')" />
        </form>

    </div>
</div>
