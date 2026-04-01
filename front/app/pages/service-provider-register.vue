<script setup>
  const { t } = useI18n();
  const loader = ref(false);
  const currentStep = ref(1);
  const errors = ref({});
  const cities = ref(null);
  const categories = ref(null);
  const applied = ref(false);

  // Image inputs
  const personal_picture = ref(null);
  const residence_image = ref(null);
  const commercial_registration_image = ref(null);
  const national_address_image = ref(null);

  const credentials = reactive({
    // Basic information
    city: '',
    name: '',
    phone: '',
    password: '',
    password_confirmation: '',
    account_type: 'service-provider',

    // Service provider information
    entity_type: '', // individual, institution or company
    categories: [],
    residence_name: '',
    residence_number: '',
    bank_name: '',
    iban: '',

    // institution or company information
    commercial_registration_number: '',
    tax_registration_number: '',
  });

  const isIndividual = computed(() => credentials.entity_type == 'individual');
  const isInstitution = computed(() => credentials.entity_type == 'institution');
  const isCompany = computed(() => credentials.entity_type == 'company');

  definePageMeta({
    layout: 'guest',
    middleware: ['guest'],
  });

  useSeoMeta({
    title: t('seo.service_provider_register.title'),
  });

  async function handleRegister() {
    loader.value = true;

    try {
      const formData = new FormData();

      for (const [key, value] of Object.entries(credentials)) {
        // Ignore categories input
        if (['categories'].includes(key)) continue;

        formData.append(key, value ?? '');
      }

      if (personal_picture.value) {
        formData.append('personal_picture', personal_picture.value);
      }

      if (commercial_registration_image.value) {
        formData.append('commercial_registration_image', commercial_registration_image.value);
      }

      if (residence_image.value) {
        formData.append('residence_image', residence_image.value);
      }

      if (national_address_image.value) {
        formData.append('national_address_image', national_address_image.value);
      }

      credentials.categories.forEach((id) => {
        formData.append('categories[]', id);
      });

      await useApiFetch('/register', {
        method: 'POST',
        body: formData,
      });

      applied.value = true;
    } catch (error) {
      // Check for validation errors
      if (error.statusCode === 422) {
        errors.value = error.data.errors.errors;
      } else {
        console.error('Failed to register:', error);
      }
    }

    loader.value = false;
  }

  function next() {
    if (currentStep.value >= 3) return;

    currentStep.value++;
  }

  function previous() {
    if (currentStep.value == 1) return;

    currentStep.value--;
  }

  onMounted(async () => {
    try {
      const response = await useApiFetch('/cities');

      cities.value = response.data;
    } catch (error) {
      console.error('Failed to load cities:', error);
    }

    try {
      const categoriesResponse = await useApiFetch('/categories');

      categories.value = categoriesResponse.data;

      categories.value = categories.value.map((item) => {
        return {
          label: item.name,
          value: item.id,
        };
      });
    } catch (error) {
      console.error('Failed to load categories:', error);
    }
  });
</script>

<template>
  <div>
    <div class="my-10">
      <h1 v-text="applied ? $t('guest.register.service_provider.applied.title') : $t('guest.register.service_provider.title')" class="text-xl text-gray-800 font-medium text-center"></h1>
      <p v-text="applied ? $t('guest.register.service_provider.applied.subtitle') : $t('guest.register.service_provider.subtitle')" class="text-gray-600 mt-2 text-center"></p>
    </div>

    <div v-if="applied" class="flex flex-col gap-5">
      <!-- material-symbols:check-circle-outline-rounded -->
      <svg class="w-32 h-32 text-brand-200 mx-auto mb-11" xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24">
        <!-- Icon from Material Symbols by Google - https://github.com/google/material-design-icons/blob/master/LICENSE -->
        <path fill="currentColor" d="m10.6 13.8l-2.15-2.15q-.275-.275-.7-.275t-.7.275t-.275.7t.275.7L9.9 15.9q.3.3.7.3t.7-.3l5.65-5.65q.275-.275.275-.7t-.275-.7t-.7-.275t-.7.275zM12 22q-2.075 0-3.9-.788t-3.175-2.137T2.788 15.9T2 12t.788-3.9t2.137-3.175T8.1 2.788T12 2t3.9.788t3.175 2.137T21.213 8.1T22 12t-.788 3.9t-2.137 3.175t-3.175 2.138T12 22m0-2q3.35 0 5.675-2.325T20 12t-2.325-5.675T12 4T6.325 6.325T4 12t2.325 5.675T12 20m0-8" />
      </svg>

      <NuxtLinkLocale :to="{ name: 'index' }" :aria-label="$t('common.back_to_home')">
        <ButtonsPrimary class="w-full">
          <span class="ps-3" v-text="$t('common.back_to_home')"></span>
          <!-- majesticons:chevron-right -->
          <svg class="w-5 h-5 rtl:rotate-180" xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24">
            <!-- Icon from Majesticons by Gerrit Halfmann - https://github.com/halfmage/majesticons/blob/main/LICENSE -->
            <path fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m10 7l5 5l-5 5" />
          </svg>
        </ButtonsPrimary>
      </NuxtLinkLocale>
    </div>

    <form v-else @submit.prevent="handleRegister()" class="space-y-6">
      <template v-if="currentStep == 1">
        <!-- Name -->
        <div>
          <InputsLabel for="name" :name="$t('inputs.name')" />
          <InputsDefault v-model="credentials.name" id="name" />
          <InputsError :message="errors?.name?.[0]" />
        </div>

        <!-- Phone -->
        <div>
          <InputsLabel for="phone" :name="$t('inputs.phone')" />
          <InputsDefault v-model="credentials.phone" id="phone" placeholder="05xxxxxxxx" />
          <InputsError :message="errors?.phone?.[0]" />
        </div>

        <!-- Password -->
        <div>
          <InputsLabel for="password" :name="$t('inputs.password')" />
          <InputsDefault v-model="credentials.password" id="password" type="password" placeholder="••••••••" autocomplete="new-password" />
          <InputsError :message="errors?.password?.[0]" />
        </div>

        <!-- Confirm password -->
        <div>
          <InputsLabel for="password_confirmation" :name="$t('inputs.password_confirmation')" />
          <InputsDefault v-model="credentials.password_confirmation" id="password_confirmation" type="password" placeholder="••••••••" autocomplete="new-password" />
          <InputsError :message="errors?.password_confirmation?.[0]" />
        </div>
      </template>

      <template v-if="currentStep == 2">
        <!-- City -->
        <div>
          <InputsLabel for="city" :name="$t('inputs.city')" />
          <InputsSelect v-model="credentials.city" id="city" required>
            <option value="" v-text="$t('common.select_city')"></option>
            <option v-for="city in cities" :key="city.id" :value="city.id" v-text="city.name"></option>
          </InputsSelect>
          <InputsError :message="errors?.city?.[0]" />
        </div>

        <!-- Categories -->
        <div>
          <InputsLabel for="categoriesDropdown" :name="$t('inputs.categories')" />
          <InputsMultiSelect v-model="credentials.categories" id="categoriesDropdown" :options="categories" :max="2" :placeholder="$t('common.select_categories')" />
          <InputsError :message="errors?.categories?.[0]" />
        </div>

        <!-- Entity type -->
        <div>
          <InputsLabel for="entity_type" :name="$t('inputs.entity_type')" />
          <InputsSelect v-model="credentials.entity_type" id="entity_type">
            <option value="" v-text="$t('common.select_type')"></option>
            <option value="individual" v-text="$t('common.individual')"></option>
            <option value="institution" v-text="$t('common.institution')"></option>
            <option value="company" v-text="$t('common.company')"></option>
          </InputsSelect>
          <InputsError :message="errors?.entity_type?.[0]" />
        </div>

        <template v-if="isIndividual">
          <!-- Personal image -->
          <div>
            <InputsLabel for="personal_picture" :name="$t('inputs.personal_picture')" />
            <InputsFile v-model="credentials.personal_picture" id="personal_picture" />
            <InputsError :message="errors?.personal_picture?.[0]" />
          </div>
        </template>

        <template v-if="isCompany || isInstitution">
          <!-- Commercial registration number -->
          <div>
            <InputsLabel for="commercial_registration_number" :name="$t('inputs.commercial_registration_number')" />
            <InputsDefault v-model="credentials.commercial_registration_number" id="commercial_registration_number" />
            <InputsError :message="errors?.commercial_registration_number?.[0]" />
          </div>

          <!-- Commercial registration image -->
          <div>
            <InputsLabel for="commercial_registration_image" :name="$t('inputs.commercial_registration_image')" />
            <InputsFile v-model="credentials.commercial_registration_image" id="commercial_registration_image" />
            <InputsError :message="errors?.commercial_registration_image?.[0]" />
          </div>

          <!-- National address image -->
          <div>
            <InputsLabel for="national_address_image" :name="$t('inputs.national_address_image')" />
            <InputsFile v-model="credentials.national_address_image" id="national_address_image" />
            <InputsError :message="errors?.national_address_image?.[0]" />
          </div>

          <!-- Tax registration number -->
          <div>
            <InputsLabel for="tax_registration_number" :name="$t('inputs.tax_registration_number')" />
            <InputsDefault v-model="credentials.tax_registration_number" id="tax_registration_number" />
            <InputsError :message="errors?.tax_registration_number?.[0]" />
          </div>
        </template>
      </template>

      <template v-if="currentStep == 3">
        <!--Residence name -->
        <div>
          <InputsLabel for="residence_name" :name="$t('inputs.residence_name')" />
          <InputsDefault v-model="credentials.residence_name" id="residence_name" />
          <InputsError :message="errors?.residence_name?.[0]" />
        </div>

        <!--Residence number -->
        <div>
          <InputsLabel for="residence_number" :name="$t('inputs.residence_number')" />
          <InputsDefault v-model="credentials.residence_number" id="residence_number" />
          <InputsError :message="errors?.residence_number?.[0]" />
        </div>

        <!--Residence image -->
        <div>
          <InputsLabel for="residence_image" :name="$t('inputs.residence_image')" />
          <InputsFile v-model="credentials.residence_image" id="residence_image" />
          <InputsError :message="errors?.residence_image?.[0]" />
        </div>

        <!-- Bank account name -->
        <div>
          <InputsLabel for="bank_name" :name="$t('inputs.bank_name')" />
          <InputsDefault v-model="credentials.bank_name" id="bank_name" />
          <InputsError :message="errors?.bank_name?.[0]" />
        </div>

        <!-- IBAN -->
        <div>
          <InputsLabel for="iban" :name="$t('inputs.iban')" />
          <InputsDefault v-model="credentials.iban" id="iban" />
          <InputsError :message="errors?.iban?.[0]" />
        </div>
      </template>

      <InputsError :message="errors?.[Object.keys(errors)[0]]?.[0]" />

      <div class="flex flex-col gap-3">
        <ButtonsPrimary @click="next" v-if="currentStep < 3" type="button" class="w-full" :name="$t('common.next')" />
        <ButtonsPrimary v-if="currentStep == 3" type="submit" class="w-full" :name="$t('guest.register.create_account')" :loader />
        <ButtonsLight @click="previous" v-if="currentStep > 1" type="button" class="w-full" :name="$t('common.back')" />
      </div>
    </form>
  </div>
</template>
