<script setup>
defineProps({
  text: {
    type: [String, Number],
    required: true,
  },
  size: {
    type: String,
    default: 'base' // accepts: 'xs', 'sm', 'base'
  }
});
</script>

<template>
  <span 
    class="inline-flex items-center gap-1 bg-brand-50 text-brand-700 font-medium rounded shadow-sm border border-brand-100"
    :class="{
      'text-[10px] px-2 py-1': size === 'xs',
      'text-xs px-2 py-1': size === 'sm',
      'text-sm px-3 py-1.5': size === 'base'
    }"
  >
    <svg 
      class="text-brand-500 animate-pulse"
      :class="{
        'w-2.5 h-2.5': size === 'xs',
        'w-3 h-3': size === 'sm',
        'w-3.5 h-3.5': size === 'base'
      }"
      fill="currentColor" 
      viewBox="0 0 20 20"
    >
      <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
    </svg>
    {{ text }}
  </span>
</template>
