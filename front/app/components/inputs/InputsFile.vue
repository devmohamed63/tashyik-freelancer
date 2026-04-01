<script setup>
  const props = defineProps({
    modelValue: File,
  });

  const emit = defineEmits(['update:modelValue']);

  const onFileChange = (e) => {
    const file = e.target.files?.[0] || null;

    emit('update:modelValue', file);
  };
</script>

<template>
  <input type="file" accept=".webp, .png, .jpg, .jpeg" @change="onFileChange" class="bg-transparent block w-full text-sm text-gray-800 border border-gray-300 rounded-lg cursor-pointer" />
</template>
