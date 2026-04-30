<x-layouts.guest page="reset_password">

    @session('status')
        <x-dashboard.alerts.success class="mb-4" :message="$value" />
    @endsession

    <form
        x-data="{ loading: false }"
        x-on:submit="loading = true"
        action="{{ route('password.store') }}"
        method="POST">

        @csrf

        <div class="space-y-5">
            <!-- Password -->
            <x-dashboard.inputs.password
                name="password"
                :required="true" />

            <!-- Confirm Password -->
            <x-dashboard.inputs.password
                name="password_confirmation"
                :required="true" />

            <!-- Submit button -->
            <x-dashboard.buttons.primary class="w-full" :name="__('auth.reset_password.button')" />
        </div>
    </form>

</x-layouts.guest>
