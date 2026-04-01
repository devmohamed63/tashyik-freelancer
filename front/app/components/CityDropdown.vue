<script setup>
  const selectedCity = defineModel('city');
  const cities = useCitiesStore();
  const emit = defineEmits(['city-changed']);

  watch(
    () => selectedCity.value,
    async () => {
      emit('city-changed', selectedCity.value);
    }
  );

  onMounted(() => {
    useFlowbite(() => {
      initDropdowns();
    });
  });
</script>

<template>
  <button class="sm:text-xl inline-flex gap-1 text-gray-500 items-center" id="citiesDropdownButton" data-dropdown-toggle="citiesDropdown" type="button">
    <!-- majesticons:map-marker -->
    <svg class="w-4 sm:w-6 h-4 sm:h-6 text-brand-500" xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24">
      <!-- Icon from Majesticons by Gerrit Halfmann - https://github.com/halfmage/majesticons/blob/main/LICENSE -->
      <path fill="currentColor" fill-rule="evenodd" d="M11.291 21.706L12 21zM12 21l.708.706a1 1 0 0 1-1.417 0l-.006-.007l-.017-.017l-.062-.063a48 48 0 0 1-1.04-1.106a50 50 0 0 1-2.456-2.908c-.892-1.15-1.804-2.45-2.497-3.734C4.535 12.612 4 11.248 4 10c0-4.539 3.592-8 8-8s8 3.461 8 8c0 1.248-.535 2.612-1.213 3.87c-.693 1.286-1.604 2.585-2.497 3.735a50 50 0 0 1-3.496 4.014l-.062.063l-.017.017l-.006.006zm0-8a3 3 0 1 0 0-6a3 3 0 0 0 0 6" clip-rule="evenodd" />
    </svg>

    {{ selectedCity.name }}

    <!-- majesticons:chevron-down -->
    <svg class="sm:ms-1 w-4 sm:w-6 h-4 sm:h-6" xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24">
      <!-- Icon from Majesticons by Gerrit Halfmann - https://github.com/halfmage/majesticons/blob/main/LICENSE -->
      <path fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m17 10l-5 5l-5-5" />
    </svg>
  </button>

  <!-- Dropdown menu -->
  <div id="citiesDropdown" class="z-10 hidden w-48 bg-white divide-y divide-gray-100 rounded-lg shadow border border-gray-200">
    <ul class="p-3 space-y-1 text-gray-500" aria-labelledby="citiesDropdownButton">
      <li v-for="city of cities.collection" :key="city.id">
        <label :for="`city-${city.id}`" class="flex items-center p-2 rounded-sm hover:bg-gray-100">
          <input :id="`city-${city.id}`" v-model="selectedCity" type="radio" :value="city" class="w-4 h-4 text-brand-600 bg-gray-100 border-gray-300 focus:ring-brand-500 dark:focus:ring-brand-600 dark:ring-offset-gray-700 dark:focus:ring-offset-gray-700 focus:ring-2 dark:bg-gray-600 dark:border-gray-500" />
          <span v-text="city.name" class="w-full ms-2 font-medium rounded-sm"></span>
        </label>
      </li>
    </ul>
  </div>
</template>
