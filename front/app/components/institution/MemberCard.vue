<script setup>
  const { t } = useI18n();

  const props = defineProps({
    member: {
      type: Object,
      required: true,
    },
  });

  const statusColor = computed(() => {
    switch (props.member.status) {
      case 'active': return 'bg-green-50 text-green-700 border-green-200';
      case 'pending': return 'bg-amber-50 text-amber-700 border-amber-200';
      default: return 'bg-gray-50 text-gray-700 border-gray-200';
    }
  });

  const statusDot = computed(() => {
    switch (props.member.status) {
      case 'active': return 'bg-green-500';
      case 'pending': return 'bg-amber-500';
      default: return 'bg-gray-400';
    }
  });

  const statusLabel = computed(() => {
    switch (props.member.status) {
      case 'active': return t('institution.status.active');
      case 'pending': return t('institution.status.pending');
      default: return t('institution.status.inactive');
    }
  });
</script>

<template>
  <div class="bg-white rounded-xl border border-gray-100 shadow-sm hover:shadow-md hover:border-gray-200 transition-all duration-300 overflow-hidden group">
    <!-- Header -->
    <div class="p-5 flex items-center gap-4">
      <div class="relative">
        <img :src="member.picture" :alt="member.name" class="w-14 h-14 rounded-full object-cover ring-2 ring-gray-100 group-hover:ring-violet-100 transition-all" />
        <span :class="statusDot" class="absolute -bottom-0.5 -right-0.5 w-4 h-4 rounded-full border-2 border-white"></span>
      </div>
      <div class="flex-1 min-w-0">
        <h3 class="font-semibold text-gray-800 truncate">{{ member.name }}</h3>
        <p class="text-sm text-gray-500 font-mono" dir="ltr">{{ member.phone }}</p>
      </div>
      <span :class="statusColor" class="text-xs font-medium px-2.5 py-1 rounded-full border">
        {{ statusLabel }}
      </span>
    </div>

    <!-- Stats -->
    <div class="grid grid-cols-3 gap-px bg-gray-100 border-t border-gray-100">
      <!-- Completed Orders -->
      <div class="bg-white p-3.5 text-center">
        <p class="text-xs text-gray-400 mb-1">{{ $t('institution.member.completed_orders') }}</p>
        <p class="text-lg font-bold text-gray-800">{{ member.completed_orders }}</p>
      </div>

      <!-- This Month Orders -->
      <div class="bg-white p-3.5 text-center">
        <p class="text-xs text-gray-400 mb-1">{{ $t('institution.member.this_month') }}</p>
        <p class="text-lg font-bold text-violet-600">{{ member.this_month_orders }}</p>
      </div>

      <!-- Total Earnings -->
      <div class="bg-white p-3.5 text-center">
        <p class="text-xs text-gray-400 mb-1">{{ $t('institution.member.earnings') }}</p>
        <p class="text-lg font-bold text-emerald-600">{{ member.total_earnings }}</p>
      </div>
    </div>

    <!-- Footer -->
    <div class="px-5 py-3 bg-gray-50/50 border-t border-gray-100 flex items-center justify-between">
      <div class="flex items-center gap-1.5 text-xs text-gray-400">
        <svg class="w-3.5 h-3.5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
          <rect x="3" y="4" width="18" height="18" rx="2" ry="2" /><line x1="16" y1="2" x2="16" y2="6" /><line x1="8" y1="2" x2="8" y2="6" /><line x1="3" y1="10" x2="21" y2="10" />
        </svg>
        {{ member.joined_at }}
      </div>
      <div v-if="member.this_month_earnings" class="text-xs font-medium text-emerald-600 bg-emerald-50 px-2 py-0.5 rounded">
        {{ $t('institution.member.this_month') }}: {{ member.this_month_earnings }}
      </div>
    </div>
  </div>
</template>
