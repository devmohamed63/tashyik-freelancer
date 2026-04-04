<script setup>
  const { t } = useI18n();
  const loader = ref(false);
  const credentials = reactive({
    phone: '',
    password: '',
    remember: false,
  });
  const errors = ref({});
  const { login } = useAuthStore();

  definePageMeta({
    layout: 'guest',
    middleware: ['guest'],
  });

  useSeoMeta({
    title: t('seo.login.title'),
  });

  async function handleLogin() {
    loader.value = true;

    const validationErrors = await login(credentials);

    if (validationErrors) {
      errors.value = validationErrors;
    }

    loader.value = false;
  }
</script>

<template>
  <div>
    <div class="my-10">
      <h1 class="text-xl text-gray-800 font-medium text-center">{{ $t('guest.login.title') }}</h1>
      <p class="text-gray-600 mt-2 text-center">{{ $t('guest.login.subtitle') }}</p>
    </div>

    <form @submit.prevent="handleLogin()" class="space-y-6">
      <!-- Phone -->
      <div>
        <InputsLabel for="phone" :name="$t('inputs.phone')" />
        <InputsDefault v-model="credentials.phone" id="phone" autocomplete="phone" required />
        <InputsError :message="errors?.phone?.[0]" />
      </div>

      <!-- Password -->
      <div>
        <InputsLabel for="password" :name="$t('inputs.password')" />
        <InputsDefault v-model="credentials.password" id="password" type="password" placeholder="••••••••" autocomplete="current-password" required />
        <InputsError :message="errors?.password?.[0]" />
      </div>

      <!-- Remember Me -->
      <div class="flex flex-wrap items-center justify-between gap-2">
        <InputsCheckbox v-model="credentials.remember" id="remembers" :name="$t('inputs.remember_me')" />
        <NuxtLinkLocale :to="{ name: 'forgot-password' }" class="text-gray-500 text-sm hover:text-brand-600 transition-colors">{{ $t('guest.login.forgot_password') }}</NuxtLinkLocale>
      </div>

      <ButtonsPrimary type="submit" class="w-full" :name="$t('guest.login.sign_in')" :loader />
    </form>

    <!-- Sign Up Link -->
    <div class="text-center mt-6">
      <p class="text-gray-600">
        <span class="block w-full mb-4" v-text="$t('guest.login.no_account')"></span>
        <NuxtLinkLocale :to="{ name: 'register' }" class="text-brand-600 hover:text-brand-700 font-medium">
          {{ $t('guest.login.sign_up') }}
        </NuxtLinkLocale>
        {{ $t('common.or') }}
        <NuxtLinkLocale :to="{ name: 'service-provider-register' }" class="text-brand-600 hover:text-brand-700 font-medium">
          {{ $t('navigation.service_provider') }}
        </NuxtLinkLocale>
      </p>
    </div>
  </div>
</template>
