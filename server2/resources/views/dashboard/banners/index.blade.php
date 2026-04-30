<x-layouts.dashboard page="view_banners">

    <x-dashboard.breadcrumb :page="__('ui.view_banners')" />

    @session('status')
        <div class="mb-5">
            <x-dashboard.alerts.success :title="$value" />
        </div>
    @endsession

    <livewire:dashboard.banners-table />

</x-layouts.dashboard>
