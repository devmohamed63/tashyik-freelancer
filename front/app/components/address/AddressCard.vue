<script setup>
  const props = defineProps({
    address: {
      type: Object,
      required: true,
    },
  });

  const loader = ref(false);
  const emit = defineEmits(['default-changed', 'deleted']);

  async function toggleDefault() {
    loader.value = true;

    const newVal = !props.address.is_default;

    emit('default-changed', { address: props.address, newVal: newVal });

    try {
      await useApiFetch(`/addresses/${props.address.id}`, {
        method: 'PUT',
        body: {
          name: props.address.name,
          address: props.address.address,
          latitude: props.address.latitude,
          longitude: props.address.longitude,
          landmark: props.address.landmark,
          building_number: props.address.building_number,
          floor_number: props.address.floor_number,
          apartment_number: props.address.apartment_number,
          is_default: newVal,
        },
      });
    } catch (error) {
      console.error('Failed to update address:', error);
    }

    loader.value = false;
  }

  async function handleDelete() {
    loader.value = true;

    emit('deleted', { address: props.address });

    try {
      await useApiFetch(`/addresses/${props.address.id}`, { method: 'DELETE' });
    } catch (error) {
      console.error('Failed to delete address:', error);
    }

    loader.value = false;
  }
</script>

<template>
  <div class="bg-white rounded-xl shadow-md p-5 flex flex-col gap-3">
    <div class="inline-flex gap-2 justify-between items-center">
      <div class="inline-flex items-center gap-2">
        <svg class="w-10 h-10 p-2 rounded-lg bg-brand-50 text-brand-500" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
          <path d="M17.5 8.33331C17.5 14.1666 10 19.1666 10 19.1666C10 19.1666 2.5 14.1666 2.5 8.33331C2.5 6.34419 3.29018 4.43653 4.6967 3.03001C6.10322 1.62349 8.01088 0.833313 10 0.833313C11.9891 0.833313 13.8968 1.62349 15.3033 3.03001C16.7098 4.43653 17.5 6.34419 17.5 8.33331Z" stroke="#724193" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
          <path d="M10 10.8333C11.3807 10.8333 12.5 9.71402 12.5 8.33331C12.5 6.9526 11.3807 5.83331 10 5.83331C8.61929 5.83331 7.5 6.9526 7.5 8.33331C7.5 9.71402 8.61929 10.8333 10 10.8333Z" stroke="#724193" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
        </svg>
        <span v-text="address.name" class="font-medium text-gray-800"></span>
      </div>
      <div class="inline-flex gap-2">
        <NuxtLinkLocale :to="{ name: 'addresses-address', params: { address: address.id } }">
          <!-- material-symbols:edit-square-outline -->
          <svg class="w-6 h-6 text-gray-400" xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24">
            <!-- Icon from Material Symbols by Google - https://github.com/google/material-design-icons/blob/master/LICENSE -->
            <path fill="currentColor" d="M5 21q-.825 0-1.412-.587T3 19V5q0-.825.588-1.412T5 3h8.925l-2 2H5v14h14v-6.95l2-2V19q0 .825-.587 1.413T19 21zm4-6v-4.25l9.175-9.175q.3-.3.675-.45t.75-.15q.4 0 .763.15t.662.45L22.425 3q.275.3.425.663T23 4.4t-.137.738t-.438.662L13.25 15zM21.025 4.4l-1.4-1.4zM11 13h1.4l5.8-5.8l-.7-.7l-.725-.7L11 11.575zm6.5-6.5l-.725-.7zl.7.7z" />
          </svg>
        </NuxtLinkLocale>
        <button @click="handleDelete" :disabled="loader">
          <!-- material-symbols:delete-outline-rounded -->
          <svg class="w-6 h-6 text-gray-400" xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24">
            <!-- Icon from Material Symbols by Google - https://github.com/google/material-design-icons/blob/master/LICENSE -->
            <path fill="currentColor" d="M7 21q-.825 0-1.412-.587T5 19V6q-.425 0-.712-.288T4 5t.288-.712T5 4h4q0-.425.288-.712T10 3h4q.425 0 .713.288T15 4h4q.425 0 .713.288T20 5t-.288.713T19 6v13q0 .825-.587 1.413T17 21zM17 6H7v13h10zm-7 11q.425 0 .713-.288T11 16V9q0-.425-.288-.712T10 8t-.712.288T9 9v7q0 .425.288.713T10 17m4 0q.425 0 .713-.288T15 16V9q0-.425-.288-.712T14 8t-.712.288T13 9v7q0 .425.288.713T14 17M7 6v13z" />
          </svg>
        </button>
      </div>
    </div>
    <img src="/images/address-map.webp" class="rounded-lg w-full" alt="Map" />
    <div class="mt-4 mb-auto flex flex-col gap-1">
      <span v-text="address.address" class="font-medium text-gray-800"></span>
      <p v-text="$t('address.details', { building_number: address.building_number, floor_number: address.floor_number, apartment_number: address.apartment_number })" class="text-gray-500"></p>
    </div>
    <hr class="border border-gray-100 my-2" />
    <div class="inline-flex gap-2 justify-between items-center">
      <span v-text="$t('address.mark_as_default')" class="text-gray-400 text-sm"></span>
      <label class="inline-flex items-center cursor-pointer">
        <input @change="toggleDefault" type="checkbox" value="" class="sr-only peer" id="default" :checked="address.is_default" :disabled="loader" />
        <div class="relative w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-brand-300 rounded-full peer peer-checked:after:translate-x-full rtl:peer-checked:after:-translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-brand-500"></div>
      </label>
    </div>
  </div>
</template>
