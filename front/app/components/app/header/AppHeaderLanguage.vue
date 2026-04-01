<script setup>
  const { locale, locales } = useI18n();
  const switchLocalePath = useSwitchLocalePath();
  const language = locales.value.filter((l) => l.code == locale.value)[0];
</script>

<template>
  <button id="languageDropdownButton" class="relative inline-flex items-center gap-2 p-2 justify-center text-sm text-gray-500 rounded-lg hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-gray-200 dark:text-gray-400 dark:hover:bg-gray-700 dark:focus:ring-gray-600" data-dropdown-toggle="languageDropdown" data-dropdown-placement="bottom" type="button">
    <!-- proicons:globe -->
    <svg class="w-5 h-5" xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24">
      <!-- Icon from ProIcons by ProCode - https://github.com/ProCode-Software/proicons/blob/main/LICENSE -->
      <path fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M21.25 12A9.25 9.25 0 0 0 12 2.75M21.25 12H2.75m18.5 0A9.25 9.25 0 0 1 12 21.25m0-18.5A9.25 9.25 0 0 0 2.75 12M12 2.75c-.5 0-4 4.141-4 9.25s3.5 9.25 4 9.25m0-18.5c.5 0 4 4.141 4 9.25s-3.5 9.25-4 9.25M2.75 12A9.25 9.25 0 0 0 12 21.25" />
    </svg>
    {{ language.name }}
    <span class="sr-only">Language</span>
    <!-- ic:outline-keyboard-arrow-down -->
    <svg class="w-5 h-5" xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24">
      <!-- Icon from Google Material Icons by Material Design Authors - https://github.com/material-icons/material-icons/blob/master/LICENSE -->
      <path fill="currentColor" d="M7.41 8.59L12 13.17l4.59-4.58L18 10l-6 6l-6-6z" />
    </svg>
  </button>

  <!-- Dropdown menu -->
  <div id="languageDropdown" class="z-10 hidden w-56 bg-white divide-y divide-gray-100 rounded-lg shadow border border-gray-200">
    <ul class="p-3 space-y-1 text-gray-500" aria-labelledby="languageDropdownButton">
      <NuxtLink class="w-full block p-2" v-for="locale in locales" :key="locale.code" :to="switchLocalePath(locale.code)" external>
        {{ locale.name }}
      </NuxtLink>
    </ul>
  </div>
</template>
