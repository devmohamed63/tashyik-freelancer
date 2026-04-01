<script setup>
  const { description } = useLayoutStore();

  useSeoMeta({
    description: description,
    ogDescription: description,
  });
</script>

<template>
  <!-- Hero Section -->
  <HomeHeroSection />

  <!-- Categories Section -->
  <HomeCategoriesSection />

  <!-- Features -->
  <HomeFeaturesSection />

  <!-- Reviews Section -->
  <HomeReviewsSection />

  <!-- Download App -->
  <DownloadAppSection />

  <!-- Questions Section -->
  <HomeQuestionsSection />
</template>
