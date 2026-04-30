<x-layouts.dashboard page="notifications">

    <x-dashboard.breadcrumb :page="__('ui.notifications')" />

    @include('dashboard.notifications.partials.notifications-list')

    <div class="p-2">
        {{ $notifications->onEachSide(1)->links() }}
    </div>

</x-layouts.dashboard>
