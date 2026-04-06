<script setup>
  const { t } = useI18n();
  const localePath = useLocalePath();
  const route = useRoute();
  const order = ref(null);
  const reviewModal = ref(false);

  definePageMeta({ middleware: ['auth'] });

  useSeoMeta({ title: t('seo.orders.view.title') });

  try {
    const response = await useApiFetch(`/user/orders/${route.params.order}`);

    order.value = response;
  } catch (error) {
    if ([403, 404].includes(error.statusCode)) {
      throw createError({
        statusCode: 404,
        statusMessage: $t('common.page_not_found'),
        fatal: true,
      });
    }

    console.error('Failed to get order:', error);
  }

  function openReviewModal() {
    if (!order.value.can_review) return;

    reviewModal.value = true;
  }
</script>

<template>
  <div class="container">
    <div class="container py-8 md:py-12 flex flex-col gap-6">
      <AppBreadcrumb :pages="[{ path: localePath({ name: 'orders' }), name: $t('orders.name') }, { name: $t('order.title') }]" />

      <div class="flex flex-col xl:grid grid-cols-12 gap-6">
        <!-- Status section -->
        <OrderStatus :order />

        <!-- Service provider info -->
        <OrderServiceProvider :order />

        <!-- Order summary -->
        <OrderSummary :order />

        <!-- Invoice summary -->
        <OrderInvoiceSummary :order />

        <!-- Order extra -->
        <OrderExtras :order />

        <!-- Cancel section -->
        <OrderCancel :order />
      </div>

      <div v-if="order.status == 'completed'" class="flex flex-col md:flex-row gap-4">
        <!-- Rate -->
        <ButtonsFilled v-if="order.service_provider?.id" @click="openReviewModal" :disabled="!order.can_review">
          {{ $t('order.actions.rate') }}
        </ButtonsFilled>

        <!-- Order again -->
        <NuxtLinkLocale :to="{ name: 'services-service-order', params: { service: order.service?.slug } }">
          <ButtonsOutline class="w-full">
            {{ $t('order.actions.order_again') }}
          </ButtonsOutline>
        </NuxtLinkLocale>

        <!-- Review modal -->
        <OrderReviewModal :order :notes :reviewModal @hide-modal="reviewModal = false" />
      </div>
    </div>
  </div>
</template>
