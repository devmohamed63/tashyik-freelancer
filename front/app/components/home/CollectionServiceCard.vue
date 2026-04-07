<script setup>
  defineProps({
    service: {
      type: Object,
      required: true,
    },
  });
</script>

<template>
  <NuxtLinkLocale
    :to="{ name: 'services-service', params: { service: service.slug } }"
    class="group relative flex flex-col bg-white rounded-2xl shadow-sm hover:shadow-md transition-shadow duration-300 overflow-hidden static-color"
  >
    <!-- Image -->
    <div class="relative bg-gray-100 w-full aspect-video overflow-hidden">
      <img
        :src="service.image"
        :alt="service.name"
        loading="lazy"
        class="w-full h-full object-center object-cover transition-transform duration-300 group-hover:scale-105"
      />

      <!-- Discount Badge -->
      <div
        v-if="service.price.has_discount"
        class="absolute top-3 start-3 bg-red-500 text-white text-xs font-bold px-2.5 py-1 rounded-lg shadow-sm"
      >
        {{ $t('common.promotion', { percentage: service.price.discount_percintage }) }}
      </div>
    </div>

    <!-- Content -->
    <div class="flex flex-col gap-2 p-4">
      <!-- Name -->
      <h4 class="text-base font-medium text-gray-800 line-clamp-1">
        {{ service.name }}
      </h4>

      <!-- Rating -->
      <div class="inline-flex items-center gap-1 text-sm text-gray-500">
        <svg class="w-4 h-4 text-amber-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor">
          <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z" />
        </svg>
        <span>{{ service.rating }}</span>
      </div>

      <!-- Price -->
      <div v-if="service.price.original > 0" class="inline-flex items-center gap-2 mt-1">
        <span class="text-lg font-bold text-brand-500">
          {{ service.price.after_discount }} {{ service.price.currency }}
        </span>
        <del v-if="service.price.has_discount" class="text-sm text-gray-400">
          {{ service.price.original }} {{ service.price.currency }}
        </del>
      </div>
      <span v-else class="text-sm text-gray-500 mt-1">{{ $t('common.no_price') }}</span>
    </div>
  </NuxtLinkLocale>
</template>
