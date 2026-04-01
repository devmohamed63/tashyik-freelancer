<script setup>
  defineProps(['category', 'swiper']);
</script>

<template>
  <NuxtLinkLocale :to="{ name: 'categories-category', params: { category: category.id } }" :class="[swiper ? 'px-2 py-3 md:px-8 md:py-8' : 'p-8']" class="flex flex-col items-center gap-2 bg-white hover:bg-brand-50 rounded-2xl border border-gray-50 shadow-sm m-1">
    <img :src="category.image" :alt="$t('alt.category', { category: category.name })" :class="[swiper ? 'w-11 h-11 md:w-16 md:h-16 mb-1 md:mb-3' : 'w-16 h-16 mb-3']" class="rounded-lg" loading="lazy" />
    <h3 v-text="category.name" :class="[swiper ? 'text-sm truncate w-full md:text-lg' : 'text-lg']" class="text-center text-gray-800 font-medium"></h3>
    <p v-text="category.description" :class="[swiper ? 'hidden md:block' : '']" class="w-full text-gray-600 truncate"></p>
  </NuxtLinkLocale>
</template>
