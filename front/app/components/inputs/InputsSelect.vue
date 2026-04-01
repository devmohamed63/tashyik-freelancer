<script setup>
  const model = defineModel();
</script>

<template>
  <select v-model="model" class="bg-transparent border border-gray-300 text-gray-800 text-sm rounded-lg focus:ring-primary focus:border-primary block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-primary dark:focus:border-primary">
    <slot />
  </select>
</template>
