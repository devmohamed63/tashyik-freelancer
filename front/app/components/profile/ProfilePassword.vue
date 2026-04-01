<script setup>
  const { t } = useI18n();
  const loader = ref(false);
  const status = ref(null);
  const errors = ref({});
  const credentials = reactive({
    current_password: '',
    password: '',
    password_confirmation: '',
  });

  async function handleUpdate() {
    loader.value = true;

    try {
      await useApiFetch('/profile/password', {
        method: 'PUT',
        body: credentials,
      });

      status.value = t('common.updated_successfully');

      setTimeout(() => {
        status.value = null;
      }, 1300);

      credentials.current_password = '';
      credentials.password = '';
      credentials.password_confirmation = '';

      errors.value = {};
    } catch (error) {
      // Check for validation errors
      if (error.statusCode === 422) {
        errors.value = error.data.errors.errors;
      } else {
        console.error('Failed to update password:', error);
      }
    }

    loader.value = false;
  }
</script>

<template>
  <div class="w-full bg-white shadow rounded-lg p-5 lg:p-10 flex flex-col gap-8">
    <h3 v-text="$t('profile.update_password')" class="text-xl md:text-2xl text-gray-700"></h3>
    <form @submit.prevent="handleUpdate()" class="space-y-6 w-full max-w-xl">
      <!-- Current password -->
      <div>
        <InputsLabel for="current_password" :name="$t('inputs.current_password')" />
        <InputsDefault v-model="credentials.current_password" id="current_password" type="password" placeholder="••••••••" autocomplete="current-password" required />
        <InputsError :message="errors?.current_password?.[0]" />
      </div>

      <!-- New password -->
      <div>
        <InputsLabel for="password" :name="$t('inputs.new_password')" />
        <InputsDefault v-model="credentials.password" id="password" type="password" placeholder="••••••••" autocomplete="new-password" required />
        <InputsError :message="errors?.password?.[0]" />
      </div>

      <!-- Confirm password -->
      <div>
        <InputsLabel for="password_confirmation" :name="$t('inputs.password_confirmation')" />
        <InputsDefault v-model="credentials.password_confirmation" id="password_confirmation" type="password" placeholder="••••••••" autocomplete="new-password" required />
        <InputsError :message="errors?.password_confirmation?.[0]" />
      </div>

      <div class="inline-flex items-center gap-2">
        <ButtonsPrimary type="submit" :name="$t('common.update')" :loader />
        <span v-if="status" v-text="status" class="text-green-500 dark:text-green-400 text-[15px]"></span>
      </div>
    </form>
  </div>
</template>
