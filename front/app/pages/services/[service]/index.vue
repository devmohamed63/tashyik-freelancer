<script setup>
  const { t } = useI18n();
  const route = useRoute();
  const service = ref(null);
  const galleryContainer = ref(null);

  try {
    const response = await useApiFetch(`/services/${route.params.service}`);

    service.value = response.data;
  } catch (error) {
    if (error.statusCode == 404) {
      throw createError({
        statusCode: 404,
        statusMessage: $t('common.page_not_found'),
        fatal: true,
      });
    }

    console.error('Failed to get service:', error);
  }

  useSeoMeta({
    title: t('alt.service', { service: service.value?.name }),
    ogTitle: t('alt.service', { service: service.value?.name }),
    description: service.value?.description,
    ogDescription: service.value?.description,
  });

  useSwiper(galleryContainer, {
    slidesPerView: 1,
    grabCursor: true,
    pagination: true,
  });
</script>

<template>
  <div class="container px-2 py-8 md:py-12 flex flex-col gap-6">
    <AppBreadcrumb :pages="[{ name: service.name }]" />

    <div v-if="!galleryContainer">
      <div class="w-full aspect-video md:aspect-16/6 rounded-xl bg-gray-300 animate-pulse"></div>
    </div>

    <!-- Gallery -->
    <ClientOnly>
      <swiper-container ref="galleryContainer" id="galleryCarousel">
        <swiper-slide v-for="(image, index) of service.gallery" :key="index">
          <img :src="image" class="w-full aspect-video md:aspect-16/6 object-center object-cover rounded-xl" alt="..." loading="lazy" />
        </swiper-slide>
      </swiper-container>
    </ClientOnly>

    <div class="flex flex-col lg:grid grid-cols-12 gap-6">
      <div class="col-span-7 xl:col-span-8 flex flex-col gap-6">
        <!-- Info section -->
        <section class="lg:bg-white lg:p-5 rounded-xl lg:shadow flex flex-col gap-4">
          <div class="flex flex-wrap gap-2 justify-between">
            <!-- Name -->
            <h1 v-text="service.name" class="text-xl text-gray-800 font-medium"></h1>

            <!-- Rating -->
            <div class="inline-flex items-center justify-center gap-2">
              <svg class="w-4 h-4 mb-1 fill-yellow-500 stroke-yellow-500" viewBox="0 0 18 17" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path
                  d="M8.439 0.745745C8.47552 0.671962 8.53193 0.609855 8.60188 0.566432C8.67182 0.52301 8.75251 0.5 8.83483 0.5C8.91716 0.5 8.99785 0.52301 9.06779 0.566432C9.13773 0.609855 9.19415 0.671962 9.23067 0.745745L11.1557 4.64491C11.2825 4.90155 11.4697 5.12359 11.7012 5.29196C11.9327 5.46033 12.2016 5.57001 12.4848 5.61158L16.7898 6.24158C16.8714 6.2534 16.948 6.28781 17.0111 6.34091C17.0741 6.39402 17.121 6.4637 17.1465 6.54208C17.172 6.62047 17.1751 6.70442 17.1553 6.78444C17.1356 6.86447 17.0938 6.93737 17.0348 6.99491L13.9215 10.0266C13.7162 10.2267 13.5626 10.4736 13.4739 10.7463C13.3852 11.0189 13.3641 11.309 13.4123 11.5916L14.1473 15.8749C14.1617 15.9564 14.1529 16.0404 14.1219 16.1171C14.0909 16.1939 14.0389 16.2604 13.972 16.3091C13.905 16.3577 13.8256 16.3866 13.7431 16.3923C13.6605 16.3981 13.5779 16.3805 13.5048 16.3416L9.6565 14.3182C9.40293 14.1851 9.12082 14.1155 8.83442 14.1155C8.54802 14.1155 8.2659 14.1851 8.01233 14.3182L4.16483 16.3416C4.09178 16.3803 4.00933 16.3977 3.92688 16.3918C3.84442 16.3859 3.76526 16.357 3.69841 16.3084C3.63156 16.2598 3.57969 16.1934 3.54871 16.1168C3.51773 16.0401 3.50888 15.9563 3.52317 15.8749L4.25733 11.5924C4.30583 11.3097 4.28482 11.0194 4.19611 10.7466C4.10741 10.4738 3.95367 10.2267 3.74817 10.0266L0.634833 6.99575C0.575328 6.93827 0.533161 6.86524 0.513135 6.78497C0.493109 6.70471 0.496031 6.62043 0.521565 6.54174C0.5471 6.46305 0.594222 6.39311 0.657564 6.3399C0.720905 6.28668 0.797919 6.25233 0.879833 6.24075L5.184 5.61158C5.46755 5.57033 5.73682 5.46079 5.96865 5.2924C6.20048 5.12401 6.38792 4.9018 6.51483 4.64491L8.439 0.745745Z"
                  stroke-linecap="round"
                  stroke-linejoin="round"
                />
              </svg>
              <span v-text="service.rating" class="text-gray-700 font-medium"></span>
            </div>
          </div>

          <!-- Warranty -->
          <WarrantyBadge v-if="service.warranty_duration" :duration="service.warranty_duration" />

          <!-- Description -->
          <p v-text="service.description" class="text-gray-500"></p>
        </section>

        <!-- Highlights section -->
        <section class="bg-white p-5 md:p-8 rounded-xl shadow flex flex-col gap-5">
          <h3 v-text="$t('service.highlights.title')" class="text-lg text-gray-800 font-medium"></h3>
          <div v-for="highlight of service.highlights" :key="highlight.id" class="inline-flex gap-2 items-center">
            <span class="w-7 h-7 bg-green-50 stroke-green-500 flex items-center justify-center rounded-full">
              <svg class="w-4 h-4" viewBox="0 0 13 10" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path d="M11.8992 0.699951L4.19922 8.39995L0.699219 4.89995" stroke-width="1.4" stroke-linecap="round" stroke-linejoin="round" />
              </svg>
            </span>
            <span v-text="highlight.title" class="text-gray-500"></span>
          </div>
        </section>

        <!-- Questions section -->
        <section class="bg-white p-5 md:p-8 rounded-xl shadow flex flex-col gap-5">
          <h3 v-text="$t('home.questions.title')" class="text-lg text-gray-800 font-medium"></h3>
          <QuestionCollapse class="divide-y divide-gray-200" title-class="text-gray-500 p-3" body-class="p-3" />
        </section>
      </div>

      <div class="col-span-5 xl:col-span-4">
        <!-- Summary section -->
        <section class="bg-white p-5 rounded-xl shadow flex flex-col gap-4">
          <!-- Promotion -->
          <AppBadge type="success" v-if="service.price.has_discount" :title="$t('common.promotion', { percentage: service.price.discount_percintage })" />

          <!-- Price -->
          <div class="flex flex-wrap items-center justify-between gap-2">
            <div v-if="service.price.original > 0" class="inline-flex items-end gap-3">
              <span v-text="`${service.price.after_discount} ${service.price.currency}`" class="text-2xl font-medium text-gray-800"></span>
              <del v-if="service.price.has_discount" v-text="`${service.price.original} ${service.price.currency}`" class="text-lg font-medium text-gray-400"></del>
            </div>
            <span v-else v-text="$t('common.no_price')" class="text-lg text-gray-800"></span>
          </div>

          <hr class="border-dashed border-gray-200" />

          <NuxtLinkLocale :to="{ name: 'services-service-order', params: { service: service.id } }" class="w-full">
            <ButtonsFilled class="w-full">
              {{ $t('service.order_service') }}
            </ButtonsFilled>
          </NuxtLinkLocale>

          <hr class="border-dashed border-gray-200" />

          <div class="inline-flex gap-2">
            <svg class="w-5 h-5 stroke-brand-500" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
              <path d="M10.0003 18.3333C14.6027 18.3333 18.3337 14.6023 18.3337 9.99996C18.3337 5.39759 14.6027 1.66663 10.0003 1.66663C5.39795 1.66663 1.66699 5.39759 1.66699 9.99996C1.66699 14.6023 5.39795 18.3333 10.0003 18.3333Z" stroke-width="1.66667" stroke-linecap="round" stroke-linejoin="round" />
              <path d="M10 5V10L13.3333 11.6667" stroke-width="1.66667" stroke-linecap="round" stroke-linejoin="round" />
            </svg>
            <span v-text="$t('service.highlight_1')" class="text-gray-500"></span>
          </div>
          <div class="inline-flex gap-2">
            <svg class="w-5 h-5 stroke-brand-500" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
              <path d="M16.6663 10.8333C16.6663 15 13.7497 17.0833 10.283 18.2916C10.1015 18.3532 9.90429 18.3502 9.72467 18.2833C6.24967 17.0833 3.33301 15 3.33301 10.8333V4.99997C3.33301 4.77895 3.42081 4.56699 3.57709 4.41071C3.73337 4.25443 3.94533 4.16663 4.16634 4.16663C5.83301 4.16663 7.91634 3.16663 9.36634 1.89997C9.54289 1.74913 9.76747 1.66626 9.99967 1.66626C10.2319 1.66626 10.4565 1.74913 10.633 1.89997C12.0913 3.17497 14.1663 4.16663 15.833 4.16663C16.054 4.16663 16.266 4.25443 16.4223 4.41071C16.5785 4.56699 16.6663 4.77895 16.6663 4.99997V10.8333Z" stroke-width="1.66667" stroke-linecap="round" stroke-linejoin="round" />
            </svg>
            <span v-text="$t('service.highlight_2')" class="text-gray-500"></span>
          </div>
          <div class="inline-flex gap-2">
            <svg class="w-5 h-5 stroke-brand-500" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
              <path
                d="M9.60404 1.91249C9.64056 1.83871 9.69697 1.7766 9.76692 1.73318C9.83686 1.68976 9.91755 1.66675 9.99987 1.66675C10.0822 1.66675 10.1629 1.68976 10.2328 1.73318C10.3028 1.7766 10.3592 1.83871 10.3957 1.91249L12.3207 5.81166C12.4475 6.0683 12.6347 6.29033 12.8662 6.4587C13.0977 6.62707 13.3666 6.73675 13.6499 6.77833L17.9549 7.40833C18.0364 7.42014 18.1131 7.45455 18.1761 7.50766C18.2391 7.56076 18.2861 7.63045 18.3116 7.70883C18.337 7.78721 18.3401 7.87116 18.3204 7.95119C18.3006 8.03121 18.2589 8.10412 18.1999 8.16166L15.0865 11.1933C14.8812 11.3934 14.7276 11.6404 14.6389 11.913C14.5502 12.1856 14.5291 12.4757 14.5774 12.7583L15.3124 17.0417C15.3268 17.1232 15.318 17.2071 15.287 17.2839C15.2559 17.3607 15.204 17.4272 15.137 17.4758C15.07 17.5245 14.9907 17.5533 14.9081 17.5591C14.8255 17.5648 14.743 17.5472 14.6699 17.5083L10.8215 15.485C10.568 15.3518 10.2859 15.2823 9.99946 15.2823C9.71306 15.2823 9.43094 15.3518 9.17737 15.485L5.32987 17.5083C5.25681 17.547 5.17437 17.5644 5.09191 17.5585C5.00946 17.5527 4.9303 17.5238 4.86345 17.4752C4.7966 17.4266 4.74473 17.3601 4.71375 17.2835C4.68277 17.2069 4.67392 17.1231 4.68821 17.0417L5.42237 12.7592C5.47087 12.4764 5.44986 12.1862 5.36115 11.9134C5.27245 11.6406 5.11871 11.3935 4.91321 11.1933L1.79987 8.16249C1.74037 8.10502 1.6982 8.03199 1.67817 7.95172C1.65815 7.87145 1.66107 7.78717 1.6866 7.70848C1.71214 7.6298 1.75926 7.55986 1.8226 7.50665C1.88594 7.45343 1.96296 7.41907 2.04487 7.40749L6.34904 6.77833C6.63259 6.73708 6.90186 6.62754 7.13369 6.45915C7.36552 6.29076 7.55296 6.06855 7.67987 5.81166L9.60404 1.91249Z"
                stroke-width="1.66667"
                stroke-linecap="round"
                stroke-linejoin="round"
              />
            </svg>
            <span v-text="$t('service.highlight_3')" class="text-gray-500"></span>
          </div>
        </section>
      </div>
    </div>
  </div>
</template>

<style scoped>
  #galleryCarousel {
    width: 100%;
    --swiper-pagination-color: #724193;
  }
</style>
