<script setup>
  const props = defineProps({
    order: {
      type: Object,
      required: true,
    },
  });

  const firstOrder = computed(() => ['service-provider-on-the-way', 'service-provider-arrived'].includes(props.order.status));
</script>

<template>
  <section :class="[firstOrder ? 'order-2' : '']" class="bg-white rounded-xl p-5 md:p-6 shadow col-span-8 flex flex-col gap-5">
    <span v-text="$t('order.summary.title')" class="text-xl md:text-2xl font-medium text-gray-800"></span>
    <div class="flex flex-col gap-5 md:gap-6 p-1 md:p-2">
      <!-- Service -->
      <div class="flex flex-wrap items-center gap-2 justify-between">
        <span v-text="$t('order.summary.service')" class="text-gray-500"></span>
        <span v-text="order.service?.name" class="text-gray-800"></span>
      </div>

      <!-- Quantity -->
      <div class="flex flex-wrap items-center gap-2 justify-between">
        <span v-text="$t('order.summary.quantity')" class="text-gray-500"></span>
        <span v-text="order.quantity" class="text-gray-800"></span>
      </div>

      <!-- Date -->
      <div class="flex flex-wrap items-center gap-2 justify-between">
        <span v-text="$t('order.summary.date')" class="text-gray-500"></span>
        <span v-text="order.created_at" class="text-gray-800"></span>
      </div>

      <!-- Status -->
      <div class="flex flex-wrap items-center gap-2 justify-between">
        <span v-text="$t('order.summary.status')" class="text-gray-500"></span>

        <!-- New or in progress status -->
        <div v-if="['new', 'service-provider-on-the-way', 'service-provider-arrived', 'started'].includes(order.status)" class="inline-flex gap-2 items-center text-amber-600 stroke-amber-600">
          <svg class="w-5 h-5" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
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
          <span v-if="order.status == 'new'" v-text="$t('order.status.new')" class="font-medium"></span>
          <span v-if="order.status == 'service-provider-on-the-way'" v-text="$t('order.status.on_the_way')" class="font-medium"></span>
          <span v-if="order.status == 'service-provider-arrived'" v-text="$t('order.status.arrived')" class="font-medium"></span>
          <span v-if="order.status == 'started'" v-text="$t('order.status.started')" class="font-medium"></span>
        </div>

        <!-- Completed status -->
        <div v-else class="inline-flex gap-2 items-center text-green-500 stroke-green-500">
          <svg class="w-5 h-5" viewBox="0 0 18 18" fill="none" xmlns="http://www.w3.org/2000/svg">
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
          <span v-text="$t('order.status.completed')" class="font-medium"></span>
        </div>
      </div>

      <template v-if="order.description">
        <hr class="border border-dashed border-gray-200" />

        <!-- Description -->
        <div class="flex flex-col gap-3">
          <span v-text="$t('order.summary.description')" class="text-gray-500"></span>
          <p v-text="order.description" class="rounded-xl p-5 bg-gray-100 text-gray-600"></p>
        </div>
      </template>
    </div>
  </section>
</template>
