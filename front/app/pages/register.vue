<script setup>
  const { t } = useI18n();
  const cities = ref(null);
  const loader = ref(false);
  const errors = ref({});
  const { register } = useAuthStore();

  const credentials = reactive({
    // Basic information
    city: '',
    name: '',
    phone: '',
    password: '',
    password_confirmation: '',
    account_type: 'user',
  });

  definePageMeta({
    layout: 'guest',
    middleware: ['guest'],
  });

  useSeoMeta({
    title: t('seo.register.title'),
  });

  async function handleRegister() {
    loader.value = true;

    const validationErrors = await register(credentials);

    if (validationErrors) {
      errors.value = validationErrors;
    }

    loader.value = false;
  }

  onMounted(async () => {
    try {
      const response = await useApiFetch('/cities');

      cities.value = response.data;
    } catch (error) {
      console.error('Failed to load cities:', error);
    }
  });
</script>

<template>
  <div>
    <div class="my-10">
      <h1 class="text-xl text-gray-800 font-medium text-center">{{ $t('guest.register.customer.title') }}</h1>
      <p class="text-gray-600 mt-2 text-center">{{ $t('guest.register.customer.subtitle') }}</p>
    </div>

    <form @submit.prevent="handleRegister()" class="space-y-6">
      <!-- Name -->
      <div>
        <InputsLabel for="name" :name="$t('inputs.name')" />
        <InputsDefault v-model="credentials.name" id="name" required />
        <InputsError :message="errors?.name?.[0]" />
      </div>

      <!-- Phone -->
      <div>
        <InputsLabel for="phone" :name="$t('inputs.phone')" />
        <InputsDefault v-model="credentials.phone" id="phone" placeholder="05xxxxxxxx" required />
        <InputsError :message="errors?.phone?.[0]" />
      </div>

      <!-- Password -->
      <div>
        <InputsLabel for="password" :name="$t('inputs.password')" />
        <InputsDefault v-model="credentials.password" id="password" type="password" placeholder="••••••••" autocomplete="new-password" required />
        <InputsError :message="errors?.password?.[0]" />
      </div>

      <!-- Confirm password -->
      <div>
        <InputsLabel for="password_confirmation" :name="$t('inputs.password_confirmation')" />
        <InputsDefault v-model="credentials.password_confirmation" id="password_confirmation" type="password" placeholder="••••••••" autocomplete="new-password" required />
        <InputsError :message="errors?.password_confirmation?.[0]" />
      </div>

      <!-- City -->
      <div>
        <InputsLabel for="city" :name="$t('inputs.city')" />
        <InputsSelect v-model="credentials.city" id="city" required>
          <option value="" v-text="$t('common.select_city')"></option>
          <option v-for="city in cities" :key="city.id" :value="city.id" v-text="city.name"></option>
        </InputsSelect>
        <InputsError :message="errors?.city?.[0]" />
      </div>

      <ButtonsPrimary type="submit" class="w-full" :name="$t('guest.register.create_account')" :loader />
    </form>

    <!-- Sign Up Link -->
    <div class="text-center mt-6">
      <p class="text-gray-600">
        {{ $t('guest.register.have_account') }}
        <NuxtLinkLocale :to="{ name: 'login' }" class="text-brand-600 hover:text-brand-700 font-medium">
          {{ $t('guest.register.sign_in') }}
        </NuxtLinkLocale>
      </p>
    </div>
  </div>
</template>
