<script setup>
  const showModal = ref(false);
  const loader = ref(false);
  const props = defineProps({
    order: {
      type: Object,
      required: true,
    },
  });

  async function handleCancelation() {
    if (loader.value || !order.can_cancel) return;

    loader.value = true;

    try {
      await useApiFetch(`/user/orders/${props.order.id}`, {
        method: 'DELETE',
      });
    } catch (error) {
      console.error('Failed to cancel order:', error);
    }

    showModal.value = true;
  }
</script>

<template>
  <section class="col-span-8 order-6 flex flex-wrap gap-5 items-center justify-between">
    <ButtonsFilled @click="handleCancelation" v-if="order.status == 'new'" :disabled="!order.can_cancel || loader" class="w-full sm:w-fit">
      {{ $t('order.actions.cancel.title') }}
    </ButtonsFilled>

    <span v-if="order.status == 'new' && !order.can_cancel" v-text="$t('order.actions.cancel.description')" class="max-sm:w-full text-gray-500 text-center"></span>
  </section>

  <div v-show="showModal" class="w-full h-full fixed z-50 top-0 start-0 flex items-center justify-center bg-black/20">
    <div class="shadow-lg bg-white rounded-xl py-10 px-5 text-center w-full max-w-sm m-5 flex flex-col gap-6 items-center justify-cente">
      <svg class="w-20 h-20 fill-green-400" viewBox="0 0 80 80" fill="none" xmlns="http://www.w3.org/2000/svg">
        <path
          d="M76.0471 39.9964C76.0471 36.5518 80.7432 32.4988 79.8936 29.3068C79.0095 26.0058 72.8954 24.8633 71.2248 21.9699C69.5542 19.0765 71.575 13.1806 69.1925 10.7981C66.81 8.41564 60.96 10.4709 58.0207 8.77158C55.0814 7.07227 54.0021 0.986906 50.6896 0.102807C47.4976 -0.746848 43.4618 3.94922 40 3.94922C36.5382 3.94922 32.5024 -0.746848 29.3104 0.102807C26.0094 0.986906 24.867 7.10098 21.9735 8.77158C19.0801 10.4422 13.1842 8.42138 10.8017 10.8039C8.41927 13.1863 10.4745 19.0363 8.7752 21.9757C7.07589 24.915 0.990533 25.9943 0.106434 29.3068C-0.760443 32.4988 3.95284 36.5518 3.95284 39.9964C3.95284 43.4409 -0.74322 47.494 0.106434 50.6859C0.990533 53.987 7.1046 55.1294 8.7752 58.0228C10.4458 60.9162 8.42501 66.8121 10.8075 69.1946C13.19 71.5771 19.0399 69.5219 21.9793 71.2212C24.9186 72.9205 25.9979 79.0058 29.3104 79.8899C32.5024 80.7396 36.5382 76.0435 40 76.0435C43.4618 76.0435 47.4976 80.7396 50.6896 79.8899C53.9906 79.0058 55.133 72.8918 58.0264 71.2212C60.9199 69.5506 66.8158 71.5714 69.1982 69.1889C71.5807 66.8064 69.5255 60.9564 71.2248 58.0171C72.9241 55.0777 79.0095 53.9984 79.8936 50.6859C80.7604 47.494 76.0471 43.4409 76.0471 39.9964Z"
        />
        <path d="M40.9731 51.5036C40.1427 52.682 38.4446 52.8128 37.4436 51.7755L23.9209 37.7627C23.566 37.393 23.3755 36.9005 23.3916 36.3934C23.4076 35.8863 23.6287 35.4062 24.0064 35.0587C24.384 34.7113 24.8872 34.5249 25.4052 34.5405C25.9232 34.5562 26.4137 34.7727 26.7687 35.1424L38.6589 47.4699C38.784 47.5997 38.9963 47.5833 39.1001 47.436L54.2209 25.9768C54.3601 25.7563 54.5437 25.5659 54.7604 25.417C54.9772 25.2681 55.2225 25.1639 55.4817 25.1108C55.7408 25.0576 56.0083 25.0565 56.2679 25.1076C56.5275 25.1588 56.7737 25.261 56.9917 25.4082C57.2096 25.5553 57.3948 25.7443 57.5358 25.9637C57.6769 26.183 57.7709 26.4282 57.8122 26.6842C57.8535 26.9402 57.8411 27.2017 57.776 27.453C57.7108 27.7042 57.5942 27.9398 57.4331 28.1455L40.9731 51.5036Z" fill="white" />
      </svg>
      <div class="flex flex-col gap-2 items-center">
        <span v-text="$t('order.canceled.title')" class="text-lg font-medium text-gray-800"></span>
        <span v-text="$t('order.canceled.subtitle')" class="text-gray-500"></span>
      </div>
      <NuxtLinkLocale :to="{ name: 'orders' }" class="w-full">
        <ButtonsFilled class="w-full max-w-xs">
          {{ $t('common.done') }}
        </ButtonsFilled>
      </NuxtLinkLocale>
    </div>
  </div>
</template>
