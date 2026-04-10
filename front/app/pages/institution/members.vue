<script setup>
  const { t } = useI18n();
  const auth = useAuthStore();

  definePageMeta({
    middleware: ['auth'],
  });

  useSeoMeta({
    title: t('institution.seo_title'),
  });

  const loading = ref(true);
  const members = ref([]);
  const summary = ref(null);

  // Filters
  const searchQuery = ref('');
  const statusFilter = ref('all');
  const sortBy = ref('name');

  async function fetchMembers() {
    loading.value = true;
    try {
      const response = await useApiFetch('/institution/members');
      members.value = response.data;
      summary.value = response.summary;
    } catch (error) {
      if (error.statusCode === 403) {
        return navigateTo(useLocalePath()({ name: 'profile' }));
      }
      console.error('Failed to fetch members:', error);
    }
    loading.value = false;
  }

  // Filtered + sorted members
  const filteredMembers = computed(() => {
    let result = [...members.value];

    // Search filter
    if (searchQuery.value.trim()) {
      const query = searchQuery.value.trim().toLowerCase();
      result = result.filter(m =>
        m.name.toLowerCase().includes(query) ||
        m.phone.includes(query)
      );
    }

    // Status filter
    if (statusFilter.value !== 'all') {
      result = result.filter(m => m.status === statusFilter.value);
    }

    // Sort
    switch (sortBy.value) {
      case 'earnings_desc':
        result.sort((a, b) => parseFloat(b.total_earnings) - parseFloat(a.total_earnings));
        break;
      case 'orders_desc':
        result.sort((a, b) => b.completed_orders - a.completed_orders);
        break;
      case 'this_month':
        result.sort((a, b) => b.this_month_orders - a.this_month_orders);
        break;
      case 'name':
      default:
        result.sort((a, b) => a.name.localeCompare(b.name));
        break;
    }

    return result;
  });

  const statusOptions = [
    { value: 'all', label: 'institution.filter.all' },
    { value: 'active', label: 'institution.status.active' },
    { value: 'pending', label: 'institution.status.pending' },
    { value: 'inactive', label: 'institution.status.inactive' },
  ];

  const sortOptions = [
    { value: 'name', label: 'institution.sort.name' },
    { value: 'earnings_desc', label: 'institution.sort.earnings' },
    { value: 'orders_desc', label: 'institution.sort.orders' },
    { value: 'this_month', label: 'institution.sort.this_month' },
  ];

  await fetchMembers();
</script>

<template>
  <div class="min-h-screen bg-gray-50 py-8">
    <div class="container mx-auto px-4 space-y-6">
      <!-- Breadcrumb -->
      <AppBreadcrumb :pages="[
        { name: $t('navigation.profile.title'), to: { name: 'profile' } },
        { name: $t('institution.title') },
      ]" />

      <!-- Page Header -->
      <div class="flex flex-col gap-1">
        <h1 class="text-xl md:text-2xl font-medium text-gray-800">
          {{ $t('institution.title') }}
        </h1>
        <p class="text-gray-500">
          {{ $t('institution.subtitle') }}
        </p>
      </div>

      <!-- Summary Cards -->
      <div v-if="summary" class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        <!-- Active Members -->
        <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-5 hover:shadow-md transition-shadow">
          <div class="flex items-center gap-3 mb-3">
            <div class="w-10 h-10 bg-violet-50 rounded-lg flex items-center justify-center">
              <svg class="w-5 h-5 text-violet-500" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2" /><circle cx="9" cy="7" r="4" /><path d="M22 21v-2a4 4 0 0 0-3-3.87" /><path d="M16 3.13a4 4 0 0 1 0 7.75" />
              </svg>
            </div>
            <p class="text-sm text-gray-500">{{ $t('institution.summary.active_members') }}</p>
          </div>
          <p class="text-2xl font-bold text-gray-800">{{ summary.active_members }} <span class="text-sm font-normal text-gray-400">/ {{ summary.total_members }}</span></p>
        </div>

        <!-- This Month Orders -->
        <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-5 hover:shadow-md transition-shadow">
          <div class="flex items-center gap-3 mb-3">
            <div class="w-10 h-10 bg-blue-50 rounded-lg flex items-center justify-center">
              <svg class="w-5 h-5 text-blue-500" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z" /><polyline points="14 2 14 8 20 8" /><line x1="16" y1="13" x2="8" y2="13" /><line x1="16" y1="17" x2="8" y2="17" /><polyline points="10 9 9 9 8 9" />
              </svg>
            </div>
            <p class="text-sm text-gray-500">{{ $t('institution.summary.this_month_orders') }}</p>
          </div>
          <p class="text-2xl font-bold text-gray-800">{{ summary.this_month_orders }}</p>
        </div>

        <!-- This Month Earnings -->
        <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-5 hover:shadow-md transition-shadow">
          <div class="flex items-center gap-3 mb-3">
            <div class="w-10 h-10 bg-emerald-50 rounded-lg flex items-center justify-center">
              <svg class="w-5 h-5 text-emerald-500" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <line x1="12" y1="1" x2="12" y2="23" /><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6" />
              </svg>
            </div>
            <p class="text-sm text-gray-500">{{ $t('institution.summary.this_month_earnings') }}</p>
          </div>
          <p class="text-2xl font-bold text-emerald-600">{{ summary.this_month_earnings }} <span class="text-sm font-normal text-gray-400">{{ summary.currency }}</span></p>
        </div>

        <!-- Balance -->
        <div class="bg-gradient-to-br from-violet-500 to-violet-600 rounded-xl shadow-sm p-5 text-white hover:shadow-md transition-shadow">
          <div class="flex items-center gap-3 mb-3">
            <div class="w-10 h-10 bg-white/20 rounded-lg flex items-center justify-center">
              <svg class="w-5 h-5 text-white" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <rect x="2" y="5" width="20" height="14" rx="2" /><line x1="2" y1="10" x2="22" y2="10" />
              </svg>
            </div>
            <p class="text-sm text-white/80">{{ $t('institution.summary.balance') }}</p>
          </div>
          <p class="text-2xl font-bold">{{ summary.balance }} <span class="text-sm font-normal text-white/70">{{ summary.currency }}</span></p>
        </div>
      </div>

      <!-- Filters Bar -->
      <div v-if="members.length" class="bg-white rounded-xl border border-gray-100 shadow-sm p-4">
        <div class="flex flex-col md:flex-row gap-3">
          <!-- Search -->
          <div class="flex-1 relative">
            <svg class="absolute top-1/2 -translate-y-1/2 start-3 w-4 h-4 text-gray-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <circle cx="11" cy="11" r="8" /><line x1="21" y1="21" x2="16.65" y2="16.65" />
            </svg>
            <input
              v-model="searchQuery"
              type="text"
              :placeholder="$t('institution.filter.search')"
              class="w-full ps-10 pe-4 py-2.5 border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-violet-500/20 focus:border-violet-400 outline-none transition-all"
            />
          </div>

          <!-- Status Filter -->
          <select
            v-model="statusFilter"
            class="px-4 py-2.5 border border-gray-200 rounded-lg text-sm text-gray-700 bg-white focus:ring-2 focus:ring-violet-500/20 focus:border-violet-400 outline-none transition-all min-w-[140px]"
          >
            <option v-for="option in statusOptions" :key="option.value" :value="option.value">
              {{ $t(option.label) }}
            </option>
          </select>

          <!-- Sort -->
          <select
            v-model="sortBy"
            class="px-4 py-2.5 border border-gray-200 rounded-lg text-sm text-gray-700 bg-white focus:ring-2 focus:ring-violet-500/20 focus:border-violet-400 outline-none transition-all min-w-[160px]"
          >
            <option v-for="option in sortOptions" :key="option.value" :value="option.value">
              {{ $t(option.label) }}
            </option>
          </select>
        </div>

        <!-- Results count -->
        <p v-if="searchQuery || statusFilter !== 'all'" class="mt-3 text-xs text-gray-400">
          {{ $t('institution.filter.results', { count: filteredMembers.length }) }}
        </p>
      </div>

      <!-- Loading State -->
      <div v-if="loading" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
        <div v-for="i in 6" :key="i" class="bg-white rounded-xl border border-gray-100 shadow-sm overflow-hidden animate-pulse">
          <div class="p-5 flex items-center gap-4">
            <div class="w-14 h-14 rounded-full bg-gray-200"></div>
            <div class="flex-1 space-y-2">
              <div class="h-4 bg-gray-200 rounded w-3/4"></div>
              <div class="h-3 bg-gray-100 rounded w-1/2"></div>
            </div>
          </div>
          <div class="grid grid-cols-3 gap-px bg-gray-100 border-t border-gray-100">
            <div class="bg-white p-4 space-y-2" v-for="j in 3" :key="j">
              <div class="h-3 bg-gray-100 rounded w-2/3"></div>
              <div class="h-5 bg-gray-200 rounded w-1/2"></div>
            </div>
          </div>
        </div>
      </div>

      <!-- Members Grid -->
      <div v-else-if="filteredMembers.length" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
        <InstitutionMemberCard v-for="member in filteredMembers" :key="member.id" :member="member" />
      </div>

      <!-- No results (search/filter) -->
      <div v-else-if="members.length && !filteredMembers.length" class="flex flex-col items-center justify-center py-16 gap-3">
        <svg class="w-12 h-12 text-gray-300" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
          <circle cx="11" cy="11" r="8" /><line x1="21" y1="21" x2="16.65" y2="16.65" /><line x1="8" y1="11" x2="14" y2="11" />
        </svg>
        <h3 class="text-base font-medium text-gray-500">{{ $t('institution.filter.no_results') }}</h3>
        <button @click="searchQuery = ''; statusFilter = 'all'" class="text-sm text-violet-600 hover:text-violet-700 font-medium">
          {{ $t('institution.filter.clear') }}
        </button>
      </div>

      <!-- Empty State (no members at all) -->
      <div v-else class="flex flex-col items-center justify-center py-20 gap-4">
        <div class="w-20 h-20 bg-gray-100 rounded-full flex items-center justify-center">
          <svg class="w-10 h-10 text-gray-300" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
            <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2" /><circle cx="9" cy="7" r="4" /><path d="M22 21v-2a4 4 0 0 0-3-3.87" /><path d="M16 3.13a4 4 0 0 1 0 7.75" />
          </svg>
        </div>
        <h3 class="text-lg font-medium text-gray-600">{{ $t('institution.empty.title') }}</h3>
        <p class="text-gray-400 text-center max-w-md">{{ $t('institution.empty.subtitle') }}</p>
      </div>
    </div>
  </div>
</template>
