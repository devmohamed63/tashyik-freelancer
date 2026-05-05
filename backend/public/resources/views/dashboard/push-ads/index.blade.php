<x-layouts.dashboard page="view_push_ads">

    <x-dashboard.breadcrumb :page="__('ui.view_push_ads')" />

    @session('status')
        <div class="mb-5">
            <x-dashboard.alerts.success :title="$value" />
        </div>
    @endsession

    <livewire:dashboard.ad-broadcasts-table />

</x-layouts.dashboard>
