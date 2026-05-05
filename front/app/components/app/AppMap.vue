<script setup>
  /**
   * Defers mounting (and Google Maps SDK registration) until the map container
   * is near the viewport — reduces main-thread + network work on first paint.
   */
  const props = defineProps({
    latitude: {
      type: Number,
      required: false,
    },
    longitude: {
      type: Number,
      required: false,
    },
  });

  const emit = defineEmits(['change']);
  const root = ref(null);
  const shouldLoad = ref(false);

  onMounted(() => {
    if (!root.value) {
      return;
    }
    if (typeof IntersectionObserver === 'undefined') {
      shouldLoad.value = true;
      return;
    }
    const io = new IntersectionObserver(
      (entries) => {
        if (entries[0]?.isIntersecting) {
          shouldLoad.value = true;
          io.disconnect();
        }
      },
      { rootMargin: '200px 0px', threshold: 0.01 },
    );
    io.observe(root.value);
    onBeforeUnmount(() => io.disconnect());
  });
</script>

<template>
  <div ref="root" :class="$attrs.class" class="relative rounded-xl overflow-hidden h-full w-full min-h-[12rem]">
    <AppMapBody
      v-if="shouldLoad"
      :latitude="props.latitude"
      :longitude="props.longitude"
      @change="emit('change', $event)"
    />
    <div
      v-else
      class="absolute inset-0 bg-gray-100 flex items-center justify-center min-h-[12rem] w-full h-full"
      aria-hidden="true"
    >
      <AppSpinner class="w-8 h-8" />
    </div>
  </div>
</template>
