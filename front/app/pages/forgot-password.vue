<script setup>
  const { t } = useI18n();
  const localePath = useLocalePath();
  const loader = ref(false);
  const errors = ref({});
  const step = ref(1); // 1: email, 2: otp, 3: new password
  const successMessage = ref('');

  const form = reactive({
    email: '',
    otp: '',
    password: '',
    password_confirmation: '',
  });

  definePageMeta({
    layout: 'guest',
    middleware: ['guest'],
  });

  useSeoMeta({
    title: t('seo.forgot_password.title'),
  });

  // Step 1: Send OTP
  async function handleSendOtp() {
    loader.value = true;
    errors.value = {};

    try {
      const response = await useApiFetch('/password/send-otp', {
        method: 'POST',
        body: { email: form.email },
      });

      console.log('OTP sent successfully:', response);
      step.value = 2;
    } catch (error) {
      if (error?.statusCode === 422) {
        errors.value = error.data?.errors?.errors || error.data?.errors || {};
      } else {
        errors.value = { email: [error?.data?.message || 'Something went wrong. Please try again.'] };
      }
    }

    loader.value = false;
  }

  // Step 2: Verify OTP
  async function handleVerifyOtp() {
    loader.value = true;
    errors.value = {};

    try {
      await useApiFetch('/password/verify-otp', {
        method: 'POST',
        body: { email: form.email, otp: form.otp },
      });

      step.value = 3;
    } catch (error) {
      if (error.statusCode === 422) {
        errors.value = error.data.errors.errors || error.data.errors;
      }
    }

    loader.value = false;
  }

  // Step 3: Reset Password
  async function handleResetPassword() {
    loader.value = true;
    errors.value = {};

    try {
      const response = await useApiFetch('/password/reset', {
        method: 'POST',
        body: {
          email: form.email,
          otp: form.otp,
          password: form.password,
          password_confirmation: form.password_confirmation,
        },
      });

      step.value = 4; // success
      successMessage.value = response.phone || '';
    } catch (error) {
      if (error.statusCode === 422) {
        errors.value = error.data.errors.errors || error.data.errors;
      }
    }

    loader.value = false;
  }

  // Resend OTP
  async function handleResendOtp() {
    loader.value = true;
    errors.value = {};

    try {
      await useApiFetch('/password/send-otp', {
        method: 'POST',
        body: { email: form.email },
      });

      form.otp = '';
    } catch (error) {
      if (error.statusCode === 422) {
        errors.value = error.data.errors.errors || error.data.errors;
      }
    }

    loader.value = false;
  }
</script>

<template>
  <div>
    <div class="my-10">
      <h1 class="text-xl text-gray-800 font-medium text-center">{{ $t('guest.forgot_password.title') }}</h1>
      <p class="text-gray-600 mt-2 text-center">
        <template v-if="step === 1">{{ $t('guest.forgot_password.subtitle') }}</template>
        <template v-else-if="step === 2">{{ $t('guest.forgot_password.otp_subtitle') }}</template>
        <template v-else-if="step === 3">{{ $t('guest.reset_password.subtitle') }}</template>
      </p>
    </div>

    <!-- Step 4: Success -->
    <div v-if="step === 4" class="text-center space-y-6">
      <!-- material-symbols:check-circle-outline-rounded -->
      <svg class="w-24 h-24 text-brand-400 mx-auto" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">
        <path fill="currentColor" d="m10.6 13.8l-2.15-2.15q-.275-.275-.7-.275t-.7.275t-.275.7t.275.7L9.9 15.9q.3.3.7.3t.7-.3l5.65-5.65q.275-.275.275-.7t-.275-.7t-.7-.275t-.7.275zM12 22q-2.075 0-3.9-.788t-3.175-2.137T2.788 15.9T2 12t.788-3.9t2.137-3.175T8.1 2.788T12 2t3.9.788t3.175 2.137T21.213 8.1T22 12t-.788 3.9t-2.137 3.175t-3.175 2.138T12 22" />
      </svg>

      <p class="text-green-600 font-medium text-lg">{{ $t('guest.reset_password.success_message') }}</p>

      <NuxtLinkLocale :to="{ name: 'login' }" class="inline-block">
        <ButtonsPrimary class="w-full" :name="$t('guest.forgot_password.back_to_login')" />
      </NuxtLinkLocale>
    </div>

    <!-- Step 1: Enter Email -->
    <form v-else-if="step === 1" @submit.prevent="handleSendOtp()" class="space-y-6">
      <div>
        <InputsLabel for="email" :name="$t('inputs.email')" />
        <InputsDefault v-model="form.email" id="email" type="email" autocomplete="email" required />
        <InputsError :message="errors?.email?.[0]" />
      </div>

      <ButtonsPrimary type="submit" class="w-full" :name="$t('guest.forgot_password.send_otp')" :loader />
    </form>

    <!-- Step 2: Enter OTP -->
    <form v-else-if="step === 2" @submit.prevent="handleVerifyOtp()" class="space-y-6">
      <div>
        <InputsLabel :name="$t('guest.forgot_password.otp_label')" class="text-center mb-4" />
        <InputsOtpInput v-model="form.otp" @complete="handleVerifyOtp" />
        <InputsError :message="errors?.otp?.[0]" class="text-center mt-2" />
      </div>

      <ButtonsPrimary type="submit" class="w-full" :name="$t('guest.forgot_password.verify_otp')" :loader />

      <div class="text-center">
        <button type="button" @click="handleResendOtp()" class="text-brand-600 hover:text-brand-700 text-sm font-medium transition-colors" :disabled="loader">
          {{ $t('guest.forgot_password.resend_otp') }}
        </button>
      </div>
    </form>

    <!-- Step 3: New Password -->
    <form v-else-if="step === 3" @submit.prevent="handleResetPassword()" class="space-y-6">
      <div>
        <InputsLabel for="password" :name="$t('inputs.new_password')" />
        <InputsDefault v-model="form.password" id="password" type="password" placeholder="••••••••" autocomplete="new-password" required />
        <InputsError :message="errors?.password?.[0]" />
      </div>

      <div>
        <InputsLabel for="password_confirmation" :name="$t('inputs.password_confirmation')" />
        <InputsDefault v-model="form.password_confirmation" id="password_confirmation" type="password" placeholder="••••••••" autocomplete="new-password" required />
        <InputsError :message="errors?.password_confirmation?.[0]" />
      </div>

      <ButtonsPrimary type="submit" class="w-full" :name="$t('guest.reset_password.reset_button')" :loader />
    </form>

    <!-- Back to Login Link -->
    <div v-if="step !== 4" class="text-center mt-6">
      <NuxtLinkLocale :to="{ name: 'login' }" class="text-brand-600 hover:text-brand-700 font-medium">
        {{ $t('guest.forgot_password.back_to_login') }}
      </NuxtLinkLocale>
    </div>
  </div>
</template>
