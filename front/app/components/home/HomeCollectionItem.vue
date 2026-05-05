<script setup>
  const props = defineProps({
    collection: {
      type: Object,
      required: true,
    },
    index: {
      type: Number,
      required: true,
    },
    maxServices: {
      type: Number,
      default: 10,
    },
  });

  const swiperContainer = ref(null);
  const swiper = ref(null);

  swiper.value = useSwiper(swiperContainer, {
    slidesPerView: 1.3,
    spaceBetween: 12,
    grabCursor: true,
    breakpoints: {
      640: {
        slidesPerView: 2.3,
        spaceBetween: 16,
      },
      1024: {
        slidesPerView: 3,
        spaceBetween: 16,
      },
      1280: {
        slidesPerView: 4,
        spaceBetween: 20,
      },
    },
  });
</script>

<template>
  <section
    :class="[index % 2 === 0 ? 'bg-white' : 'bg-brand-50']"
    class="py-14 md:py-20"
  >
    <div class="container flex flex-col gap-6">
      <!-- Header -->
      <div class="flex items-center justify-between">
        <h2 class="text-xl lg:text-3xl font-medium text-gray-800">
          {{ collection.title }}
        </h2>
      </div>

      <!-- Skeleton Loader -->
      <div v-if="!swiperContainer" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <div v-for="n in 4" :key="n" class="bg-gray-100 rounded-2xl animate-pulse">
          <div class="aspect-video bg-gray-200 rounded-t-2xl"></div>
          <div class="p-4 flex flex-col gap-3">
            <div class="h-4 w-3/4 bg-gray-200 rounded-full"></div>
            <div class="h-3 w-1/3 bg-gray-200 rounded-full"></div>
            <div class="h-5 w-1/2 bg-gray-200 rounded-full"></div>
          </div>
        </div>
      </div>

      <!-- Swiper -->
      <ClientOnly>
        <div class="relative">
          <swiper-container ref="swiperContainer" :id="`collectionSwiper-${index}`">
            <swiper-slide
              v-for="service in collection.services?.slice(0, maxServices)"
              :key="service.id"
            >
              <CollectionServiceCard :service="service" />
            </swiper-slide>
          </swiper-container>

          <!-- Navigation Arrows -->
          <button
            @click="swiper?.prev()"
            class="max-md:hidden hover:bg-brand-50 z-10 absolute top-[35%] -start-4 flex items-center justify-center w-10 h-10 rounded-full border border-brand-500 text-brand-500 bg-white shadow-sm"
          >
            <svg class="w-6 h-6 ltr:rotate-180" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24">
              <path fill="currentColor" d="M8.59 16.58L13.17 12L8.59 7.41L10 6l6 6l-6 6z" />
            </svg>
          </button>

          <button
            @click="swiper?.next()"
            class="max-md:hidden hover:bg-brand-50 z-10 absolute top-[35%] -end-4 flex items-center justify-center w-10 h-10 rounded-full border border-brand-500 text-brand-500 bg-white shadow-sm"
          >
            <svg class="w-6 h-6 rtl:rotate-180" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24">
              <path fill="currentColor" d="M8.59 16.58L13.17 12L8.59 7.41L10 6l6 6l-6 6z" />
            </svg>
          </button>
        </div>
      </ClientOnly>
    </div>
  </section>
</template>

<style scoped>
  @reference "tailwindcss";

  [id^="collectionSwiper-"] {
    width: 100%;
    padding-bottom: 8px;
  }
</style>
