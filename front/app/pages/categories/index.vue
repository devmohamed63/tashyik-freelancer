<script setup>
  const { t, locale } = useI18n();
  const config = useRuntimeConfig();
  const categories = ref(null);

  useSeoMeta({
    title: t('seo.categories.title'),
    ogTitle: t('seo.categories.title'),
    description: t('seo.categories.description'),
    ogDescription: t('seo.categories.description'),
  });

  try {
    const response = await useApiFetch('/categories');

    categories.value = response.data;
  } catch (error) {
    console.error('Failed to load categories:', error);
  }

  // JSON-LD Structured Data
  if (categories.value?.length) {
    useHead({
      script: [
        {
          type: 'application/ld+json',
          innerHTML: JSON.stringify({
            '@context': 'https://schema.org',
            '@type': 'ItemList',
            name: t('seo.categories.title'),
            itemListElement: categories.value.map((cat, i) => ({
              '@type': 'ListItem',
              position: i + 1,
              name: cat.name,
              url: `${config.public.appUrl}/${locale.value}/categories/${cat.slug}`,
            })),
          }),
        },
      ],
    });
  }
</script>

<template>
  <section class="container py-12 md:py-20 space-y-12 px-4">
    <div class="flex flex-col gap-5">
      <h1 v-text="$t('categories.title')" class="text-3xl md:text-4xl text-gray-800 font-medium"></h1>
      <h2 v-text="$t('categories.subtitle')" class="text-lg md:text-xl text-gray-500"></h2>
    </div>
    <div>
      <div class="flex flex-col md:grid grid-cols-2 lg:grid-cols-3 gap-5">
        <CategoryLink v-for="category of categories" :key="category.id" :category />
      </div>
    </div>
  </section>

  <!-- Ready to join section -->
  <ReadyToJoinSection :filled="true" />
</template>
