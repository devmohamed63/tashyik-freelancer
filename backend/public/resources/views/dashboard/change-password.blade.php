<x-layouts.dashboard page="change_password">

    <x-dashboard.breadcrumb :page="__('ui.change_password')" />

    @session('status')
        <div class="mb-5">
            <x-dashboard.alerts.success :title="$value" />
        </div>
    @endsession

    @session('error')
        <div class="mb-5">
            <x-dashboard.alerts.error :title="$value" />
        </div>
    @endsession

    <div class="relative overflow-hidden rounded-xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03] p-5 max-w-xl">

        <form
            x-data="{ loading: false }"
            x-on:submit="loading = true"
            method="POST"
            action="{{ route('dashboard.change_password.update') }}"
            class="grid grid-cols-1 gap-5">

            @method('PUT')
            @csrf

            <!-- Current Password -->
            <x-dashboard.inputs.password
                name="current_password"
                :required="true" />

            <!-- New Password -->
            <x-dashboard.inputs.password
                name="password"
                :required="true" />

            <!-- Confirm New Password -->
            <x-dashboard.inputs.password
                name="password_confirmation"
                :required="true" />

            <!-- Submit button -->
            <div>
                <x-dashboard.buttons.primary :name="__('ui.update')" />
            </div>

        </form>
    </div>

</x-layouts.dashboard>
