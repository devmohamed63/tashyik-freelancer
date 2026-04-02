<x-layouts.dashboard page="orders">

    <x-dashboard.breadcrumb :page="__('ui.orders')" />

    <div class="grid grid-cols-2 lg:grid-cols-4 gap-5 mb-5 mt-5">
        <x-dashboard.cards.overview
            style="col"
            :index="6"
            :title="__('ui.new_orders')"
            :count="\App\Models\Order::isNew()->count()"
            :authorize="Illuminate\Support\Facades\Gate::allows('manage orders')"
            :link="route('dashboard.orders.index', ['statusFilter' => App\Models\Order::NEW_STATUS])">
            <!-- ic:sharp-home-repair-service -->
            <!-- Icon from Google Material Icons by Material Design Authors - https://github.com/material-icons/material-icons/blob/master/LICENSE -->
            <path fill="currentColor" d="M18 16h-2v-1H8v1H6v-1H2v5h20v-5h-4zm-1-8V4H7v4H2v6h4v-2h2v2h8v-2h2v2h4V8zM9 6h6v2H9z" />
        </x-dashboard.cards.overview>

        <x-dashboard.cards.overview
            style="col"
            :index="8"
            :title="__('ui.completed_orders')"
            :count="\App\Models\Order::completed()->count()"
            :authorize="Illuminate\Support\Facades\Gate::allows('manage orders')"
            :link="route('dashboard.orders.index', ['statusFilter' => App\Models\Order::COMPLETED_STATUS])">
            <!-- material-symbols:star-shine-rounded -->
            <!-- Icon from Material Symbols by Google - https://github.com/google/material-design-icons/blob/master/LICENSE -->
            <path fill="currentColor" d="M19 15q.3-.3.713-.3t.712.3L22 16.6q.3.3.3.7t-.3.7t-.7.3t-.7-.3L19 16.425q-.3-.3-.3-.712T19 15m1-12q.3.3.3.713t-.3.712L18.425 6q-.3.3-.712.3T17 6t-.3-.712t.3-.713L18.6 3q.3-.3.7-.3t.7.3M4 3q.3-.3.713-.3t.712.3L7 4.6q.3.3.3.7T7 6t-.712.3t-.713-.3L4 4.425q-.3-.3-.3-.712T4 3m1 12q.3.3.3.713t-.3.712L3.425 18q-.3.3-.712.3T2 18t-.3-.712t.3-.713L3.6 15q.3-.3.7-.3t.7.3m7 2.275l-4.15 2.5q-.275.175-.575.15t-.525-.2t-.35-.437t-.05-.588l1.1-4.725L3.775 10.8q-.25-.225-.312-.513t.037-.562t.3-.45t.55-.225l4.85-.425l1.875-4.45q.125-.3.388-.45t.537-.15t.537.15t.388.45l1.875 4.45l4.85.425q.35.05.55.225t.3.45t.038.563t-.313.512l-3.675 3.175l1.1 4.725q.075.325-.05.588t-.35.437t-.525.2t-.575-.15z" />
        </x-dashboard.cards.overview>
    </div>

    <livewire:dashboard.orders-table />

</x-layouts.dashboard>
