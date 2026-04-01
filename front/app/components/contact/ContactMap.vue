<script setup>
  const config = useRuntimeConfig();
  const key = ref();

  key.value = config.public.scripts.googleMaps.apiKey;
</script>

<template>
  <section class="bg-gray-50">
    <div class="container px-4 flex flex-col gap-8 pb-14 md:pb-20">
      <div class="flex flex-col gap-3 text-center">
        <h3 v-text="$t('contact.map.title')" class="text-xl md:text-3xl font-medium text-gray-800"></h3>
        <span v-text="$t('contact.map.subtitle')" class="md:text-lg text-gray-500"></span>
      </div>

      <iframe title="map" class="shadow rounded-lg h-[400px] sm:h-[500px] w-full" :src="`https://www.google.com/maps/embed/v1/place?key=${key}&q=شركة تطبيق تشييك، شارع عبدالله بن الزبير، العريجاء الغربية، الرياض 14913، المملكة العربية السعودية`" style="border: 0" allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>
    </div>
  </section>
</template>
