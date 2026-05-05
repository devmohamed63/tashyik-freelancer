<div>
    <div class="space-y-5">

        <!-- Modal loader -->
        <x-dashboard.loaders.centered wire:loading.class.remove="hidden" />

        <!-- Modal title -->
        <x-dashboard.modals.title :value="__('ui.edit_city')" />

        <form class="grid md:grid-cols-2 gap-5" wire:submit="update" x-data="{ loading: false }">

            <!-- Name ar -->
            <x-dashboard.inputs.default
                name="name"
                wire:model="name.ar"
                locale="ar"
                :required="true" />

            <!-- Name en -->
            <x-dashboard.inputs.default
                name="name"
                wire:model="name.en"
                locale="en"
                :required="true" />

            <!-- Submit button -->
            <x-dashboard.buttons.primary :name="__('ui.update')" />

        </form>

    </div>
</div>
