<script setup>
  const { t } = useI18n();
  const salesIq = useSalesIq();
  const route = useRoute();
  const data = ref(null);
  const selectedCategory = ref(null);
  const services = ref([]);
  const currentPage = ref(1);
  const lastPage = ref(1);
  const hasMoreServices = computed(() => currentPage.value < lastPage.value);
  const loader = ref(false);
  const q = ref(null);

  try {
    const response = await useApiFetch(`/categories/${route.params.category}`);

    data.value = response.data;

    selectedCategory.value = data.value.subcategories[0]?.id;
  } catch (error) {
    if (error.statusCode === 404 || error.response?.status === 404) {
      throw createError({
        statusCode: 404,
        statusMessage: t('common.page_not_found'),
        fatal: true,
      });
    }

    throw createError({
        statusCode: 500,
        statusMessage: error.message || 'Failed to fetch category',
        fatal: true,
    });
  }

  useSeoMeta({
    title: t('alt.category', { category: data.value?.name }),
    ogTitle: t('alt.category', { category: data.value?.name }),
    description: data.value?.description,
    ogDescription: data.value?.description,
  });

  async function loadServices() {
    try {
      const response = await useApiFetch(`/services?page=${currentPage.value}`, {
        method: 'POST',
        body: {
          category: selectedCategory.value,
          q: q.value,
        },
      });

      services.value = [...services.value, ...response.data];

      currentPage.value = response.meta.current_page;
      lastPage.value = response.meta.last_page;
    } catch (error) {
      console.error('Failed to load services:', error);
    }

    loader.value = false;
  }

  async function handleSearch() {
    selectedCategory.value = null;

    currentPage.value = 1;

    loader.value = true;

    services.value = [];

    await loadServices();
  }

  async function loadMore() {
    loader.value = true;

    currentPage.value++;

    await loadServices();

    loader.value = false;
  }

  watch(
    () => selectedCategory.value,
    async () => {
      currentPage.value = 1;

      loader.value = true;

      services.value = [];

      await loadServices();
    },
    {
      immediate: true,
    },
  );
</script>

<template>
  <section v-if="data" class="container px-4 py-8 md:py-12 space-y-5 md:space-y-10">
    <AppBreadcrumb :pages="[{ name: $t('alt.category', { category: data?.name }) }]" />

    <!-- Search -->
    <div class="rounded-xl bg-white shadow p-3 md:p-6 flex flex-col gap-3 md:gap-5">
      <form @submit.prevent="handleSearch">
        <label for="services-search" class="sr-only">Search</label>
        <div class="relative">
          <div class="absolute inset-y-0 start-0 flex items-center ps-3 pointer-events-none">
            <svg class="w-4 h-4 text-gray-400" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 20 20">
              <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m19 19-4-4m0-7A7 7 0 1 1 1 8a7 7 0 0 1 14 0Z" />
            </svg>
          </div>
          <input v-model="q" type="search" id="services-search" class="block w-full p-3 ps-10 md:p-4 md:ps-10 text-sm text-gray-800 border border-gray-200 rounded-lg bg-transparent focus:ring-brand-100 focus:border-brand-100 placeholder:text-gray-400" :placeholder="$t('common.search_for_services')" required minlength="3" autocomplete="off" />
          <button v-text="$t('common.search')" type="submit" class="text-white absolute end-[5px] md:end-2.5 bottom-[5px] md:bottom-2.5 bg-brand-500 hover:bg-brand-600 focus:ring-4 focus:outline-none focus:ring-brand-100 rounded-lg text-sm px-5 py-2"></button>
        </div>
      </form>
      <div class="inline-flex gap-3 overflow-x-auto">
        <button v-if="q" @click="selectedCategory = null" v-text="$t('common.all')" class="px-6 py-3 rounded-lg text-sm text-nowrap" :class="[selectedCategory == null ? 'bg-brand-500 text-white' : 'bg-gray-100 text-gray-500 hover:bg-gray-200']"></button>
        <button v-for="subcategory in data?.subcategories" :key="subcategory.id" v-text="subcategory.name" @click="selectedCategory = subcategory.id" class="px-6 py-3 rounded-lg text-sm text-nowrap" :class="[subcategory.id == selectedCategory ? 'bg-brand-500 text-white' : 'bg-gray-100 text-gray-500 hover:bg-gray-200']"></button>
      </div>
    </div>

    <!-- Services -->
    <div class="flex flex-col lg:grid grid-cols-2 xl:grid-cols-3 gap-8">
      <ServiceCard v-for="service of services" :key="service.id" :service />
    </div>

    <!-- No services -->
    <div v-if="!loader && !services?.length" class="w-full h-80 flex flex-col gap-5 items-center justify-center">
      <!-- ic:round-home-repair-service -->
      <svg class="w-16 h-16 text-gray-300" xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24">
        <!-- Icon from Google Material Icons by Material Design Authors - https://github.com/material-icons/material-icons/blob/master/LICENSE -->
        <path fill="currentColor" d="M17 16c-.55 0-1-.45-1-1H8c0 .55-.45 1-1 1s-1-.45-1-1H2v3c0 1.1.9 2 2 2h16c1.1 0 2-.9 2-2v-3h-4c0 .55-.45 1-1 1m3-8h-3V6c0-1.1-.9-2-2-2H9c-1.1 0-2 .9-2 2v2H4c-1.1 0-2 .9-2 2v4h4v-1c0-.55.45-1 1-1s1 .45 1 1v1h8v-1c0-.55.45-1 1-1s1 .45 1 1v1h4v-4c0-1.1-.9-2-2-2m-5 0H9V6h6z" />
      </svg>
      <h3 v-text="$t('services.empty.title')" class="text-gray-600 text-xl font-medium"></h3>
      <span v-text="$t('services.empty.subtitle')" class="text-gray-500"></span>
    </div>

    <!-- Loader -->
    <div v-if="loader" class="mt-10">
      <AppSpinner />
    </div>

    <!-- Load more -->
    <div v-if="hasMoreServices && !loader" class="text-center mt-2 sm:mt-8">
      <button @click="loadMore()" class="hover:text-brand-500 transition-colors mt-10 mx-auto w-fit text-gray-700 font-medium text-lg flex gap-1 items-center justify-center">
        {{ $t('common.load_more') }}
        <!-- material-symbols:arrow-downward-alt -->
        <svg class="w-7 h-7 pt-1" xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24">
          <!-- Icon from Material Symbols by Google - https://github.com/google/material-design-icons/blob/master/LICENSE -->
          <path fill="currentColor" d="m12 18l-6-6l1.4-1.4l3.6 3.6V5h2v9.2l3.6-3.6L18 12z" />
        </svg>
      </button>
    </div>
  </section>

  <!-- Custom service section -->
  <section class="container pb-8 md:pb-12 px-5">
    <div class="bg-brand-50 rounded-lg py-16 px-6 text-center flex flex-col gap-3 items-center">
      <h3 v-text="$t('services.custom_service.title')" class="text-2xl md:text-3xl text-gray-800 font-medium"></h3>
      <p v-text="$t('services.custom_service.subtitle')" class="md:text-lg text-gray-500 max-w-lg mb-6"></p>
      <ButtonsFilled @click="salesIq.openChat" class="w-fit">
        {{ $t('services.custom_service.action') }}
      </ButtonsFilled>
    </div>
  </section>
</template>
