<div>
    <div class="space-y-5">

        <!-- Modal loader -->
        <x-dashboard.loaders.centered wire:loading.class.remove="hidden" />

        <!-- Modal title -->
        <x-dashboard.modals.title :value="__('ui.create_ad')" />

        @if ($error)
            <x-dashboard.alerts.error :title="$error" />
        @endif

        <form class="space-y-5" wire:submit="publish" x-data="{ loading: false }">
            <!-- Audience -->
            <div class="flex flex-col gap-2">
                <x-dashboard.label name="audience" :required="true" />

                <div class="inline-flex gap-4 items-center w-full">
                    <x-dashboard.inputs.checkbox
                        id="customer"
                        name="audience"
                        wire:model="audience"
                        type="radio"
                        value="customers"
                        :title="__('ui.customers')"
                        required />

                    <x-dashboard.inputs.checkbox
                        id="service_provider"
                        name="audience"
                        wire:model="audience"
                        type="radio"
                        value="service_providers"
                        :title="__('ui.service_providers')"
                        required />

                    <x-dashboard.inputs.checkbox
                        id="guest"
                        name="audience"
                        wire:model="audience"
                        type="radio"
                        value="guests"
                        :title="__('ui.guests')"
                        required />
                </div>

                @error('audience')
                    <x-dashboard.inputs.error :message="$message" />
                @enderror
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
