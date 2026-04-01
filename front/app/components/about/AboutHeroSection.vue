<template>
  <section class="bg-gray-50">
    <div class="container px-4 flex flex-col lg:grid grid-cols-2 py-10">
      <div class="block my-auto">
        <div class="flex flex-col gap-4 md:gap-5 xl:gap-8">
          <h1 class="inline-flex items-center w-fit font-medium bg-brand-50 border border-brand-100 py-1.5 px-4 rounded-full text-brand-500">
            <svg class="w-[9px] h-[9px] fill-brand-500 me-2" viewBox="0 0 10 9" fill="none" xmlns="http://www.w3.org/2000/svg">
              <path d="M0 4C0 1.79086 1.79086 0 4 0C6.20914 0 8 1.79086 8 4C8 6.20914 6.20914 8 4 8C1.79086 8 0 6.20914 0 4Z" />
            </svg>
            {{ $t('about.hero.title') }}
          </h1>
          <div class="text-2xl md:text-4xl xl:text-5xl inline-flex gap-3">
            <span v-text="$t('about.hero.subtitle.part_1')"></span>
            <span v-text="$t('about.hero.subtitle.part_2')" class="text-brand-500"></span>
          </div>
          <p v-text="$t('about.hero.description')" class="text-gray-500 text-lg md:text-xl xl:text-2xl leading-9"></p>
          <div class="inline-flex gap-3 sm:gap-5 whitespace-nowrap overflow-hidden">
            <ButtonsFilled>
              <NuxtLinkLocale :to="{ name: 'about', hash: '#story-section' }" class="static-color">
                {{ $t('about.hero.actions.discover') }}
              </NuxtLinkLocale>
            </ButtonsFilled>
            <img class="h-12" src="/images/about-hero-section-discover.webp" loading="lazy" alt="Discover" />
          </div>
        </div>
      </div>
      <div class="relative w-fit mt-10 mb-12 lg:m-5 lg:mt-0 md:p-5">
        <img class="max-h-[500px]" src="/images/about-hero-section.webp" alt="About hero section" />
        <div class="flex flex-row gap-3 items-center bg-white rounded-xl shadow absolute -bottom-10 -start-3 lg:-bottom-6 md:-start-6 p-5 px-5 md:px-10">
          <svg class="w-11 h-11 bg-green-100 stroke-green-500 p-2 rounded-lg" viewBox="0 0 28 28" fill="none" xmlns="http://www.w3.org/2000/svg">
            <path d="M23.3332 15.1666C23.3332 21 19.2498 23.9166 14.3965 25.6083C14.1424 25.6944 13.8663 25.6903 13.6148 25.5966C8.74984 23.9166 4.6665 21 4.6665 15.1666V6.99995C4.6665 6.69054 4.78942 6.39379 5.00821 6.175C5.22701 5.9562 5.52375 5.83329 5.83317 5.83329C8.1665 5.83329 11.0832 4.43329 13.1132 2.65995C13.3603 2.44879 13.6747 2.33276 13.9998 2.33276C14.3249 2.33276 14.6393 2.44879 14.8865 2.65995C16.9282 4.44495 19.8332 5.83329 22.1665 5.83329C22.4759 5.83329 22.7727 5.9562 22.9915 6.175C23.2103 6.39379 23.3332 6.69054 23.3332 6.99995V15.1666Z" stroke-width="2.33333" stroke-linecap="round" stroke-linejoin="round" />
            <path d="M19.3332 10L11.9998 17.3333L8.6665 14" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
          </svg>
          <div class="flex flex-col gap-1">
            <span v-text="$t('about.hero.highlight.title')" class="text-xs md:text-sm text-gray-500"></span>
            <span v-text="$t('about.hero.highlight.subtitle')" class="text-sm md:text-base text-gray-800 font-medium"></span>
          </div>
        </div>
      </div>
    </div>
  </section>
</template>
