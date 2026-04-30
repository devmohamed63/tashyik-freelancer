<div>
    <div class="space-y-5">

        <!-- Modal loader -->
        <x-dashboard.loaders.centered wire:loading.class.remove="hidden" />

        <!-- Modal title -->
        <x-dashboard.modals.title :value="__('ui.show_order')" />

        <div class="max-w-xl grid grid-cols-1 sm:grid-cols-2 gap-5">

            <!-- Order location -->
            <x-dashboard.info-label :name="__('ui.order_location')">
                @if ($order?->latitude && $order?->longitude)
                    <a href="https://maps.google.com/maps?q={{ $order?->latitude }},{{ $order?->longitude }}" target="_blank" class="text-brand-400 hover:text-brand-500">{{ __('ui.view_on_map') }}</a>
                @else
                    {{ __('ui.none') }}
                @endif
            </x-dashboard.info-label>

            <!-- Customer name -->
            <x-dashboard.info-label
                :name="__('ui.customer_name')"
                :value="$order?->customer?->name ?? __('ui.none')" />

            <!-- Customer phone -->
            <x-dashboard.info-label
                :name="__('ui.customer_phone')"
                :value="$order?->customer?->phone ?? __('ui.none')" />

            <!-- Service provider name -->
            <x-dashboard.info-label
                :name="__('ui.service_provider_name')"
                :value="$order?->serviceProvider?->name ?? __('ui.none')" />

            <!-- Service provider phone -->
            <x-dashboard.info-label
                :name="__('ui.service_provider_phone')"
                :value="$order?->serviceProvider?->phone ?? __('ui.none')" />

        </div>

    </div>
</div>
