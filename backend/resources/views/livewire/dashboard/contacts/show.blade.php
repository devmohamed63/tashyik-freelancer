<div>
    <div class="space-y-5">

        <!-- Modal loader -->
        <x-dashboard.loaders.centered wire:loading.class.remove="hidden" />

        <!-- Modal title -->
        <x-dashboard.modals.title :value="__('ui.show_contact_request')" />

        <div class="space-y-5">

            <!-- Subject -->
            <x-dashboard.info-label :name="__('validation.attributes.subject')" :value="$contact?->subject" />

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">

                <!-- Name -->
                <x-dashboard.info-label :name="__('validation.attributes.name')" :value="$contact?->name" />

                <!-- Phone -->
                <x-dashboard.info-label :name="__('validation.attributes.phone')" :value="$contact?->phone" />

                <!-- Date -->
                <x-dashboard.info-label :name="__('ui.created_at')" :value="$date" />

            </div>

            <x-dashboard.ui.hr />

            <!-- Message -->
            <x-dashboard.info-label :name="__('validation.attributes.message')" :value="$contact?->message" />

        </div>
    </div>
</div>
