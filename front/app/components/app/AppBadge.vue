<script setup>
  const classes = ref({});

  const props = defineProps({
    type: {
      type: String,
      required: true,
    },
    title: {
      type: String,
      required: true,
    },
  });

  switch (props.type) {
    case 'success':
      classes.value = 'bg-green-50 text-green-500';
      break;
  }
</script>

<template>
  <span v-text="title" :class="classes" class="px-3 py-1 rounded-lg w-fit"></span>
</template>
