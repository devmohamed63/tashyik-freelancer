<script setup>
  const { t } = useI18n();

  useSeoMeta({
    title: t('seo.about.title'),
    ogTitle: t('seo.about.title'),
    description: t('seo.about.description'),
    ogDescription: t('seo.about.description'),
  });
</script>

<template>
  <!-- Hero Section -->
  <AboutHeroSection />

  <!-- Story Section -->
  <AboutStorySection />

  <!-- Features -->
  <AboutFeaturesSection />

  <!-- Statistics -->
  <AboutStatisticsSection />

  <!-- Audience section -->
  <AboutAudienceSection />

  <!-- Ready to join section -->
  <ReadyToJoinSection :filled="false" />
</template>
