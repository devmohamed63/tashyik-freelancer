<script setup>
  const { t } = useI18n();
  const auth = useAuthStore();
  const loader = ref(false);
  const status = ref(null);
  const errors = ref({});
  const image = ref({});
  const fileInput = ref(null);
  const selectedImage = ref(null);
  const credentials = reactive({
    name: '',
    phone: '',
    email: '',
  });

  credentials.name = auth.user.name;
  credentials.phone = auth.user.phone;
  credentials.email = auth.user.email || '';
  image.value.previewUrl = auth.user.picture;

  function handleFile() {
    const file = fileInput.value.files[0];

    fileInput.value.value = null;

    if (!file) {
      return;
    }

    selectedImage.value = file;

    const imageTypes = ['image/jpeg', 'image/png', 'image/webp'];

    if (!imageTypes.includes(file.type)) {
      errors.value.image = [t('profile.image.errors.not_valid')];
      selectedImage.value = null;
      return;
    }

    if ((file.size / 1024 / 1024).toFixed(2) > 2) {
      errors.value.image = [t('profile.image.errors.size_error')];
      selectedImage.value = null;
      return;
    }

    image.value.previewUrl = URL.createObjectURL(file);
  }

  async function handleUpdate() {
    loader.value = true;

    const formData = new FormData();

    Object.keys(credentials).forEach((key) => {
      formData.append(key, credentials[key] ?? '');
    });

    if (selectedImage.value) {
      formData.append('image', selectedImage.value);
    }

    try {
      await useApiFetch('/profile', {
        method: 'POST',
        body: formData,
      });

      // Update user
      auth.fetchUser();

      status.value = t('common.updated_successfully');

      setTimeout(() => {
        status.value = null;
      }, 1300);

      errors.value = {};
    } catch (error) {
      // Check for validation errors
      if (error.statusCode === 422) {
        errors.value = error.data.errors.errors;
      } else {
        console.error('Failed to update profile:', error);
      }
    }

    loader.value = false;
  }
</script>

<template>
  <div class="w-full bg-white shadow rounded-lg p-5 lg:p-10 flex flex-col gap-8">
    <h3 v-text="$t('profile.information')" class="text-xl md:text-2xl text-gray-700"></h3>

    <!-- Linked institution badge for members -->
    <div v-if="auth.user?.institution" class="flex items-center gap-2 px-4 py-3 bg-violet-50 border border-violet-100 rounded-lg">
      <svg class="w-5 h-5 text-violet-500 shrink-0" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
        <rect x="4" y="2" width="16" height="20" rx="2" ry="2" /><path d="M9 22v-4h6v4" /><path d="M8 6h.01" /><path d="M16 6h.01" /><path d="M12 6h.01" /><path d="M12 10h.01" /><path d="M12 14h.01" /><path d="M16 10h.01" /><path d="M16 14h.01" /><path d="M8 10h.01" /><path d="M8 14h.01" />
      </svg>
      <span class="text-sm text-violet-700 font-medium">{{ $t('profile.linked_institution') }} {{ auth.user.institution.name }}</span>
    </div>

    <div class="flex flex-col md:flex-row gap-5 justify-between">
      <!-- Avatar -->
      <div class="md:order-2 flex flex-col lg:items-center gap-2 w-full">
        <div class="w-36 h-36 md:w-40 md:h-40 rounded-full text-white group relative">
          <!-- material-symbols:android-camera-outline -->
          <label for="profile-picture" type="button" class="cursor-pointer transition-all rounded-full w-full h-full absolute flex justify-center items-center bg-black/20 group-hover:bg-black/40">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-9 h-9" viewBox="0 0 24 24"><path fill="currentColor" d="M12 18q2.075 0 3.538-1.462Q17 15.075 17 13q0-2.075-1.462-3.538Q14.075 8 12 8Q9.925 8 8.463 9.462Q7 10.925 7 13q0 2.075 1.463 3.538Q9.925 18 12 18Zm0-2q-1.25 0-2.125-.875T9 13q0-1.25.875-2.125T12 10q1.25 0 2.125.875T15 13q0 1.25-.875 2.125T12 16Zm6-6q.425 0 .712-.288Q19 9.425 19 9t-.288-.713Q18.425 8 18 8t-.712.287Q17 8.575 17 9t.288.712Q17.575 10 18 10ZM4 21q-.825 0-1.412-.587Q2 19.825 2 19V7q0-.825.588-1.412Q3.175 5 4 5h3.15L8.7 3.325q.15-.15.337-.238Q9.225 3 9.425 3h5.15q.2 0 .388.087q.187.088.337.238L16.85 5H20q.825 0 1.413.588Q22 6.175 22 7v12q0 .825-.587 1.413Q20.825 21 20 21Zm16-2V7h-4.05l-1.825-2h-4.25L8.05 7H4v12Zm-8-6Z" /></svg>
            <input @change="handleFile" type="file" id="profile-picture" class="hidden" ref="fileInput" />
          </label>
          <img :src="image?.previewUrl" class="w-full h-full object-cover object-center rounded-full" />
        </div>
        <InputsError :message="errors?.image?.[0]" />
      </div>
      <form @submit.prevent="handleUpdate()" class="space-y-6 w-full max-w-xl">
        <!-- Name -->
        <div>
          <InputsLabel for="name" :name="$t('inputs.name')" />
          <InputsDefault v-model="credentials.name" id="name" type="text" required />
          <InputsError :message="errors?.name?.[0]" />
        </div>

        <!-- Phone -->
        <div>
          <InputsLabel for="phone" :name="$t('inputs.phone')" />
          <InputsDefault v-model="credentials.phone" id="phone" required />
          <InputsError :message="errors?.phone?.[0]" />
        </div>

        <!-- Email -->
        <div>
          <InputsLabel for="email" :name="$t('inputs.email_optional')" />
          <InputsDefault v-model="credentials.email" id="email" type="email" autocomplete="email" />
          <InputsError :message="errors?.email?.[0]" />
        </div>

        <div class="inline-flex items-center gap-2">
          <ButtonsPrimary type="submit" :name="$t('common.update')" :loader />
          <span v-if="status" v-text="status" class="text-green-500 dark:text-green-400 text-[15px]"></span>
        </div>
      </form>
    </div>
  </div>
</template>
