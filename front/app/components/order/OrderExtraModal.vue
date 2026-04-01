<script setup>
  import { Modal } from 'flowbite';

  const targetElement = ref(null);
  const modal = ref(null);
  const loader = ref(true);
  const emit = defineEmits(['hide-modal']);
  const extra = ref(null);

  const props = defineProps({
    extraId: {
      type: Number,
      required: false,
    },
  });

  async function loadExtra(confirmOrder = 0) {
    try {
      const response = await useApiFetch(`user/order-extra/${props.extraId}?confirm_order=${confirmOrder}`);

      extra.value = response;
    } catch (error) {
      console.error('Failed to load extra:', error);
    }
  }

  watch(
    () => props.extraId,
    async () => {
      if (props.extraId) {
        loader.value = true;

        modal.value.show();

        await loadExtra();

        loader.value = false;
      } else {
        modal.value.hide();

        extra.value = null;
      }
    },
  );

  async function confirmOrder() {
    if (loader.value) return;

    loader.value = true;

    await loadExtra(1);

    reloadNuxtApp();
  }

  onMounted(() => {
    if (targetElement.value) {
      modal.value = new Modal(targetElement.value, {
        onHide: () => {
          emit('hide-modal');
        },
        backdropClasses: 'modal-backdrop',
      });
    }
  });
</script>

<template>
  <div v-show="extraId" class="w-full h-full fixed z-30 start-0 top-0 bg-black/40"></div>

  <div ref="targetElement" id="orderExtraModal" tabindex="-1" aria-hidden="true" class="fixed top-0 left-0 right-0 z-50 hidden w-full p-4 overflow-x-hidden overflow-y-auto md:inset-0 h-[calc(100%-1rem)] max-h-full">
    <div class="relative w-full max-w-md max-h-full">
      <!-- Modal content -->
      <div class="relative bg-white rounded-2xl shadow-sm dark:bg-gray-700">
        <!-- Modal header -->
        <div class="flex items-center justify-between p-4 border-b rounded-t border-gray-100">
          <span v-text="$t('order.extra.invoice.title')" class="text-lg text-gray-800 font-medium"></span>
          <button @click="$emit('hide-modal')" id="closeButton" type="button" class="text-gray-400 bg-transparent hover:bg-gray-200 hover:text-gray-900 rounded-lg text-sm p-1.5 ms-auto inline-flex items-center dark:hover:bg-gray-600 dark:hover:text-white">
            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path></svg>
          </button>
        </div>

        <!-- Modal body -->
        <div v-if="extraId" class="p-6 flex flex-col gap-5">
          <AppSpinner v-if="loader" />

          <template v-else-if="extra">
            <!-- Service -->
            <div class="flex flex-wrap items-center gap-2 justify-between">
              <span v-text="$t('services.invoice.service')" class="text-gray-500"></span>
              <span v-text="extra.name" class="text-gray-800"></span>
            </div>

            <!-- Status -->
            <div class="flex flex-wrap items-center gap-2 justify-between">
              <span v-text="$t('order.summary.status')" class="text-gray-500"></span>

              <!-- Unpaid -->
              <div v-if="extra.status == 'pending'" class="inline-flex gap-2 items-center text-amber-600 stroke-amber-600">
                <svg class="w-4 h-4" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
                  <g clip-path="url(#clip0_11315_2138)">
                    <path d="M7.99967 14.6666C11.6816 14.6666 14.6663 11.6819 14.6663 7.99998C14.6663 4.31808 11.6816 1.33331 7.99967 1.33331C4.31778 1.33331 1.33301 4.31808 1.33301 7.99998C1.33301 11.6819 4.31778 14.6666 7.99967 14.6666Z" stroke-linecap="round" stroke-linejoin="round" />
                    <path d="M8 4V8L10.6667 9.33333" stroke-linecap="round" stroke-linejoin="round" />
                  </g>
                  <defs>
                    <clipPath id="clip0_11315_2138">
                      <rect class="w-4 h-4" fill="white" />
                    </clipPath>
                  </defs>
                </svg>
                <span v-text="$t('order.extra.unpaid')"></span>
              </div>

              <!-- Paid -->
              <div v-else class="inline-flex gap-2 items-center text-green-500 stroke-green-500">
                <svg class="w-4 h-4" viewBox="0 0 18 18" fill="none" xmlns="http://www.w3.org/2000/svg">
                  <g clip-path="url(#clip0_9069_2685)">
                    <path d="M9 16.4999C13.1421 16.4999 16.5 13.142 16.5 8.99988C16.5 4.85774 13.1421 1.49988 9 1.49988C4.85786 1.49988 1.5 4.85774 1.5 8.99988C1.5 13.142 4.85786 16.4999 9 16.4999Z" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                    <path d="M6.75 9.00012L8.25 10.5001L11.25 7.50012" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                  </g>
                  <defs>
                    <clipPath id="clip0_9069_2685">
                      <rect class="w-5 h-5" fill="white" />
                    </clipPath>
                  </defs>
                </svg>
                <span v-text="$t('order.extra.paid')"></span>
              </div>
            </div>

            <!-- Price -->
            <div class="flex flex-wrap items-center gap-2 justify-between">
              <span v-text="$t('order.extra.invoice.price')" class="text-gray-500"></span>
              <span v-text="`${extra.price} ${extra.currency}`" class="text-gray-800"></span>
            </div>

            <!-- Tax -->
            <div class="flex flex-wrap items-center gap-2 justify-between">
              <span v-text="$t('order.extra.invoice.tax')" class="text-gray-500"></span>
              <span v-text="`${extra.tax} ${extra.currency}`" class="text-gray-800"></span>
            </div>

            <!-- Materials -->
            <div class="flex flex-wrap items-center gap-2 justify-between">
              <span v-text="$t('order.extra.invoice.materials')" class="text-gray-500"></span>
              <span v-text="`${extra.materials} ${extra.currency}`" class="text-gray-800"></span>
            </div>

            <!-- Wallet balance -->
            <div class="flex flex-wrap items-center gap-2 justify-between">
              <span v-text="$t('order.invoice.wallet_balance')" class="text-gray-500"></span>
              <span v-text="`-${extra.wallet_balance} ${extra.currency}`" class="text-green-500"></span>
            </div>

            <!-- Total -->
            <div class="flex flex-wrap items-center gap-2 justify-between">
              <span v-text="$t('services.invoice.total')" class="text-gray-500"></span>
              <span v-text="`${extra.total} ${extra.currency}`" class="text-gray-800 font-medium"></span>
            </div>

            <ButtonsFilled v-if="extra.status == 'pending'">
              <a v-if="extra.payment_link" :href="extra.payment_link" v-text="$t('order.summary.pay')" class="w-full h-full static-color"></a>
              <span v-else v-text="$t('order.summary.confirm')" class="w-full h-full" @click="confirmOrder"></span>
            </ButtonsFilled>
          </template>
        </div>
      </div>
    </div>
  </div>
</template>
