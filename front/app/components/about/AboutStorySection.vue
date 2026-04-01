<template>
  <section class="bg-brand-50" id="story-section">
    <div class="container px-4 flex flex-col lg:grid grid-cols-2 gap-10 py-12 md:py-20">
      <img class="max-h-[500px]" src="/images/about-story-section.webp" loading="lazy" alt="About story section" />
      <div class="block my-auto">
        <div class="flex flex-col gap-4 md:gap-4 xl:gap-6">
          <h3 v-text="$t('about.story.title')" class="text-gray-800 font-medium text-xl md:text-2xl xl:text-3xl"></h3>
          <p v-text="$t('about.story.paragraph_1')" class="text-gray-500 md:text-lg xl:text-xl leading-9"></p>
          <p v-text="$t('about.story.paragraph_2')" class="text-gray-500 md:text-lg xl:text-xl leading-9"></p>
        </div>
      </div>
    </div>
  </section>
</template>
