<script setup>
  const { data, error } = useApiFetch('/categories', {}, true);

  watch(error, (e) => {
    if (e) console.error('Failed to load categories:', e);
  });

  const categories = computed(() => data.value?.data ?? null);
  const categoryContainer = ref(null);
  const swiper = ref(null);

  const categoryChunks = computed(() => {
    const chunks = [];
    const list = categories.value;
    for (let i = 0; i < list?.length; i += 6) {
      chunks.push(list?.slice(i, i + 6));
    }
    return chunks;
  });

  swiper.value = useSwiper(categoryContainer, {
    slidesPerView: 1,
    spaceBetween: 22,
    grabCursor: true,
    pagination: true,
  });
</script>

<template>
  <section class="py-14 md:py-26">
    <div class="container flex flex-col gap-5">
      <div class="flex flex-row gap-3 sm:gap-5 justify-between items-center flex-wrap">
        <h2 v-text="$t('home.categories.title')" class="text-xl lg:text-4xl font-medium text-gray-800"></h2>
        <ButtonsOutline class="max-md:hidden min-h-12 min-w-12 inline-flex items-center justify-center">
          <NuxtLinkLocale :to="{ name: 'categories' }" class="inline-flex items-center justify-center min-h-12 px-2">
            {{ $t('home.categories.action') }}
          </NuxtLinkLocale>
        </ButtonsOutline>
      </div>

      <div v-if="!categoryContainer">
        <div class="w-full h-[260px] md:h-[500px] rounded-2xl bg-gray-100 animate-pulse flex flex-col items-center justify-center p-8 gap-2">
          <div class="bg-gray-200 w-16 h-16 rounded-lg mb-3"></div>
          <div class="h-5 w-40 rounded-full bg-gray-200"></div>
          <div class="h-5 w-64 rounded-full bg-gray-200"></div>
        </div>
      </div>

      <ClientOnly>
        <div class="relative">
          <swiper-container ref="categoryContainer" id="categoriesCarousel">
            <swiper-slide v-for="(chunk, index) in categoryChunks" :key="index">
              <div class="grid grid-cols-3 gap-1 md:gap-5">
                <CategoryLink v-for="category in chunk" :key="category.id" :category="category" :swiper="true" />
              </div>
            </swiper-slide>
          </swiper-container>
          <!-- Previous button -->
          <button class="max-md:hidden hover:bg-brand-50 z-10 absolute top-[30%] -start-4 flex items-center justify-center w-12 h-12 rounded-full border border-brand-500 text-brand-500">
            <!-- mdi:chevron-right -->
            <svg @click="swiper.prev()" class="w-8 h-8 ltr:rotate-180" xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24">
              <!-- Icon from Material Design Icons by Pictogrammers - https://github.com/Templarian/MaterialDesign/blob/master/LICENSE -->
              <path fill="currentColor" d="M8.59 16.58L13.17 12L8.59 7.41L10 6l6 6l-6 6z" />
            </svg>
          </button>

          <!-- Next button -->
          <button @click="swiper.next()" class="max-md:hidden hover:bg-brand-50 z-10 absolute top-[30%] -end-4 flex items-center justify-center w-12 h-12 rounded-full border border-brand-500 text-brand-500">
            <!-- mdi:chevron-right -->
            <svg class="w-8 h-8 rtl:rotate-180" xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24">
              <!-- Icon from Material Design Icons by Pictogrammers - https://github.com/Templarian/MaterialDesign/blob/master/LICENSE -->
              <path fill="currentColor" d="M8.59 16.58L13.17 12L8.59 7.41L10 6l6 6l-6 6z" />
            </svg>
          </button>
        </div>
      </ClientOnly>

      <ButtonsOutline class="md:hidden block mx-auto min-h-12 min-w-12">
        <NuxtLinkLocale :to="{ name: 'categories' }" class="inline-flex items-center justify-center min-h-12 w-full">
          {{ $t('home.categories.action') }}
        </NuxtLinkLocale>
      </ButtonsOutline>
    </div>
  </section>
</template>

<style scoped>
  @reference "tailwindcss";

  #categoriesCarousel {
    width: 100%;
    @apply h-[260px] md:h-[500px];
    --swiper-pagination-color: #724193;
  }

  #categoriesCarousel::part(pagination) {
    overflow: hidden;
    white-space: nowrap;
  }
</style>
