<script setup>
  const props = defineProps({
    order: {
      type: Object,
      required: true,
    },
  });
</script>

<template>
  <div class="bg-white shadow p-5 md:p-8 rounded-xl flex flex-col gap-3">
    <div class="flex flex-wrap justify-between gap-2">
      <!-- Name -->
      <span v-text="order.name" class="text-lg md:text-xl text-gray-800 font-medium"></span>

      <!-- Price -->
      <span v-text="`${order.total} ${order.currency}`" class="md:text-lg text-brand-500 font-medium"></span>
    </div>

    <!-- Date -->
    <div class="inline-flex items-center gap-2 text-gray-500 stroke-gray-500">
      <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
        <path d="M8 2.00012V6.00012" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" />
        <path d="M16 2.00012V6.00012" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" />
        <path d="M19 3.99988H5C3.89543 3.99988 3 4.89531 3 5.99988V19.9999C3 21.1044 3.89543 21.9999 5 21.9999H19C20.1046 21.9999 21 21.1044 21 19.9999V5.99988C21 4.89531 20.1046 3.99988 19 3.99988Z" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" />
        <path d="M3 9.99988H21" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" />
      </svg>
      <span v-text="order.created_at" class="w-full truncate"></span>
    </div>

    <!-- Order ID -->
    <div class="inline-flex items-center gap-2 text-gray-500 stroke-gray-500 my-2">
      <span v-text="`${$t('order.id')}: #${order.id}`"></span>
    </div>

    <hr class="border border-dashed border-gray-300 my-2" />

    <NuxtLinkLocale :to="{ name: 'orders-order', params: { order: order.id } }" class="static-color text-brand-500 hover:text-brand-600 stroke-brand-500 hover:stroke-brand-600 inline-flex items-center gap-2 font-medium">
      {{ $t('order.actions.view_details') }}
      <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
        <path d="M19 12H5" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" />
        <path d="M12 19L5 12L12 5" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" />
      </svg>
    </NuxtLinkLocale>
  </div>
</template>
