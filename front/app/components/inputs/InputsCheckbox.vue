<script setup>
  const model = defineModel();
  defineProps(['name', 'id']);
</script>

<template>
  <div class="flex items-start">
    <div class="flex items-center h-5">
      <input v-model="model" :id="id" type="checkbox" value="" class="w-4 h-4 text-brand-500 accent-brand-500 border border-gray-300 rounded bg-gray-50 focus:ring-3 focus:ring-brand-200 dark:bg-gray-700 dark:border-gray-600 dark:focus:ring-brand-500 dark:ring-offset-gray-800 dark:focus:ring-offset-gray-800" :required="$attrs.required" />
    </div>
    <label :for="id" :class="`ms-2 text-sm font-medium text-gray-900 dark:text-gray-300 ${$attrs.class}`">
      <slot>
        {{ name }}
      </slot>
    </label>
  </div>
</template>
