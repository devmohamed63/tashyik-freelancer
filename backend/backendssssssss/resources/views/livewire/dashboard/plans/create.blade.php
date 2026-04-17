<div>
    <div class="space-y-5">

        <!-- Modal loader -->
        <x-dashboard.loaders.centered wire:loading.class.remove="hidden" />

        <!-- Modal title -->
        <x-dashboard.modals.title :value="__('ui.add_plan')" />

        <form class="flex flex-col md:grid grid-cols-2 gap-5" wire:submit="store" x-data="{ loading: false }">

            <!-- Name -->
            <x-dashboard.inputs.default
                name="name"
                id="plan-name"
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
                id="plan-price"
                wire:model="price"
                type="number"
                min="0"
                :required="true" />

            <!-- Duration in months -->
            <x-dashboard.inputs.default
                name="duration_in_months"
                id="plan-duration_in_months"
                wire:model="duration_in_months"
                type="number"
                min="1"
                :required="true" />

            <!-- Submit button -->
            <x-dashboard.buttons.primary :name="__('ui.add')" />

        </form>

    </div>
</div>
