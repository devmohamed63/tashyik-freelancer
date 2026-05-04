<script setup>
  const { description } = useLayoutStore();

  const heroImageSrc = '/images/home-hero-section.webp';

  useHead({
    link: [
      {
        rel: 'preload',
        as: 'image',
        href: heroImageSrc,
        fetchpriority: 'high',
      },
    ],
  });

  useSeoMeta({
    description: description,
    ogDescription: description,
  });
</script>

<template>
  <!-- Hero Section -->
  <HomeHeroSection />

  <!-- Featured Collection (first one, teaser) -->
  <HomeCollectionsSection :start="0" :end="1" :max-services="5" />

  <!-- Categories Section -->
  <HomeCategoriesSection />

  <!-- Remaining Collections -->
  <HomeCollectionsSection :start="1" :max-services="10" />

  <!-- Features -->
  <HomeFeaturesSection />

  <!-- Reviews Section -->
  <HomeReviewsSection />

  <!-- Download App -->
  <DownloadAppSection />

  <!-- Questions Section -->
  <HomeQuestionsSection />
</template>
