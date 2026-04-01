<script setup>
  const { t } = useI18n();
  const route = useRoute();

  useHead({
    titleTemplate: (titleChunk) => {
      let title;

      if (route.name.startsWith('index__')) {
        title = titleChunk || `${t('common.brand')} | ${t('common.short_description')}`;
      } else {
        title = titleChunk ? `${titleChunk} - ${t('common.brand')}` : t('common.brand');
      }

      return title;
    },
    meta: [{ property: 'og:site_name', content: t('common.brand') }],
    link: [{ rel: 'icon', type: 'image/x-icon', href: '/favicon.ico' }],
  });
</script>
<template>
  <NuxtLayout>
    <NuxtPage />
  </NuxtLayout>
</template>
