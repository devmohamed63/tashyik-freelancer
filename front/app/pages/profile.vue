<script setup>
  const { t } = useI18n();

  definePageMeta({
    middleware: ['auth'],
  });

  useSeoMeta({
    title: t('seo.profile.title'),
  });
</script>

<template>
  <div class="min-h-screen bg-gray-50 py-8">
    <div class="container mx-auto px-4 space-y-6">
      <!-- Breadcrumb -->
      <AppBreadcrumb :pages="[{ name: $t('profile.name') }]" />

      <!-- Page Header -->
      <div class="mb-8 space-y-6">
        <div class="flex flex-col gap-1">
          <h1 class="text-xl md:text-2xl font-medium text-gray-800">
            {{ $t('profile.header.title') }}
          </h1>
          <p class="text-gray-600">
            {{ $t('profile.header.subtitle') }}
          </p>
        </div>

        <ProfileInformation />
        <ProfilePassword />
        <ProfileDelete />
      </div>
    </div>
  </div>
</template>
