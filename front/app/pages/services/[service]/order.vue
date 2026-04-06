<script setup>
  import { watchDebounced } from '@vueuse/core';

  const { t } = useI18n();
  const summary = ref(null);
  const route = useRoute();
  const quantity = ref(1);
  const localePath = useLocalePath();
  const address = ref('');
  const loader = ref(false);
  const coupon = ref(null);
  const coupons = ref([]);
  const validCoupons = computed(() => summary.value.coupons.filter((c) => c.is_valid == true));
  const description = ref('');
  const addresses = ref(null);

  definePageMeta({ middleware: ['auth'] });

  async function loadSummary(confirmOrder = false) {
    const response = await useApiFetch('/user/orders', {
      method: 'POST',
      body: {
        address: address.value,
        description: description.value,
        service: route.params.service,
        quantity: quantity.value,
        confirm_order: confirmOrder,
        coupons: coupons.value,
      },
    });

    summary.value = response;

    loader.value = false;
  }

  try {
    await loadSummary();
  } catch (error) {
    if (error.statusCode == 404 || error.response?.status == 404) {
      throw createError({
        statusCode: 404,
        statusMessage: $t('common.page_not_found'),
        fatal: true,
      });
    }

    throw createError({
        statusCode: 500,
        statusMessage: error.message || 'Failed to fetch summary',
        fatal: true,
    });
  }

  useSeoMeta({
    title: t('orders.make.title'),
  });

  async function increaseQuantity() {
    if (loader.value) return;

    loader.value = true;

    quantity.value++;

    await loadSummary();
  }

  async function decreaseQuantity() {
    if (quantity.value == 1 || loader.value) return;

    loader.value = true;

    quantity.value--;

    await loadSummary();
  }

  async function handleCoupon() {
    if (loader.value) return;

    loader.value = true;

    coupons.value = [...coupons.value, coupon.value];

    coupon.value = null;

    await loadSummary();
  }

  async function confirmOrder() {
    if (loader.value) return;

    loader.value = true;

    await loadSummary(true);

    await navigateTo(localePath({ name: 'orders' }));
  }

  watch(
    () => address.value,
    async () => {
      if (loader.value) return;

      loader.value = true;

      await loadSummary();
    },
  );

  watchDebounced(
    () => description.value,
    async () => {
      await loadSummary();
    },
    { debounce: 800, maxWait: 2000 },
  );

  onMounted(async () => {
    try {
      const response = await useApiFetch('/addresses');

      addresses.value = response.data;
    } catch (error) {
      console.error('Failed to load addresses:', error);
    }
  });
</script>

<template>
  <div class="container px-2 py-8 md:py-12 space-y-8">
    <AppBreadcrumb :pages="[{ path: localePath({ name: 'services-service', params: { service: summary.service.slug } }), name: summary.service.name }, { name: $t('orders.make.title') }]" />

    <div class="flex flex-col lg:grid grid-cols-12 gap-6 text-gray-500">
      <div class="col-span-7 flex flex-col gap-6">
        <!-- Address section -->
        <section class="bg-white p-5 lg:p-8 rounded-xl shadow flex flex-col gap-4">
          <h3 v-text="$t('orders.make.address')" class="md:text-xl font-medium"></h3>
          <InputsSelect v-model="address" id="address" required :disabled="loader">
            <option value="" v-text="$t('common.select_address')"></option>
            <option v-for="a in addresses" :key="a.id" :value="a.id" v-text="`${a.name} - ${a.address}`"></option>
          </InputsSelect>
          <NuxtLinkLocale :to="{ name: 'addresses-create' }" class="inline-flex items-center gap-1 text-brand-500 hover:text-brand-700">
            <!-- ic:baseline-plus -->
            <svg class="w-5 h-5" xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24">
              <!-- Icon from Google Material Icons by Material Design Authors - https://github.com/material-icons/material-icons/blob/master/LICENSE -->
              <path fill="currentColor" d="M19 12.998h-6v6h-2v-6H5v-2h6v-6h2v6h6z" />
            </svg>
            {{ $t('seo.addresses.create.title') }}
          </NuxtLinkLocale>
        </section>

        <!-- Details section -->
        <section class="bg-white p-5 lg:p-8 rounded-xl shadow flex flex-col gap-4">
          <h3 v-text="$t('orders.make.details.title')" class="md:text-xl font-medium"></h3>
          <hr class="border-dashed border-gray-200" />
          <div class="inline-flex justify-between gap-3">
            <span v-text="summary.service.name" class="truncate"></span>
            <div class="inline-flex items-center gap-3">
              <!-- ic:baseline-plus -->
              <svg @click="increaseQuantity" class="cursor-pointer w-7 h-7 p-1 text-brand-500 bg-brand-100 hover:bg-brand-200 rounded-md" xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24">
                <!-- Icon from Google Material Icons by Material Design Authors - https://github.com/material-icons/material-icons/blob/master/LICENSE -->
                <path fill="currentColor" d="M19 12.998h-6v6h-2v-6H5v-2h6v-6h2v6h6z" />
              </svg>
              <span v-text="quantity" class="font-medium mt-1"></span>
              <!-- material-symbols:remove -->
              <svg @click="decreaseQuantity" class="cursor-pointer w-7 h-7 p-1 text-brand-500 border border-brand-500 hover:bg-brand-50 rounded-md" xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24">
                <!-- Icon from Material Symbols by Google - https://github.com/google/material-design-icons/blob/master/LICENSE -->
                <path fill="currentColor" d="M5 13v-2h14v2z" />
              </svg>
            </div>
          </div>
          <hr class="border-dashed border-gray-200" />
          <textarea v-model="description" :placeholder="$t('orders.make.details.description')" class="rounded-lg p-5 border border-gray-200 bg-gray-50 focus:ring-4 ring-brand-50" rows="4"></textarea>
        </section>

        <!-- Coupons section -->
        <section class="bg-white p-5 lg:p-8 rounded-xl shadow flex flex-col gap-4">
          <h3 class="md:text-xl font-medium inline-flex gap-2 items-center">
            <svg class="w-6 h-6 stroke-brand-400" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
              <g clip-path="url(#clip0_11625_4936)">
                <path d="M10.4877 2.15496C10.1752 1.84238 9.75134 1.66672 9.30935 1.66663H3.33268C2.89065 1.66663 2.46673 1.84222 2.15417 2.15478C1.84161 2.46734 1.66602 2.89127 1.66602 3.33329V9.30996C1.66611 9.75195 1.84177 10.1758 2.15435 10.4883L9.40768 17.7416C9.78644 18.118 10.2987 18.3292 10.8327 18.3292C11.3666 18.3292 11.8789 18.118 12.2577 17.7416L17.741 12.2583C18.1174 11.8795 18.3286 11.3673 18.3286 10.8333C18.3286 10.2993 18.1174 9.78705 17.741 9.40829L10.4877 2.15496Z" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                <path d="M6.25065 6.66671C6.48077 6.66671 6.66732 6.48016 6.66732 6.25004C6.66732 6.01992 6.48077 5.83337 6.25065 5.83337C6.02053 5.83337 5.83398 6.01992 5.83398 6.25004C5.83398 6.48016 6.02053 6.66671 6.25065 6.66671Z" fill="#BB98D6" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
              </g>
              <defs>
                <clipPath id="clip0_11625_4936">
                  <rect width="20" height="20" fill="white" />
                </clipPath>
              </defs>
            </svg>
            {{ $t('orders.make.coupons.title') }}
          </h3>
          <form @submit.prevent="handleCoupon">
            <label for="coupon-code" class="sr-only">Coupon code</label>
            <div class="relative">
              <input v-model="coupon" type="text" id="coupon-code" class="block w-full p-3 md:p-4 text-gray-800 border border-gray-200 rounded-lg bg-transparent focus:ring-brand-100 focus:border-brand-100 placeholder:text-gray-400 max-md:text-sm" :placeholder="$t('orders.make.coupons.placeholder')" required autocomplete="off" />
              <button v-text="$t('orders.make.coupons.apply')" type="submit" class="text-white absolute top-0 end-0 h-full bg-brand-500 hover:bg-brand-600 focus:ring-4 focus:outline-none focus:ring-brand-100 rounded-e-lg max-md:text-sm px-7 py-1"></button>
            </div>
          </form>
        </section>
      </div>
      <div class="col-span-5">
        <!-- Summary section -->
        <section class="bg-white p-5 lg:p-8 rounded-xl shadow flex flex-col gap-4 relative">
          <div v-if="loader" class="absolute start-0 top-0 w-full h-full flex items-center justify-center bg-white/40 rounded-xl">
            <AppSpinner class="w-8 h-8" />
          </div>

          <h3 v-text="$t('orders.make.summary.title')" class="md:text-xl font-medium"></h3>

          <hr class="border-dashed border-gray-200" />

          <!-- Visit cost -->
          <div class="inline-flex justify-between gap-3">
            <span v-text="$t('order.summary.visit_cost')"></span>
            <span v-text="`${summary.visit_cost} ${summary.currency}`" class="font-medium"></span>
          </div>

          <!-- Subtotal -->
          <div class="inline-flex justify-between gap-3">
            <span v-text="$t('order.summary.subtotal')"></span>
            <span v-text="`${summary.subtotal} ${summary.currency}`" class="font-medium"></span>
          </div>

          <!-- Tax -->
          <div class="inline-flex justify-between gap-3">
            <span v-text="$t('order.summary.tax', { rate: summary.tax_rate })"></span>
            <span v-text="`${summary.tax} ${summary.currency}`" class="font-medium"></span>
          </div>

          <!-- Coupons -->
          <div v-for="(coupon, index) of validCoupons" :key="index" class="inline-flex justify-between gap-3">
            <span v-text="coupon.code"></span>
            <span v-text="`-${coupon.amount}`" class="text-green-500 font-medium"></span>
          </div>

          <!-- Wallet balance -->
          <div class="inline-flex justify-between gap-3">
            <span v-text="$t('order.summary.wallet_balance')"></span>
            <span v-text="`-${summary.wallet_balance} ${summary.currency}`" class="text-green-500 font-medium"></span>
          </div>

          <hr class="border-dashed border-gray-200" />

          <!-- Total -->
          <div class="inline-flex justify-between gap-3">
            <span v-text="$t('order.summary.total')"></span>
            <span v-text="`${summary.total} ${summary.currency}`" class="text-brand-500 font-medium"></span>
          </div>

          <ButtonsFilled v-if="!address" disabled>
            {{ $t('order.summary.pay') }}
          </ButtonsFilled>
          <ButtonsFilled v-else>
            <a v-if="summary.payment_link" :href="summary.payment_link" v-text="$t('order.summary.pay')" class="w-full h-full static-color"></a>
            <span v-else v-text="$t('order.summary.confirm')" class="w-full h-full" @click="confirmOrder"></span>
          </ButtonsFilled>
        </section>
      </div>
    </div>
  </div>
</template>
