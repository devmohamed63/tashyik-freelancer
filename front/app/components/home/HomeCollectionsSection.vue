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

  const { data, error } = useApiFetch('/general/service-collections', {}, true);

  watch(error, (e) => {
    if (e) console.error('Failed to load service collections:', e);
  });

  const visibleCollections = computed(() => {
    const list = data.value?.data;
    if (!list) return [];
    return list.slice(props.start, props.end);
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
