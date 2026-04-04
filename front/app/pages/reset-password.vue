<script setup>
  const { t } = useI18n();
  const route = useRoute();
  const localePath = useLocalePath();
  const loader = ref(false);
  const errors = ref({});
  const success = ref(false);
  const successPhone = ref('');

  const credentials = reactive({
    token: route.query.token || '',
    email: route.query.email || '',
    password: '',
    password_confirmation: '',
  });

  definePageMeta({
    layout: 'guest',
    middleware: ['guest'],
  });

  useSeoMeta({
    title: t('seo.reset_password.title'),
  });

  async function handleSubmit() {
    loader.value = true;
    errors.value = {};

    try {
      const response = await useApiFetch('/reset-password', {
        method: 'POST',
        body: credentials,
      });

      success.value = true;
      successPhone.value = response.phone || '';
    } catch (error) {
      if (error.statusCode === 422) {
        errors.value = error.data.errors.errors;
      } else {
        console.error('Failed to reset password:', error);
      }
    }

    loader.value = false;
  }
</script>

<template>
  <div>
    <div class="my-10">
      <h1 class="text-xl text-gray-800 font-medium text-center">{{ $t('guest.reset_password.title') }}</h1>
      <p class="text-gray-600 mt-2 text-center">{{ $t('guest.reset_password.subtitle') }}</p>
    </div>

    <!-- Success Message -->
    <div v-if="success" class="text-center space-y-6">
      <!-- material-symbols:check-circle-outline-rounded -->
      <svg class="w-24 h-24 text-brand-400 mx-auto" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">
        <!-- Icon from Material Symbols by Google - https://github.com/google/material-design-icons/blob/master/LICENSE -->
        <path fill="currentColor" d="m10.6 13.8l-2.15-2.15q-.275-.275-.7-.275t-.7.275t-.275.7t.275.7L9.9 15.9q.3.3.7.3t.7-.3l5.65-5.65q.275-.275.275-.7t-.275-.7t-.7-.275t-.7.275zM12 22q-2.075 0-3.9-.788t-3.175-2.137T2.788 15.9T2 12t.788-3.9t2.137-3.175T8.1 2.788T12 2t3.9.788t3.175 2.137T21.213 8.1T22 12t-.788 3.9t-2.137 3.175t-3.175 2.138T12 22" />
      </svg>

      <p class="text-green-600 font-medium text-lg">{{ $t('guest.reset_password.success_message') }}</p>

      <NuxtLinkLocale :to="{ name: 'login' }" class="inline-block">
        <ButtonsPrimary class="w-full" :name="$t('guest.forgot_password.back_to_login')" />
      </NuxtLinkLocale>
    </div>

    <!-- Form -->
    <form v-else @submit.prevent="handleSubmit()" class="space-y-6">
      <!-- Email (read-only) -->
      <div>
        <InputsLabel for="email" :name="$t('inputs.email')" />
        <InputsDefault v-model="credentials.email" id="email" type="email" autocomplete="email" readonly class="bg-gray-100 cursor-not-allowed" />
        <InputsError :message="errors?.email?.[0]" />
      </div>

      <!-- Password -->
      <div>
        <InputsLabel for="password" :name="$t('inputs.new_password')" />
        <InputsDefault v-model="credentials.password" id="password" type="password" placeholder="••••••••" autocomplete="new-password" required />
        <InputsError :message="errors?.password?.[0]" />
      </div>

      <!-- Confirm Password -->
      <div>
        <InputsLabel for="password_confirmation" :name="$t('inputs.password_confirmation')" />
        <InputsDefault v-model="credentials.password_confirmation" id="password_confirmation" type="password" placeholder="••••••••" autocomplete="new-password" required />
        <InputsError :message="errors?.password_confirmation?.[0]" />
      </div>

      <InputsError :message="errors?.token?.[0]" />

      <ButtonsPrimary type="submit" class="w-full" :name="$t('guest.reset_password.reset_button')" :loader />
    </form>
  </div>
</template>
