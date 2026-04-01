<script setup>
  const props = defineProps({
    order: {
      type: Object,
      required: true,
    },
  });

  const step = computed(() => {
    if (['service-provider-on-the-way', 'service-provider-arrived'].includes(props.order.status)) {
      return props.order.status;
    }
  });
</script>

<template>
  <section v-if="step" class="order-1 bg-white rounded-xl p-5 md:p-6 shadow col-span-8 flex flex-col gap-5">
    <span v-text="$t('order.status.title')" class="text-xl md:text-2xl font-medium text-gray-800"></span>
    <div class="flex flex-col gap-5 md:gap-6 p-1 md:p-2">
      <!-- Confirmed -->
      <div class="inline-flex gap-2 justify-between">
        <div class="inline-flex items-center gap-3 font-medium text-gray-800">
          <svg class="w-10 md:w-12 h-10 md:h-12 bg-green-400 stroke-white rounded-full p-2.5 md:p-3" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
            <path d="M9.99935 18.3333C14.6017 18.3333 18.3327 14.6023 18.3327 9.99996C18.3327 5.39759 14.6017 1.66663 9.99935 1.66663C5.39698 1.66663 1.66602 5.39759 1.66602 9.99996C1.66602 14.6023 5.39698 18.3333 9.99935 18.3333Z" stroke-width="1.66667" stroke-linecap="round" stroke-linejoin="round" />
            <path d="M7.5 10L9.16667 11.6667L12.5 8.33337" stroke-width="1.66667" stroke-linecap="round" stroke-linejoin="round" />
          </svg>
          <span v-text="$t('order.status.confirmed')" class="md:text-lg"></span>
        </div>
        <svg class="w-6 h-6 stroke-green-400" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
          <path d="M9.99935 18.3333C14.6017 18.3333 18.3327 14.6023 18.3327 9.99996C18.3327 5.39759 14.6017 1.66663 9.99935 1.66663C5.39698 1.66663 1.66602 5.39759 1.66602 9.99996C1.66602 14.6023 5.39698 18.3333 9.99935 18.3333Z" stroke-width="1.66667" stroke-linecap="round" stroke-linejoin="round" />
          <path d="M7.5 10L9.16667 11.6667L12.5 8.33337" stroke-width="1.66667" stroke-linecap="round" stroke-linejoin="round" />
        </svg>
      </div>

      <!-- Service provider on the way -->
      <div class="inline-flex gap-2 justify-between">
        <div :class="[step == 'service-provider-on-the-way' ? 'text-brand-500' : 'text-gray-800']" class="inline-flex items-center gap-3 font-medium">
          <svg :class="[step == 'service-provider-on-the-way' ? 'bg-brand-500' : 'bg-green-400']" class="w-10 md:w-12 h-10 md:h-12 stroke-white rounded-full p-2.5 md:p-3" viewBox="0 0 23 20" fill="none" xmlns="http://www.w3.org/2000/svg">
            <path d="M2.5 9.16666L18.3333 1.66666L10.8333 17.5L9.16667 10.8333L2.5 9.16666Z" stroke-width="1.66667" stroke-linecap="round" stroke-linejoin="round" />
          </svg>
          <span v-text="$t('order.status.on_the_way')" class="md:text-lg"></span>
        </div>
        <svg v-if="step != 'service-provider-on-the-way'" class="w-6 h-6 stroke-green-400" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
          <path d="M9.99935 18.3333C14.6017 18.3333 18.3327 14.6023 18.3327 9.99996C18.3327 5.39759 14.6017 1.66663 9.99935 1.66663C5.39698 1.66663 1.66602 5.39759 1.66602 9.99996C1.66602 14.6023 5.39698 18.3333 9.99935 18.3333Z" stroke-width="1.66667" stroke-linecap="round" stroke-linejoin="round" />
          <path d="M7.5 10L9.16667 11.6667L12.5 8.33337" stroke-width="1.66667" stroke-linecap="round" stroke-linejoin="round" />
        </svg>
      </div>

      <!-- Service provider arrived -->
      <div :class="[step == 'service-provider-arrived' ? 'text-brand-500' : 'text-gray-400']" class="inline-flex items-center gap-3 font-medium">
        <svg :class="[step == 'service-provider-arrived' ? 'bg-brand-500 stroke-white' : 'bg-gray-100 stroke-gray-400']" class="w-10 md:w-12 h-10 md:h-12 rounded-full p-2.5 md:p-3" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
          <path d="M16.6663 8.33332C16.6663 12.4942 12.0505 16.8275 10.5005 18.1658C10.3561 18.2744 10.1803 18.3331 9.99967 18.3331C9.81901 18.3331 9.64324 18.2744 9.49884 18.1658C7.94884 16.8275 3.33301 12.4942 3.33301 8.33332C3.33301 6.56521 4.03539 4.86952 5.28563 3.61928C6.53587 2.36904 8.23156 1.66666 9.99967 1.66666C11.7678 1.66666 13.4635 2.36904 14.7137 3.61928C15.964 4.86952 16.6663 6.56521 16.6663 8.33332Z" stroke-width="1.66667" stroke-linecap="round" stroke-linejoin="round" />
          <path d="M10 10.8333C11.3807 10.8333 12.5 9.71406 12.5 8.33334C12.5 6.95263 11.3807 5.83334 10 5.83334C8.61929 5.83334 7.5 6.95263 7.5 8.33334C7.5 9.71406 8.61929 10.8333 10 10.8333Z" stroke-width="1.66667" stroke-linecap="round" stroke-linejoin="round" />
        </svg>
        <span v-text="$t('order.status.arrived')" class="md:text-lg"></span>
      </div>

      <!-- Started -->
      <div class="inline-flex items-center gap-3 font-medium text-gray-400">
        <svg class="w-10 md:w-12 h-10 md:h-12 bg-gray-100 stroke-gray-400 rounded-full p-2.5 md:p-3" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
          <path
            d="M12.2501 5.24997C12.0974 5.40575 12.0119 5.61518 12.0119 5.83331C12.0119 6.05143 12.0974 6.26087 12.2501 6.41664L13.5835 7.74997C13.7392 7.90266 13.9487 7.98819 14.1668 7.98819C14.3849 7.98819 14.5943 7.90266 14.7501 7.74997L17.8918 4.60831C18.3108 5.5343 18.4377 6.566 18.2555 7.56593C18.0733 8.56585 17.5907 9.48651 16.872 10.2052C16.1533 10.9239 15.2327 11.4065 14.2327 11.5887C13.2328 11.7709 12.2011 11.644 11.2751 11.225L5.51679 16.9833C5.18527 17.3148 4.73563 17.5011 4.26679 17.5011C3.79795 17.5011 3.34831 17.3148 3.01679 16.9833C2.68527 16.6518 2.49902 16.2021 2.49902 15.7333C2.49902 15.2645 2.68527 14.8148 3.01679 14.4833L8.77512 8.72497C8.35609 7.79898 8.22922 6.76728 8.41141 5.76735C8.5936 4.76743 9.0762 3.84677 9.79489 3.12807C10.5136 2.40938 11.4342 1.92678 12.4342 1.74459C13.4341 1.5624 14.4658 1.68928 15.3918 2.10831L12.2585 5.24164L12.2501 5.24997Z"
            stroke-width="1.66667"
            stroke-linecap="round"
            stroke-linejoin="round"
          />
        </svg>
        <span v-text="$t('order.status.started')" class="md:text-lg"></span>
      </div>

      <!-- Completed -->
      <div class="inline-flex items-center gap-3 font-medium text-gray-400">
        <svg class="w-10 md:w-12 h-10 md:h-12 bg-gray-100 stroke-gray-400 rounded-full p-2.5 md:p-3" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
          <path d="M9.99935 18.3333C14.6017 18.3333 18.3327 14.6023 18.3327 9.99996C18.3327 5.39759 14.6017 1.66663 9.99935 1.66663C5.39698 1.66663 1.66602 5.39759 1.66602 9.99996C1.66602 14.6023 5.39698 18.3333 9.99935 18.3333Z" stroke-width="1.66667" stroke-linecap="round" stroke-linejoin="round" />
          <path d="M7.5 10L9.16667 11.6667L12.5 8.33337" stroke-width="1.66667" stroke-linecap="round" stroke-linejoin="round" />
        </svg>
        <span v-text="$t('order.status.completed')" class="md:text-lg"></span>
      </div>
    </div>
  </section>
</template>
