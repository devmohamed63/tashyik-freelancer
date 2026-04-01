<script setup>
  const { t } = useI18n();
  const orders = ref([]);
  const loader = ref(false);
  const currentPage = ref(1);
  const lastPage = ref(1);
  const hasMoreOrders = computed(() => currentPage.value < lastPage.value);
  const route = useRoute();
  const router = useRouter();

  definePageMeta({ middleware: ['auth'] });

  useSeoMeta({ title: t('seo.orders.title') });

  const currentTap = computed(() => {
    if (!route.query.tap) return 'new';

    switch (route.query.tap) {
      case 'new':
        return 'new';

      case 'in_progress':
        return 'in_progress';

      case 'completed':
        return 'completed';
    }
  });

  async function loadOrders() {
    try {
      const response = await useApiFetch(`/user/orders?status=${currentTap.value}&page=${currentPage.value}`);

      orders.value = [...orders.value, ...response.data];

      currentPage.value = response.meta.current_page;
      lastPage.value = response.meta.last_page;
    } catch (error) {
      console.error('Failed to load orders:', error);
    }

    loader.value = false;
  }

  await loadOrders();

  async function loadMore() {
    loader.value = true;

    currentPage.value++;

    await loadOrders();
  }

  watch(
    () => route.query.tap,
    async () => {
      loader.value = true;

      orders.value = [];

      currentPage.value = 1;

      await loadOrders();
    },
  );

  function changeTap(newTap) {
    router.push({ query: { tap: newTap } });
  }
</script>

<template>
  <div class="container">
    <div class="container py-8 md:py-12 flex flex-col gap-6">
      <AppBreadcrumb :pages="[{ name: $t('orders.name') }]" />

      <!-- Taps section -->
      <section class="p-4 md:p-8 rounded-xl shadow bg-white">
        <div class="inline-flex gap-3 md:gap-4 overflow-x-auto w-full">
          <button @click="changeTap('new')" v-text="$t('orders.navigation.new')" class="text-nowrap px-4 md:px-7 py-2.5 rounded-lg font-light" :class="[currentTap == 'new' ? 'bg-brand-500 text-white' : 'bg-gray-200 text-gray-600 hover:bg-gray-300']"></button>
          <button @click="changeTap('in_progress')" v-text="$t('orders.navigation.in_progress')" class="text-nowrap px-4 md:px-7 py-2.5 rounded-lg font-light" :class="[currentTap == 'in_progress' ? 'bg-brand-500 text-white' : 'bg-gray-200 text-gray-600 hover:bg-gray-300']"></button>
          <button @click="changeTap('completed')" v-text="$t('orders.navigation.completed')" class="text-nowrap px-4 md:px-7 py-2.5 rounded-lg font-light" :class="[currentTap == 'completed' ? 'bg-brand-500 text-white' : 'bg-gray-200 text-gray-600 hover:bg-gray-300']"></button>
        </div>
      </section>

      <!-- Orders list -->
      <div v-if="orders.length" class="flex flex-col lg:grid grid-cols-2 gap-6">
        <OrderCard v-for="order in orders" :key="order.id" :order />
      </div>

      <!-- Loader -->
      <div v-else-if="loader" class="mt-10">
        <AppSpinner />
      </div>

      <!-- No orders yet -->
      <div v-else class="w-full h-80 flex flex-col gap-5 items-center justify-center">
        <!-- ic:round-home-repair-service -->
        <svg class="w-16 h-16 text-gray-300" xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24">
          <!-- Icon from Google Material Icons by Material Design Authors - https://github.com/material-icons/material-icons/blob/master/LICENSE -->
          <path fill="currentColor" d="M17 16c-.55 0-1-.45-1-1H8c0 .55-.45 1-1 1s-1-.45-1-1H2v3c0 1.1.9 2 2 2h16c1.1 0 2-.9 2-2v-3h-4c0 .55-.45 1-1 1m3-8h-3V6c0-1.1-.9-2-2-2H9c-1.1 0-2 .9-2 2v2H4c-1.1 0-2 .9-2 2v4h4v-1c0-.55.45-1 1-1s1 .45 1 1v1h8v-1c0-.55.45-1 1-1s1 .45 1 1v1h4v-4c0-1.1-.9-2-2-2m-5 0H9V6h6z" />
        </svg>
        <h3 v-text="$t('orders.empty.title')" class="text-gray-600 text-xl font-medium"></h3>
        <span v-text="$t('orders.empty.subtitle')" class="text-gray-500"></span>
        <NuxtLinkLocale :to="{ name: 'categories' }">
          <ButtonsOutline>
            <span v-text="$t('orders.empty.view_services')" class="ps-3"></span>
            <!-- majesticons:chevron-right -->
            <svg class="w-5 h-5 rtl:rotate-180" xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24">
              <!-- Icon from Majesticons by Gerrit Halfmann - https://github.com/halfmage/majesticons/blob/main/LICENSE -->
              <path fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m10 7l5 5l-5 5" />
            </svg>
          </ButtonsOutline>
        </NuxtLinkLocale>
      </div>

      <!-- Load more -->
      <div v-if="hasMoreOrders && !loader" class="text-center mt-8">
        <button @click="loadMore()" class="hover:text-brand-500 transition-colors mx-auto w-fit text-gray-700 font-medium text-lg flex gap-1 items-center justify-center">
          {{ $t('common.load_more') }}
          <!-- material-symbols:arrow-downward-alt -->
          <svg class="w-7 h-7 pt-1" xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24">
            <!-- Icon from Material Symbols by Google - https://github.com/google/material-design-icons/blob/master/LICENSE -->
            <path fill="currentColor" d="m12 18l-6-6l1.4-1.4l3.6 3.6V5h2v9.2l3.6-3.6L18 12z" />
          </svg>
        </button>
      </div>
    </div>
  </div>
</template>
