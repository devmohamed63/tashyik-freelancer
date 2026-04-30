<x-layouts.guest page="forgot_passwrod">

    @session('status')
        <x-dashboard.alerts.success class="mb-4" :message="$value" />
    @endsession

    <form
        x-data="{ loading: false }"
        x-on:submit="loading = true"
        action="{{ route('password.email') }}"
        method="POST">

        @csrf

        <div class="space-y-5">
            <!-- Email -->
            <x-dashboard.inputs.default
                name="email"
                type="email"
                :label="__('auth.email') ?? 'البريد الإلكتروني'"
                :required="true" />

            <div class="flex items-center justify-between">
                <a href="{{ route('login') }}" class="text-sm text-brand-500 hover:text-brand-600 dark:text-brand-400">{{ __('auth.back_home') }}</a>
            </div>

            <!-- Submit button -->
            <x-dashboard.buttons.primary class="w-full" :name="__('auth.forgot_passwrod.button')" />
        </div>
    </form>

</x-layouts.guest>
