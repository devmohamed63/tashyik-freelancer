<script setup>
  const { t } = useI18n();

  const reviews = [
    {
      name: t('home.reviews.item_1.name'),
      body: t('home.reviews.item_1.body'),
      date: t('home.reviews.item_1.date'),
      image: '/images/review-1.webp',
    },
    {
      name: t('home.reviews.item_2.name'),
      body: t('home.reviews.item_2.body'),
      date: t('home.reviews.item_2.date'),
      image: '/images/review-2.webp',
    },
    {
      name: t('home.reviews.item_3.name'),
      body: t('home.reviews.item_3.body'),
      date: t('home.reviews.item_3.date'),
      image: '/images/review-3.webp',
    },
  ];
</script>

<template>
  <section class="py-12 md:py-28">
    <div class="container px-4">
      <h3 v-text="$t('home.reviews.title')" class="text-2xl md:text-3xl text-gray-800 font-medium"></h3>
      <div class="flex flex-col lg:grid grid-cols-3 gap-5 py-12 md:py-20">
        <HomeReview v-for="(review, index) in reviews" :key="index" :review />
      </div>
    </div>
  </section>
</template>
