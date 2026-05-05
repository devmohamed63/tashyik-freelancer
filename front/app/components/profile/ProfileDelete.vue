<script setup>
  const auth = useAuthStore();
  const loader = ref(false);
  const errors = ref({});
  const localePath = useLocalePath();

  async function handleDelete() {
    loader.value = true;

    try {
      await useApiFetch('/profile', {
        method: 'DELETE',
      });

      auth.resetUser();

      return navigateTo(localePath({ name: 'index' }));
    } catch (error) {
      // Check for validation errors
      if (error.statusCode === 422) {
        errors.value = error.data.errors.errors;
      } else {
        console.error('Failed to delete account:', error);
      }
    }

    loader.value = false;
  }
</script>

<template>
  <div class="w-full bg-white shadow rounded-lg p-5 lg:p-10 flex flex-col gap-8">
    <h3 v-text="$t('profile.delete_account')" class="text-xl md:text-2xl text-gray-700"></h3>
    <form @submit.prevent="handleDelete()" class="space-y-6 w-full max-w-xl">
      <ButtonsDanger type="submit" :name="$t('common.delete')" :loader />
    </form>
  </div>
</template>
