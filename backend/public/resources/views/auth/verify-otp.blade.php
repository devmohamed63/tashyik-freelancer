<x-layouts.guest page="verify-otp">

    @session('status')
        <x-dashboard.alerts.success class="mb-4" :message="$value" />
    @endsession

    <form
        x-data="{ loading: false }"
        x-on:submit="loading = true"
        action="{{ route('dashboard.password.otp.verify') }}"
        method="POST">

        @csrf

        <div class="space-y-5">
            <!-- OTP -->
            <x-dashboard.inputs.default
                name="otp"
                type="text"
                :label="__('رمز OTP')"
                :required="true" />

            <div class="flex items-center justify-between">
                <a href="{{ route('password.request') }}" class="text-sm text-brand-500 hover:text-brand-600 dark:text-brand-400">تغيير البريد الإلكتروني؟</a>
            </div>

            <!-- Submit button -->
            <x-dashboard.buttons.primary class="w-full" :name="__('تحقق واتبع')" />
        </div>
    </form>

</x-layouts.guest>
