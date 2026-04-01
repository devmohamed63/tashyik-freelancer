<div>
    <div class="space-y-5">

        <!-- Modal loader -->
        <x-dashboard.loaders.centered wire:loading.class.remove="hidden" />

        <!-- Modal title -->
        <x-dashboard.modals.title :value="__('ui.show_user')" />

        <div class="space-y-5">

            @include('livewire.dashboard.users.partials.basic-information')

            @if ($isServiceProvider)
                @include('livewire.dashboard.users.partials.service-provider-information')
            @endif

        </div>
    </div>
</div>
