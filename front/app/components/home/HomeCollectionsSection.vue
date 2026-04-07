<script setup>
  const props = defineProps({
    start: {
      type: Number,
      default: 0,
    },
    end: {
      type: Number,
      default: undefined,
    },
    maxServices: {
      type: Number,
      default: 10,
    },
  });

  const collections = ref(null);

  try {
    const response = await useApiFetch('/general/service-collections');
    collections.value = response.data;
  } catch (error) {
    console.error('Failed to load service collections:', error);
  }

  const visibleCollections = computed(() => {
    if (!collections.value) return [];
    return collections.value.slice(props.start, props.end);
  });
</script>

<template>
  <template v-if="visibleCollections?.length">
    <HomeCollectionItem
      v-for="(collection, index) in visibleCollections"
      :key="collection.title"
      :collection="collection"
      :index="start + index"
      :max-services="maxServices"
    />
  </template>
</template>
