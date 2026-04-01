<script setup>
  const invoiceDetails = ref(false);
  const props = defineProps({
    order: {
      type: Object,
      required: true,
    },
  });
</script>

<template>
  <section class="order-3 bg-white rounded-xl p-5 md:p-6 shadow col-span-8 flex flex-col gap-5">
    <span v-text="$t('order.invoice.title')" class="text-xl md:text-2xl font-medium text-gray-800"></span>

    <!-- Price -->
    <div class="flex flex-wrap items-center gap-2 justify-between">
      <span v-text="$t('order.invoice.price')" class="text-gray-500"></span>
      <span v-text="`${order.subtotal} ${order.currency}`" class="text-gray-800"></span>
    </div>

    <!-- Tax -->
    <div class="flex flex-wrap items-center gap-2 justify-between">
      <span v-text="$t('order.invoice.tax', { rate: order.tax_rate })" class="text-gray-500"></span>
      <span v-text="`${order.tax} ${order.currency}`" class="text-gray-800"></span>
    </div>

    <!-- Total -->
    <div class="flex flex-wrap items-center gap-2 justify-between">
      <span v-text="$t('order.invoice.total')" class="text-gray-500"></span>
      <span v-text="`${order.total} ${order.currency}`" class="font-medium text-gray-800"></span>
    </div>

    <hr class="border border-dashed border-gray-200" />

    <!-- View details -->
    <button @click="invoiceDetails = !invoiceDetails" class="w-fit inline-flex items-center gap-1 text-brand-500 stroke-brand-500 hover:text-brand-700 hover:stroke-brand-700 font-medium">
      <svg class="w-5 h-5" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
        <path d="M10.833 1.66663H4.99967C4.55765 1.66663 4.13372 1.84222 3.82116 2.15478C3.5086 2.46734 3.33301 2.89127 3.33301 3.33329V16.6666C3.33301 17.1087 3.5086 17.5326 3.82116 17.8451C4.13372 18.1577 4.55765 18.3333 4.99967 18.3333H14.9997C15.4417 18.3333 15.8656 18.1577 16.1782 17.8451C16.4907 17.5326 16.6663 17.1087 16.6663 16.6666V7.49996L10.833 1.66663Z" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
        <path d="M10.833 1.66663V7.49996H16.6663" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
      </svg>
      <span v-text="invoiceDetails ? $t('order.invoice.hide_details') : $t('order.invoice.view_details')"></span>
    </button>

    <template v-if="invoiceDetails">
      <!-- Visit cost -->
      <div class="flex flex-wrap items-center gap-2 justify-between">
        <span v-text="$t('order.invoice.visit_cost')" class="text-gray-500"></span>
        <span v-text="`${order.visit_cost} ${order.currency}`" class="text-gray-800"></span>
      </div>

      <!-- Coupons -->
      <div class="flex flex-wrap items-center gap-2 justify-between">
        <span v-text="$t('order.invoice.coupons')" class="text-gray-500"></span>
        <span v-text="`-${order.coupons_total} ${order.currency}`" class="text-green-500"></span>
      </div>

      <!-- Wallet balance -->
      <div class="flex flex-wrap items-center gap-2 justify-between">
        <span v-text="$t('order.invoice.wallet_balance')" class="text-gray-500"></span>
        <span v-text="`-${order.wallet_balance} ${order.currency}`" class="text-green-500"></span>
      </div>
    </template>
  </section>
</template>
