<script setup>
  const page = ref(null);

  try {
    const response = await useApiFetch('/general/default-pages/privacy-policy');

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

<style lang="postcss" scoped>
  .ck-content :deep(iframe) {
    width: 100% !important;
    overflow: hidden;
  }

  .ck-content :deep(div) {
    @apply dark:text-gray-200 flex flex-col gap-3 mt-3;
  }

  .ck-content :deep(p),
  .ck-content :deep(h1),
  .ck-content :deep(h2),
  .ck-content :deep(h3),
  .ck-content :deep(h4),
  .ck-content :deep(h5),
  .ck-content :deep(h6) {
    scroll-margin-top: 8rem;
    font-size: 17px;
    font-weight: revert;
  }

  .ck-content :deep(ol),
  .ck-content :deep(ul),
  .ck-content :deep(li) {
    padding: revert;
    list-style: revert;
    @apply marker:text-brand-500 dark:marker:text-brand-300;
  }

  .ck-content :deep(h2),
  .ck-content :deep(h3),
  .ck-content :deep(h4) {
    @apply border-s-[3px] text-base rtl:bg-gradient-to-l ltr:bg-gradient-to-r from-gray-100/80 dark:from-gray-700/90 to-transparent w-full leading-10 px-2 dark:text-gray-200;
  }

  .ck-content :deep(h2) {
    @apply border-teal-500 dark:border-teal-300/70;
  }

  .ck-content :deep(h3) {
    @apply border-blue-500 dark:border-blue-300/70;
  }

  .ck-content :deep(h4) {
    @apply border-violet-500 dark:border-violet-300/70;
  }

  .ck-content :deep(ol),
  .ck-content :deep(ul),
  .ck-content :deep(p) {
    @apply leading-8;
  }

  .ck-content :deep(.image.image_resized img) {
    width: 100% !important;
  }

  .ck-content:not(.ck-editor__editable) figure.table {
    @apply block overflow-auto;
  }

  .ck-content:not(.ck-editor__editable) figure.table::-webkit-scrollbar {
    width: 5px;
    height: 5px;
  }

  /* Handle & Track */
  .ck-content:not(.ck-editor__editable) figure.table::-webkit-scrollbar-track {
    @apply bg-transparent;
  }

  .ck-content:not(.ck-editor__editable) figure.table::-webkit-scrollbar-thumb {
    @apply bg-gray-300 dark:bg-gray-600;
  }

  .ck-content :deep(table) {
    @apply w-full border-0 !important;
  }

  .ck-content :deep(table th) {
    @apply bg-cyan-800/10 dark:bg-gray-700 text-gray-700 dark:text-gray-200 text-sm sm:text-base p-1.5 sm:p-3 text-start border border-gray-200 dark:border-gray-600 !important;
  }

  .ck-content :deep(table strong),
  .ck-content :deep(table th) {
    @apply text-sm !important;
  }

  .ck-content :deep(table tbody tr td) {
    @apply text-sm sm:text-base p-1.5 sm:p-3 text-gray-600 dark:text-gray-300 bg-gray-50 dark:bg-gray-700/40 text-start border border-gray-200 dark:border-gray-600 !important;
  }

  .ck-content:not(.ck-editor__editable) blockquote {
    @apply p-4 border-s-4 border-gray-300 bg-gray-50 dark:border-gray-500 dark:bg-gray-700/40 italic;
  }

  /*
  .ck-content table tbody tr:nth-child(even) {
    @apply bg-cyan-800/5 !important;
  } */

  .ck-content a:not(.country-link, .content-internal-link) {
    @apply text-blue-500 hover:text-blue-500;
  }

  .ck-powered-by-balloon {
    z-index: 10 !important;
  }
</style>
