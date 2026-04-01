<script setup>
  defineProps(['message']);
</script>

<template>
  <span v-if="message" class="text-red-500 text-[15px] mt-1 block" v-text="message"></span>
</template>
