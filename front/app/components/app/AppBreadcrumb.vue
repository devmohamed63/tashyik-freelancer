<script setup>
  defineProps({
    pages: {
      type: Array,
      default: () => [],
      validator: (value) => {
        return value.every((item) => typeof item.name === 'string' && typeof item.path === 'object');
      },
    },
  });
</script>

<template>
  <nav aria-label="Breadcrumb">
    <ol class="flex flex-wrap items-center gap-2">
      <li>
        <NuxtLinkLocale :to="{ name: 'index' }" class="text-gray-500 flex items-center">
          <svg class="w-4 h-4 me-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path>
          </svg>
          {{ $t('navigation.home') }}
        </NuxtLinkLocale>
      </li>

      <li v-for="(page, index) in pages" :key="index" class="flex items-center">
        <svg class="w-4 h-4 text-gray-400 mx-2 rtl:rotate-180" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
        </svg>

        <NuxtLinkLocale v-if="index < pages.length - 1" :to="page.path" class="text-gray-500">
          {{ page.name }}
        </NuxtLinkLocale>

        <span v-else class="text-brand-500">
          {{ page.name }}
        </span>
      </li>
    </ol>
  </nav>
</template>
