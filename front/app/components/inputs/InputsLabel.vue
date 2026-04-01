<script setup>
  defineProps({
    name: null,
    optional: false,
  });
  const { t } = useI18n();
</script>

<template>
  <label class="block mb-2 text-sm text-gray-900 dark:text-white">
    <slot>
      <div class="inline-flex gap-2 items-center">
        <span v-text="name"></span>
        <span v-if="optional" class="bg-brand-50 text-brand-500 text-xs px-3 py-1 rounded-sm dark:bg-gray-700 dark:text-gray-200" v-text="t('common.optional')"></span>
      </div>
    </slot>
  </label>
</template>
