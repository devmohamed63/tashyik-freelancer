<x-layouts.guest page="login">

    @session('status')
        <x-dashboard.alerts.success class="mb-4" :message="$value" />
    @endsession

    <form
        x-data="{ loading: false }"
        x-on:submit="loading = true"
        action="{{ route('login') }}"
        method="POST">

        @csrf

        <div class="space-y-5">

            <!-- Phone -->
            <x-dashboard.inputs.default
                name="phone"
                type="text"
                :required="true" />

            <!-- Password -->
            <x-dashboard.inputs.password
                name="password"
                :required="true" />

            <!-- Remember -->
            <div class="flex items-center justify-between">
                <x-dashboard.inputs.checkbox name="remember_me" />

                @if (Route::has('password.request'))
                    <a href="{{ route('password.request') }}" class="text-sm text-brand-500 hover:text-brand-600 dark:text-brand-400">{{ __('auth.login.forgot_password') }}</a>
                @endif
            </div>

            <!-- Submit button -->
            <x-dashboard.buttons.primary class="w-full" :name="__('auth.login.button')" />

        </div>
    </form>

    @if (Route::has('register'))
        <div class="mt-5">
            <p class="text-sm font-normal text-center text-gray-700 dark:text-gray-400 sm:text-start">
                {{ __('auth.login.dont_have_account') }}
                <a href="{{ route('register') }}" class="text-brand-500 hover:text-brand-600 dark:text-brand-400">{{ __('auth.register.title') }}</a>
            </p>
        </div>
    @endif

</x-layouts.guest>
