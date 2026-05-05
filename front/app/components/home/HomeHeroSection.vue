<script setup>
  const salesIq = useSalesIq();

  /** Primary display ~622×512 (mobile / split layout); full asset for large viewports. */
  const heroSrcDefault = '/images/home-hero-section-622.webp';
  const heroSrcLarge = '/images/home-hero-section.webp';
  const heroWidth = 622;
  const heroHeight = 512;
  const heroWidthLarge = 1176;
  const heroHeightLarge = 968;
</script>

<template>
  <section class="bg-brand-50">
    <div class="container px-4 flex flex-col lg:grid grid-cols-2 py-10">
      <div class="block my-auto">
        <div class="flex flex-col gap-4 md:gap-5 xl:gap-8">
          <h1 class="text-2xl md:text-4xl xl:text-5xl flex flex-col gap-2 md:gap-4 text-gray-800 font-medium my-auto h-full">
            <span v-text="$t('home.hero.title')"></span>
            <div class="inline-flex gap-3">
              <span v-text="$t('home.hero.subtitle.part_1')"></span>
              <span v-text="$t('home.hero.subtitle.part_2')" class="text-brand-500"></span>
            </div>
          </h1>
          <p v-text="$t('home.hero.description')" class="text-gray-600 text-lg md:text-xl xl:text-2xl leading-8"></p>
          <div class="inline-flex flex-wrap gap-3 sm:gap-4 items-center">
            <NuxtLinkLocale
              :to="{ name: 'categories' }"
              class="text-sm md:text-base inline-flex items-center justify-center gap-2 leading-[26px] font-normal min-h-12 cursor-pointer rounded-lg bg-brand-500 px-5 py-3 text-center text-white hover:bg-brand-600 focus:outline-none md:px-7 static-color [text-wrap:balance]"
            >
              {{ $t('home.hero.actions.browse_categories') }}
            </NuxtLinkLocale>
            <ButtonsOutline class="min-h-12" @click="salesIq.openChat">
              {{ $t('home.hero.actions.request_custom_service') }}
            </ButtonsOutline>
          </div>
        </div>
      </div>
      <div class="relative w-fit mt-20 mb-12 lg:m-5 md:p-5">
        <div class="flex flex-col gap-3 items-center bg-white rounded-xl shadow absolute -top-10 -start-3 lg:-top-6 lg:-start-6 p-5 px-5 md:px-10">
          <svg class="w-5 h-5 xl:w-7 xl:h-7 stroke-brand-500" width="30" height="30" viewBox="0 0 30 30" fill="none" xmlns="http://www.w3.org/2000/svg">
            <path
              d="M14.4063 2.86874C14.4611 2.75807 14.5457 2.6649 14.6506 2.59977C14.7555 2.53464 14.8766 2.50012 15.0001 2.50012C15.1235 2.50012 15.2446 2.53464 15.3495 2.59977C15.4544 2.6649 15.539 2.75807 15.5938 2.86874L18.4813 8.71749C18.6715 9.10245 18.9523 9.4355 19.2996 9.68806C19.6468 9.94061 20.0502 10.1051 20.4751 10.1675L26.9326 11.1125C27.0549 11.1302 27.1699 11.1818 27.2644 11.2615C27.359 11.3411 27.4293 11.4457 27.4676 11.5632C27.5058 11.6808 27.5104 11.8067 27.4808 11.9268C27.4512 12.0468 27.3886 12.1562 27.3001 12.2425L22.6301 16.79C22.3221 17.0901 22.0916 17.4606 21.9586 17.8695C21.8256 18.2785 21.7939 18.7136 21.8663 19.1375L22.9688 25.5625C22.9904 25.6848 22.9772 25.8107 22.9307 25.9258C22.8842 26.041 22.8062 26.1408 22.7057 26.2137C22.6053 26.2867 22.4863 26.33 22.3624 26.3386C22.2385 26.3472 22.1147 26.3209 22.0051 26.2625L16.2326 23.2275C15.8522 23.0278 15.429 22.9234 14.9994 22.9234C14.5698 22.9234 14.1467 23.0278 13.7663 23.2275L7.99505 26.2625C7.88547 26.3205 7.7618 26.3466 7.63812 26.3378C7.51443 26.329 7.3957 26.2857 7.29542 26.2128C7.19514 26.1398 7.11734 26.0402 7.07087 25.9253C7.0244 25.8103 7.01113 25.6846 7.03255 25.5625L8.1338 19.1387C8.20655 18.7147 8.17503 18.2792 8.04198 17.87C7.90892 17.4609 7.67831 17.0902 7.37005 16.79L2.70005 12.2437C2.61079 12.1575 2.54754 12.048 2.51751 11.9276C2.48747 11.8072 2.49185 11.6808 2.53015 11.5627C2.56845 11.4447 2.63914 11.3398 2.73415 11.26C2.82916 11.1801 2.94468 11.1286 3.06755 11.1112L9.5238 10.1675C9.94912 10.1056 10.353 9.94131 10.7008 9.68872C11.0485 9.43614 11.3297 9.10283 11.5201 8.71749L14.4063 2.86874Z"
              stroke-width="2.60965"
              stroke-linecap="round"
              stroke-linejoin="round"
            />
          </svg>
          <span v-text="$t('home.hero.highlight_1')" class="text-xs md:text-base text-gray-500"></span>
        </div>
        <img
          class="max-h-[500px] w-auto h-auto"
          :src="heroSrcDefault"
          :srcset="`${heroSrcDefault} ${heroWidth}w, ${heroSrcLarge} ${heroWidthLarge}w`"
          :sizes="`(max-width: 1024px) min(100vw - 2rem, ${heroWidth}px), min(50vw - 3rem, ${heroWidthLarge}px)`"
          :width="heroWidth"
          :height="heroHeight"
          fetchpriority="high"
          decoding="sync"
          :alt="$t('common.brand')"
        />
        <div class="flex flex-col gap-3 items-center bg-white rounded-xl shadow absolute -bottom-10 -end-3 lg:-bottom-6 md:-end-6 p-5 px-5 md:px-10">
          <svg class="w-5 h-5 xl:w-7 xl:h-7 stroke-brand-500" width="30" height="30" viewBox="0 0 30 30" fill="none" xmlns="http://www.w3.org/2000/svg">
            <path d="M15 27.5C21.9036 27.5 27.5 21.9036 27.5 15C27.5 8.09644 21.9036 2.5 15 2.5C8.09644 2.5 2.5 8.09644 2.5 15C2.5 21.9036 8.09644 27.5 15 27.5Z" stroke-width="2.60965" stroke-linecap="round" stroke-linejoin="round" />
            <path d="M15 7.5V15L20 17.5" stroke-width="2.60965" stroke-linecap="round" stroke-linejoin="round" />
          </svg>
          <span v-text="$t('home.hero.highlight_2')" class="text-xs md:text-base text-gray-500"></span>
        </div>
      </div>
    </div>
  </section>
</template>
