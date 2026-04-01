<template>
  <section class="bg-brand-50">
    <div class="container px-4 flex flex-col lg:grid grid-cols-2 py-10">
      <div class="block my-auto">
        <div class="flex flex-col gap-4">
          <h1 v-text="$t('contact.hero.title')" class="text-gray-800 text-xl md:text-2xl xl:text-3xl font-medium"></h1>
          <p v-text="$t('contact.hero.description')" class="text-gray-600 md:text-lg xl:text-xl leading-9"></p>
        </div>
      </div>
      <div class="w-fit mt-10 mb-5 lg:m-5 lg:mt-0 md:p-5">
        <img class="max-h-[500px]" src="/images/contact-hero-section.webp" alt="Contact hero section" />
      </div>
    </div>
  </section>
</template>
