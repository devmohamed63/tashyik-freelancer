<script setup>
  const { t } = useI18n();
  const localePath = useLocalePath();

  definePageMeta({ middleware: ['auth'] });

  useSeoMeta({
    title: t('seo.addresses.create.title'),
  });
</script>

<template>
  <section class="container px-2 py-8 md:py-12 space-y-5 md:space-y-8">
    <AppBreadcrumb :pages="[{ name: $t('addresses.title'), path: localePath({ name: 'addresses' }) }, { name: $t('addresses.create.title') }]" />

    <!-- Heading section -->
    <section class="flex flex-col lg:flex-row gap-5 lg:justify-between lg:items-center">
      <div class="flex flex-col gap-3">
        <h1 v-text="$t('addresses.create.heading.title')" class="text-2xl md:text-3xl text-gray-800 font-medium"></h1>
        <p v-text="$t('addresses.create.heading.subtitle')" class="md:text-xl text-gray-500 max-w-xl"></p>
      </div>
    </section>

    <AddressForm />
  </section>
</template>
