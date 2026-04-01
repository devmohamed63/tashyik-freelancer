<script setup>
  defineProps(['name', 'loader']);
</script>

<template>
  <button class="flex flex-row items-center justify-center gap-2 text-white bg-red-500 hover:bg-red-500/90 focus:ring-4 focus:outline-none focus:ring-red-200 font-medium rounded-lg text-sm px-5 py-2.5 text-center dark:ring-red-400 dark:hover:bg-red-500 dark:focus:ring-red-500/90 disabled:bg-red-400 disabled:hover:bg-red-400 dark:disabled:hover:bg-red-400" :disabled="loader">
    <slot>
      {{ name }}
    </slot>
    <!-- svg-spinners:180-ring-with-bg -->
    <svg v-if="loader" xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 animate-spin" width="32" height="32" viewBox="0 0 24 24">
      <path fill="currentColor" d="M12,1A11,11,0,1,0,23,12,11,11,0,0,0,12,1Zm0,19a8,8,0,1,1,8-8A8,8,0,0,1,12,20Z" opacity=".25" />
      <path fill="currentColor" d="M12,4a8,8,0,0,1,7.89,6.7A1.53,1.53,0,0,0,21.38,12h0a1.5,1.5,0,0,0,1.48-1.75,11,11,0,0,0-21.72,0A1.5,1.5,0,0,0,2.62,12h0a1.53,1.53,0,0,0,1.49-1.3A8,8,0,0,1,12,4Z" />
    </svg>
  </button>
</template>
