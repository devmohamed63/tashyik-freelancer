<script setup>
  defineProps(['category', 'swiper']);
</script>

<template>
  <NuxtLinkLocale :to="{ name: 'categories-category', params: { category: category.slug } }" :class="[swiper ? 'px-2 py-3 md:px-8 md:py-8' : 'p-8']" class="relative flex flex-col items-center gap-2 bg-white hover:bg-brand-50 rounded-2xl border border-gray-50 shadow-sm mx-1 mb-1 mt-4 md:mt-5">
    <img :src="category.image" :alt="$t('alt.category', { category: category.name })" :class="[swiper ? 'w-11 h-11 md:w-16 md:h-16 mb-1 md:mb-3' : 'w-16 h-16 mb-3']" class="rounded-lg" loading="lazy" />
    <h3 v-text="category.name" :class="[swiper ? 'text-sm truncate w-full md:text-lg' : 'text-lg']" class="text-center text-gray-800 font-medium"></h3>
    <p v-text="category.description" :class="[swiper ? 'hidden md:block' : '']" class="w-full text-gray-600 truncate"></p>
    <span v-if="category.badge" class="absolute -top-3.5 md:-top-4 rtl:left-4 ltr:right-4 inline-flex items-center gap-1 bg-brand-50 text-brand-700 font-medium px-2.5 md:px-3 py-1 md:py-1.5 rounded shadow-sm border border-brand-100 z-10 w-fit text-xs md:text-sm whitespace-nowrap">
      <svg class="w-3.5 h-3.5 text-brand-500" fill="currentColor" viewBox="0 0 20 20">
        <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
      </svg>
      {{ category.badge }}
    </span>
  </NuxtLinkLocale>
</template>
