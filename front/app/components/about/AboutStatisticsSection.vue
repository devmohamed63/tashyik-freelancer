<template>
  <section class="bg-brand-500">
    <div class="container px-4 py-16 md:py-28 flex flex-col gap-5 text-center">
      <div class="grid md:grid-cols-3 gap-x-14 gap-y-20 text-white">
        <!-- Stats 1 -->
        <div class="flex flex-col gap-3">
          <span v-text="$t('about.stats_1.title')" class="text-3xl md:text-4xl font-bold"></span>
          <span v-text="$t('about.stats_1.subtitle')" class="text-lg md:text-xl"></span>
        </div>

        <!-- Stats 2 -->
        <div class="flex flex-col gap-3">
          <span v-text="$t('about.stats_2.title')" class="text-3xl md:text-4xl font-bold"></span>
          <span v-text="$t('about.stats_2.subtitle')" class="text-lg md:text-xl"></span>
        </div>

        <!-- Stats 3 -->
        <div class="flex flex-col gap-3">
          <span v-text="$t('about.stats_3.title')" class="text-3xl md:text-4xl font-bold"></span>
          <span v-text="$t('about.stats_3.subtitle')" class="text-lg md:text-xl"></span>
        </div>
      </div>
    </div>
  </section>
</template>
