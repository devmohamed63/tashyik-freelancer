<script setup>
  const props = defineProps({
    service: {
      type: Object,
      required: true,
    },
  });
</script>

<template>
  <div class="relative w-full bg-white shadow flex flex-col rounded-2xl static-color">
    <!-- Image -->
    <div class="bg-gray-100 rounded-2xl rounded-b-none w-full aspect-video shrink-0 object-center object-cover overflow-hidden">
      <img :src="service.image" :alt="$t('alt.service', { service: service.name })" loading="lazy" class="w-full h-full object-center object-cover transition-all duration-300 group-hover:scale-105" />
    </div>

    <!-- Warranty -->
    <WarrantyBadge v-if="service.warranty_duration" :duration="service.warranty_duration" class="absolute top-4 start-4" />

    <div class="flex flex-col w-full h-full p-6">
      <!-- Name -->
      <span v-text="service.name" class="text-xl text-gray-800 font-medium"></span>

      <!-- Highlights -->
      <ul class="flex flex-col gap-3 text-gray-500 list-disc marker:text-xs ps-6 my-6">
        <li v-for="highlight of service.highlights" :key="highlight.id">
          <p v-text="highlight.title" class="truncate"></p>
        </li>
      </ul>

      <div class="flex flex-col gap-5 mt-auto">
        <hr class="border border-gray-200/70" />

        <!-- Promotion -->
        <AppBadge type="success" v-if="service.price.has_discount" :title="$t('common.promotion', { percentage: service.price.discount_percintage })" />

        <!-- Price -->
        <div class="flex flex-wrap items-center justify-between gap-2">
          <div v-if="service.price.original > 0" class="inline-flex items-end gap-3">
            <span v-text="`${service.price.after_discount} ${service.price.currency}`" class="text-xl font-medium text-gray-800"></span>
            <del v-if="service.price.has_discount" v-text="`${service.price.original} ${service.price.currency}`" class="font-medium text-gray-400"></del>
          </div>
          <span v-else v-text="$t('common.no_price')" class="text-gray-800"></span>
          <img class="h-4" src="/images/tabby-tamara.webp" alt="Tabby & Tamara" loading="lazy" />
        </div>

        <!-- Actions -->
        <div class="inline-flex items-center gap-2 overflow-hidden">
          <NuxtLinkLocale :to="{ name: 'services-service-order', params: { service: service.slug } }" class="w-full">
            <ButtonsFilled class="w-full text-nowrap">
              {{ $t('service.actions.order_now') }}
            </ButtonsFilled>
          </NuxtLinkLocale>
          <NuxtLinkLocale :to="{ name: 'services-service', params: { service: service.slug } }" class="w-full">
            <ButtonsOutline class="w-full text-nowrap">
              {{ $t('service.actions.view_details') }}
            </ButtonsOutline>
          </NuxtLinkLocale>
        </div>
      </div>
    </div>
  </div>
</template>
