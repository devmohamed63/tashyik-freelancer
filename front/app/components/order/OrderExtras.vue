<script setup>
  const loader = ref(true);
  const extras = ref([]);
  const orderExtra = ref(null);
  const props = defineProps({
    order: {
      type: Object,
      required: true,
    },
  });

  onMounted(async () => {
    try {
      const response = await useApiFetch(`user/order-extra?order=${props.order.id}`);

      extras.value = response.data;
    } catch (error) {
      console.error('Failed to load order extra:', error);
    }

    loader.value = false;
  });
</script>

<template>
  <div v-if="loader" class="order-5 col-span-8">
    <AppSpinner />
  </div>
  <section v-else-if="extras.length" class="order-5 bg-white rounded-xl p-5 md:p-6 shadow col-span-8 flex flex-col gap-5">
    <span v-text="$t('order.extra.title')" class="text-xl md:text-2xl font-medium text-gray-800"></span>

    <div class="flex flex-col gap-5 p-1 md:p-2">
      <template v-for="(extra, index) of extras" :key="extra.id">
        <div class="flex flex-col gap-6">
          <div class="inline-flex gap-3 justify-between">
            <div class="flex flex-col gap-2">
              <!-- Service -->
              <span v-text="extra.name" class="md:text-lg text-gray-800 font-medium"></span>

              <!-- Date -->
              <span v-text="extra.created_at" class="text-gray-500"></span>
            </div>
            <span :class="[extra.status == 'paid' ? 'bg-green-50 text-green-500 stroke-green-500' : 'bg-amber-50 text-amber-500 stroke-amber-500']" class="inline-flex gap-2 items-center rounded-lg px-3 md:px-5 py-2 md:py-3 h-fit text-nowrap">
              <!-- Paid -->
              <svg v-if="extra.status == 'paid'" class="w-4 h-4" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
                <g clip-path="url(#clip0_9069_2796)">
                  <path d="M14.6663 7.38662V7.99995C14.6655 9.43757 14.2 10.8364 13.3392 11.9878C12.4785 13.1393 11.2685 13.9816 9.88991 14.3892C8.5113 14.7968 7.03785 14.7479 5.68932 14.2497C4.3408 13.7515 3.18944 12.8307 2.40698 11.6247C1.62452 10.4186 1.25287 8.99199 1.34746 7.55749C1.44205 6.12299 1.99781 4.7575 2.93186 3.66467C3.86591 2.57183 5.1282 1.81021 6.53047 1.49338C7.93274 1.17656 9.39985 1.32151 10.713 1.90662" stroke-width="1.33333" stroke-linecap="round" stroke-linejoin="round" />
                  <path d="M14.6667 2.66663L8 9.33996L6 7.33996" stroke-width="1.33333" stroke-linecap="round" stroke-linejoin="round" />
                </g>
                <defs>
                  <clipPath id="clip0_9069_2796">
                    <rect class="w-4 h-4" fill="white" />
                  </clipPath>
                </defs>
              </svg>

              <!-- Unpaid -->
              <svg v-else class="w-4 h-4" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
                <g clip-path="url(#clip0_11144_1188)">
                  <path d="M7.99967 14.6666C11.6816 14.6666 14.6663 11.6819 14.6663 7.99998C14.6663 4.31808 11.6816 1.33331 7.99967 1.33331C4.31778 1.33331 1.33301 4.31808 1.33301 7.99998C1.33301 11.6819 4.31778 14.6666 7.99967 14.6666Z" stroke-linecap="round" stroke-linejoin="round" />
                  <path d="M8 4V8L10.6667 9.33333" stroke-linecap="round" stroke-linejoin="round" />
                </g>
                <defs>
                  <clipPath id="clip0_11144_1188">
                    <rect class="w-4 h-4" fill="white" />
                  </clipPath>
                </defs>
              </svg>

              {{ extra.status == 'paid' ? $t('order.extra.paid') : $t('order.extra.unpaid') }}
            </span>
          </div>

          <!-- View details -->
          <button @click="orderExtra = extra" class="w-fit inline-flex items-center gap-2 text-brand-500 stroke-brand-500 hover:text-brand-700 hover:stroke-brand-700 font-medium">
            {{ $t('order.extra.view_details') }}
            <svg class="w-4 h-4" viewBox="0 0 17 17" fill="none" xmlns="http://www.w3.org/2000/svg">
              <path d="M13.4587 8.5H3.54199" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
              <path d="M8.50033 13.4583L3.54199 8.49996L8.50033 3.54163" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
            </svg>
          </button>
        </div>

        <hr v-if="index + 1 != extras.length" class="my-2 md:my-4 border border-dashed border-gray-200" />
      </template>
    </div>
  </section>

  <OrderExtraModal :extra-id="orderExtra?.id" @hide-modal="orderExtra = null" />
</template>
