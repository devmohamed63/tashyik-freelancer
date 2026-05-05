<script setup>
  const page = ref(null);

  try {
    const response = await useApiFetch('/general/default-pages/terms-and-conditions');

    page.value = response.data;
  } catch (error) {
    console.error('Failed to load page:', error);
  }

  useSeoMeta({
    title: page.value.name,
    ogTitle: page.value.name,
    description: page.value?.description?.replace(/<[^>]*>/g, ''),
    ogDescription: page.value?.description?.replace(/<[^>]*>/g, ''),
  });
</script>

<template>
  <section class="text-center py-16 bg-gradient-to-r from-primary-50 to-accent-50 px-3 md:px-10 container">
    <h1 v-text="page.name" class="text-brand-500 text-3xl font-heading font-bold text-scimaPrimary mb-6"></h1>
    <div v-html="page.body" class="text-lg mt-2 text-gray-900 leading-8"></div>
  </section>
</template>
