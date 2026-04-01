<script setup>
  const { t } = useI18n();

  useSeoMeta({
    title: t('seo.contact.title'),
    ogTitle: t('seo.contact.title'),
    description: t('seo.contact.description'),
    ogDescription: t('seo.contact.description'),
  });
</script>

<template>
  <!-- Hero Section -->
  <ContactHeroSection />

  <!-- Contact -->
  <ContactSection />

  <!-- Map -->
  <ContactMap />
</template>
