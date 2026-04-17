<x-layouts.dashboard page="change_email">

    <x-dashboard.breadcrumb :page="__('ui.change_email')" />

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
            action="{{ route('dashboard.change_email.update') }}"
            class="grid grid-cols-1 gap-5">

            @method('PUT')
            @csrf

            <!-- New Email -->
            <x-dashboard.inputs.default
                name="email"
                type="email"
                :label="__('auth.email') ?? 'البريد الإلكتروني الجديد'"
                :value="auth()->user()->email"
                :required="true" />

            <!-- Submit button -->
            <div>
                <x-dashboard.buttons.primary :name="__('ui.update')" />
            </div>

        </form>
    </div>

</x-layouts.dashboard>
