<script setup>
  defineProps({
    duration: {
      type: String,
      required: true,
    },
  });
</script>

<template>
  <div class="w-fit inline-flex items-center gap-1 text-sm bg-green-50 text-green-500 stroke-green-500 px-3 py-2 rounded-lg me-4">
    <svg class="w-4.5 h-4.5" width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
      <path d="M8.00033 14.6668C8.00033 14.6668 13.3337 12.0002 13.3337 8.00016V3.3335L8.00033 1.3335L2.66699 3.3335V8.00016C2.66699 12.0002 8.00033 14.6668 8.00033 14.6668Z" stroke-width="1.33333" stroke-linecap="round" stroke-linejoin="round" />
      <path d="M10.6663 6L6.99967 9.66667L5.33301 8" stroke-linecap="round" stroke-linejoin="round" />
    </svg>
    {{ $t('common.warranty', { duration: duration }) }}
  </div>
</template>
