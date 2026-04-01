<script setup>
  const model = defineModel();
</script>

<template>
  <input v-model="model" :type="$attrs.type || 'text'" class="bg-transparent border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-brand-100 focus:border-brand-300 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-brand-500 dark:focus:border-brand-500" />
</template>
