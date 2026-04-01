<script setup>
  const { t } = useI18n();
  const addresses = ref(null);

  definePageMeta({ middleware: ['auth'] });

  useSeoMeta({
    title: t('seo.addresses.title'),
  });

  try {
    const response = await useApiFetch('/addresses');

    addresses.value = response.data;
  } catch (error) {
    console.error('Failed to load addresses:', error);
  }

  function toggleDefaultAddress({ address, newVal }) {
    addresses.value = addresses.value.map((a) => (a.id == address.id ? { ...a, is_default: newVal } : { ...a, is_default: false }));
  }

  function addressDeleted({ address }) {
    addresses.value = addresses.value.filter((a) => a.id != address.id);
  }
</script>

<template>
  <div class="container px-2 py-8 md:py-12 space-y-5 md:space-y-8">
    <AppBreadcrumb :pages="[{ name: $t('addresses.title') }]" />

    <!-- Heading section -->
    <section class="flex flex-col lg:flex-row gap-5 lg:justify-between lg:items-center">
      <div class="flex flex-col gap-3">
        <h1 v-text="$t('addresses.heading.title')" class="text-2xl md:text-3xl text-gray-800 font-medium"></h1>
        <p v-text="$t('addresses.heading.subtitle')" class="md:text-xl text-gray-500 max-w-xl"></p>
      </div>
      <NuxtLinkLocale :to="{ name: 'addresses-create' }">
        <ButtonsFilled class="w-full sm:w-auto">
          <!-- ic:baseline-plus -->
          <svg class="w-5 h-5" xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24">
            <!-- Icon from Google Material Icons by Material Design Authors - https://github.com/material-icons/material-icons/blob/master/LICENSE -->
            <path fill="currentColor" d="M19 12.998h-6v6h-2v-6H5v-2h6v-6h2v6h6z" />
          </svg>
          <span v-text="$t('addresses.heading.action')"></span>
        </ButtonsFilled>
      </NuxtLinkLocale>
    </section>

    <!-- Addresses list -->
    <section class="flex flex-col md:grid grid-cols-2 lg:grid-cols-3 gap-6">
      <NuxtLinkLocale :to="{ name: 'addresses-create' }" class="w-full text-center p-8 hidden md:flex flex-col gap-6 items-center justify-center rounded-xl border border-dashed border-gray-400">
        <!-- ic:baseline-plus -->
        <svg class="w-16 h-16 bg-brand-50 text-brand-500 p-4 rounded-full" xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24">
          <!-- Icon from Google Material Icons by Material Design Authors - https://github.com/material-icons/material-icons/blob/master/LICENSE -->
          <path fill="currentColor" d="M19 12.998h-6v6h-2v-6H5v-2h6v-6h2v6h6z" />
        </svg>
        <div class="flex flex-col gap-3">
          <span v-text="$t('addresses.new.title')" class="text-2xl text-gray-800 font-medium"></span>
          <span v-text="$t('addresses.new.subtitle')" class="text-lg text-gray-500"></span>
        </div>
      </NuxtLinkLocale>
      <AddressCard v-for="address of addresses" :key="address.id" :address @default-changed="toggleDefaultAddress" @deleted="addressDeleted" />
    </section>
  </div>
</template>
