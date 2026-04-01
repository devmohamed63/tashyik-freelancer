<script setup>
  const { t } = useI18n();
  const localePath = useLocalePath();
  const errors = ref({});
  const loader = ref(false);
  const otherName = computed(() => ![t('addresses.form.options.home'), t('addresses.form.options.work')].includes(credentials.name));

  const props = defineProps({
    address: {
      type: Object,
      required: false,
    },
  });

  const credentials = reactive({
    name: props.address?.name ?? t('addresses.form.options.home'),
    address: props.address?.address ?? '',
    latitude: props.address?.latitude ?? '',
    longitude: props.address?.longitude ?? '',
    landmark: props.address?.landmark ?? '',
    building_number: props.address?.building_number ?? '',
    floor_number: props.address?.floor_number ?? '',
    apartment_number: props.address?.apartment_number ?? '',
    is_default: props.address?.is_default ?? false,
  });

  function positionChanged(position) {
    credentials.address = position.value.address;
    credentials.latitude = position.value.latitude;
    credentials.longitude = position.value.longitude;
  }

  async function handleSubmit() {
    loader.value = true;

    const route = props.address ? `/addresses/${props.address.id}` : '/addresses';
    const method = props.address ? 'PUT' : 'POST';

    try {
      await useApiFetch(route, {
        method: method,
        body: {
          name: credentials.name,
          address: credentials.address,
          latitude: credentials.latitude,
          longitude: credentials.longitude,
          landmark: credentials.landmark,
          building_number: credentials.building_number,
          floor_number: credentials.floor_number,
          apartment_number: credentials.apartment_number,
          is_default: credentials.is_default,
        },
      });

      await navigateTo(localePath({ name: 'addresses' }));
    } catch (error) {
      // Check for validation errors
      if (error.statusCode === 422) {
        errors.value = error.data.errors.errors;
      } else {
        console.error('Failed to create/update address:', error);
      }
    }

    loader.value = false;
  }
</script>

<template>
  <form @submit.prevent="handleSubmit" class="space-y-5 md:space-y-8">
    <div class="flex flex-col lg:grid grid-cols-2 gap-5">
      <!-- Details -->
      <div class="max-md:order-2 bg-white rounded-xl p-6 md:p-10 shadow flex flex-col gap-8">
        <span v-text="$t('addresses.form.title')" class="font-medium text-gray-800 text-xl md:text-2xl"></span>

        <!-- Name -->
        <div class="flex flex-col gap-8">
          <div>
            <label v-text="$t('inputs.address_name')" class="block mb-3 text-gray-500 md:text-lg font-medium"></label>
            <div class="grid grid-cols-3 gap-2 md:gap-4">
              <button @click="credentials.name = $t('addresses.form.options.home')" v-text="$t('addresses.form.options.home')" class="max-md:text-sm rounded-lg py-3 border" :class="[credentials.name == $t('addresses.form.options.home') ? 'bg-brand-500 text-white border-brand-500' : 'bg-transparent text-gray-500 border-gray-400']" type="button"></button>
              <button @click="credentials.name = $t('addresses.form.options.work')" v-text="$t('addresses.form.options.work')" class="max-md:text-sm rounded-lg py-3 border" :class="[credentials.name == $t('addresses.form.options.work') ? 'bg-brand-500 text-white border-brand-500' : 'bg-transparent text-gray-500 border-gray-400']" type="button"></button>
              <button @click="credentials.name = null" v-text="$t('addresses.form.options.other')" class="max-md:text-sm rounded-lg py-3 border" :class="[otherName ? 'bg-brand-500 text-white border-brand-500' : 'bg-transparent text-gray-500 border-gray-400']" type="button"></button>
            </div>
          </div>
          <div v-if="otherName">
            <label v-text="$t('inputs.type_address_name.title')" for="name" class="block mb-3 text-gray-500 md:text-lg font-medium"></label>
            <InputsDefault class="py-3 px-4" v-model="credentials.name" id="name" :placeholder="$t('inputs.type_address_name.placeholder')" />
            <InputsError :message="errors?.name?.[0]" />
          </div>
        </div>

        <!-- Address -->
        <div>
          <label v-text="$t('inputs.address.title')" for="address" class="block mb-3 text-gray-500 md:text-lg font-medium"></label>
          <InputsDefault class="py-3 px-4" v-model="credentials.address" id="address" :placeholder="$t('inputs.address.placeholder')" required />
          <InputsError :message="errors?.address?.[0]" />
        </div>

        <!-- Landmark -->
        <div>
          <label for="landmark" class="block mb-3 text-gray-500 md:text-lg font-medium">
            {{ $t('inputs.landmark.title') }}
            <span class="bg-brand-50 text-brand-400 text-[10px] sm:text-xs px-2 py-1 rounded-md" v-text="t('common.optional')"></span>
          </label>
          <InputsDefault class="py-3 px-4" v-model="credentials.landmark" id="landmark" :placeholder="$t('inputs.landmark.placeholder')" />
          <InputsError :message="errors?.landmark?.[0]" />
        </div>

        <div class="grid grid-cols-3 gap-4">
          <!-- Building number -->
          <div>
            <label for="building_number" class="block mb-3 text-gray-500 max-md:text-sm md:text-lg font-medium">
              {{ $t('inputs.building_number') }}
              <span class="bg-brand-50 text-brand-400 text-[10px] sm:text-xs px-2 py-1 rounded-md" v-text="t('common.optional')"></span>
            </label>
            <InputsDefault class="py-3 px-4" v-model="credentials.building_number" placeholder="245" id="building_number" />
            <InputsError :message="errors?.building_number?.[0]" />
          </div>

          <!-- Floor number -->
          <div>
            <label for="floor_number" class="block mb-3 text-gray-500 max-md:text-sm md:text-lg font-medium">
              {{ $t('inputs.floor_number') }}
              <span class="bg-brand-50 text-brand-400 text-[10px] sm:text-xs px-2 py-1 rounded-md" v-text="t('common.optional')"></span>
            </label>
            <InputsDefault class="py-3 px-4" v-model="credentials.floor_number" placeholder="14" id="floor_number" />
            <InputsError :message="errors?.floor_number?.[0]" />
          </div>

          <!-- Apartment number -->
          <div>
            <label for="apartment_number" class="block mb-3 text-gray-500 max-md:text-sm md:text-lg font-medium">
              {{ $t('inputs.apartment_number') }}
              <span class="bg-brand-50 text-brand-400 text-[10px] sm:text-xs px-2 py-1 rounded-md" v-text="t('common.optional')"></span>
            </label>
            <InputsDefault class="py-3 px-4" v-model="credentials.apartment_number" placeholder="21" id="apartment_number" />
            <InputsError :message="errors?.apartment_number?.[0]" />
          </div>
        </div>

        <!-- Mark as default -->
        <div class="inline-flex items-center h-5">
          <input v-model="credentials.is_default" id="is_default" type="checkbox" value="" class="w-5 h-5 text-brand-500 accent-brand-500 border border-gray-300 rounded bg-gray-50 focus:ring-3 focus:ring-brand-200 dark:bg-gray-700 dark:border-gray-600 dark:focus:ring-brand-500 dark:ring-offset-gray-800 dark:focus:ring-offset-gray-800" :required="$attrs.required" />
          <label v-text="$t('address.mark_as_default')" for="is_default" class="ms-3 text-gray-500"></label>
        </div>
      </div>

      <!-- Map -->
      <AppMap :latitude="address?.latitude" :longitude="address?.longitude" @change="positionChanged" class="max-md:order-1 h-[calc(100dvh-12rem)] lg:h-full" />
    </div>

    <ButtonsFilled class="max-sm:w-full" type="submit" :disabled="loader">
      {{ address ? $t('addresses.edit.save') : $t('addresses.create.save') }}
    </ButtonsFilled>
  </form>
</template>
