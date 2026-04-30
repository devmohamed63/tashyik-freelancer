<div>
    <div class="space-y-5">

        <!-- Modal loader -->
        <x-dashboard.loaders.centered wire:loading.class.remove="hidden" />

        <!-- Modal title -->
        <x-dashboard.modals.title :value="__('ui.add_city')" />

        <form class="grid md:grid-cols-2 gap-5" wire:submit="store" x-data="{ loading: false }">

            <div class="col-span-full">
                <!-- Name ar -->
                <x-dashboard.inputs.default
                    name="name"
                    wire:model="name.ar"
                    locale="ar"
                    :required="true" />
            </div>

            <!-- Submit button -->
            <x-dashboard.buttons.primary :name="__('ui.add')" />

        </form>

    </div>
</div>
