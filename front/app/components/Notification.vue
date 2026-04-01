<script setup>
  const localePath = useLocalePath();
  const props = defineProps({
    notification: {
      type: Object,
      required: true,
    },
  });

  function handleClick() {
    const orderId = props.notification.data?.order?.id;

    if (orderId) return reloadNuxtApp({ path: localePath({ name: 'orders-order', params: { order: orderId } }) });
  }
</script>

<template>
  <button @click="handleClick" class="flex px-4 py-3 w-full hover:bg-gray-100 dark:hover:bg-gray-700">
    <div class="shrink-0">
      <div class="rounded-full w-12 h-12 flex items-center justify-center bg-brand-50 text-brand-400">
        <template v-if="['order-accepted', 'service-provider-on-the-way', 'service-provider-arrived', 'order-started', 'order-completed'].includes(notification.type)">
          <!-- material-symbols:home-repair-service -->
          <svg class="w-5 h-5" xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24">
            <!-- Icon from Material Symbols by Google - https://github.com/google/material-design-icons/blob/master/LICENSE -->
            <path fill="currentColor" d="M9 8h6V6H9zM2 20v-5h4v1h2v-1h8v1h2v-1h4v5zm0-6v-4q0-.825.588-1.412T4 8h3V6q0-.825.588-1.412T9 4h6q.825 0 1.413.588T17 6v2h3q.825 0 1.413.588T22 10v4h-4v-2h-2v2H8v-2H6v2z" />
          </svg>
        </template>

        <template v-if="notification.type == 'new-order-extra'">
          <!-- heroicons:wrench-screwdriver-solid -->
          <svg class="w-5 h-5" xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24">
            <!-- Icon from HeroIcons by Refactoring UI Inc - https://github.com/tailwindlabs/heroicons/blob/master/LICENSE -->
            <g fill="currentColor">
              <path fill-rule="evenodd" d="M12 6.75a5.25 5.25 0 0 1 6.775-5.025a.75.75 0 0 1 .313 1.248l-3.32 3.319a2.25 2.25 0 0 0 1.941 1.939l3.318-3.319a.75.75 0 0 1 1.248.313a5.25 5.25 0 0 1-5.472 6.756c-1.018-.086-1.87.1-2.309.634L7.344 21.3A3.298 3.298 0 1 1 2.7 16.657l8.684-7.151c.533-.44.72-1.291.634-2.309A5 5 0 0 1 12 6.75M4.117 19.125a.75.75 0 0 1 .75-.75h.008a.75.75 0 0 1 .75.75v.008a.75.75 0 0 1-.75.75h-.008a.75.75 0 0 1-.75-.75z" clip-rule="evenodd" />
              <path d="m10.076 8.64l-2.201-2.2V4.874a.75.75 0 0 0-.364-.643l-3.75-2.25a.75.75 0 0 0-.916.113l-.75.75a.75.75 0 0 0-.113.916l2.25 3.75a.75.75 0 0 0 .643.364h1.564l2.062 2.062z" />
              <path fill-rule="evenodd" d="m12.556 17.329l4.183 4.182a3.375 3.375 0 0 0 4.773-4.773l-3.306-3.305a6.8 6.8 0 0 1-1.53.043c-.394-.034-.682-.006-.867.042a.6.6 0 0 0-.167.063zm3.414-1.36a.75.75 0 0 1 1.06 0l1.875 1.876a.75.75 0 1 1-1.06 1.06L15.97 17.03a.75.75 0 0 1 0-1.06" clip-rule="evenodd" />
            </g>
          </svg>
        </template>
      </div>
    </div>
    <div class="w-full ps-3 space-y-1 text-start">
      <div v-text="notification.title" class="text-gray-800 font-medium"></div>
      <p v-text="notification.description" class="text-gray-500 text-sm"></p>
      <span v-text="notification.date" class="text-gray-400 text-xs mb-1.5"></span>
    </div>
  </button>
</template>
