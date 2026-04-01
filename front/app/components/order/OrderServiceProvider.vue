<script setup>
  const props = defineProps({
    order: {
      type: Object,
      required: true,
    },
  });
</script>

<template>
  <section v-if="order.service_provider?.id" class="order-2 bg-white h-fit rounded-xl py-5 sm:py-8 px-6 shadow col-span-4 flex flex-col gap-5 xl:sticky top-28">
    <div class="inline-flex items-center gap-4">
      <img class="w-16 h-16 rounded-full" src="/images/service-provider.webp" :alt="$t('order.service_provider.title')" />
      <div class="flex flex-col gap-1">
        <div class="text-sm inline-flex items-center gap-1 text-gray-500 stroke-gray-500">
          <svg class="w-4 h-4" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
            <path d="M12.6673 14V12.6667C12.6673 11.9594 12.3864 11.2811 11.8863 10.781C11.3862 10.281 10.7079 10 10.0007 10H6.00065C5.29341 10 4.61513 10.281 4.11503 10.781C3.61494 11.2811 3.33398 11.9594 3.33398 12.6667V14" stroke-width="1.2" stroke-linecap="round" stroke-linejoin="round" />
            <path d="M8.00065 7.33333C9.47341 7.33333 10.6673 6.13943 10.6673 4.66667C10.6673 3.19391 9.47341 2 8.00065 2C6.52789 2 5.33398 3.19391 5.33398 4.66667C5.33398 6.13943 6.52789 7.33333 8.00065 7.33333Z" stroke-width="1.2" stroke-linecap="round" stroke-linejoin="round" />
          </svg>
          <span v-text="$t('order.service_provider.title')"></span>
        </div>
        <!-- Name -->

        <span v-text="order.service_provider?.name" class="text-lg font-medium text-gray-800"></span>
        <!-- Phone -->
        <span v-text="`${$t('order.service_provider.phone')}: ${order.service_provider?.phone}`" class="text-sm text-gray-500"></span>
      </div>
    </div>

    <!-- View on map -->
    <a :href="order.service_provider?.map_url" target="_blank" class="max-w-md">
      <div class="w-full h-fit relative max-sm:text-sm">
        <img class="w-full rounded-lg" src="/images/service-provider-map.webp" alt="..." />
        <button class="absolute bottom-4 start-4 bg-white rounded-lg px-4 py-3 font-medium text-brand-500 stroke-brand-500 inline-flex items-center gap-2">
          <svg class="max-sm:w-4 w-5 max-sm:h-4 h-5" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
            <path d="M2 7.33331L14.6667 1.33331L8.66667 14L7.33333 8.66665L2 7.33331Z" stroke-width="1.33333" stroke-linecap="round" stroke-linejoin="round" />
          </svg>
          {{ $t('order.service_provider.actions.view_on_map') }}
        </button>
      </div>
    </a>

    <div class="max-w-md flex flex-col sm:grid grid-cols-2 lg:flex 2xl:grid gap-4">
      <!-- Call -->
      <a :href="`tel:${order.service_provider?.phone}`" target="_blank" class="w-full *:w-full">
        <ButtonsOutline>
          <svg class="w-4 h-4 mt-0.5 stroke-brand-500" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
            <g clip-path="url(#clip0_11684_5580)">
              <path
                d="M10.0336 3.33335C10.6848 3.4604 11.2832 3.77886 11.7523 4.24798C12.2214 4.7171 12.5399 5.31553 12.6669 5.96669M10.0336 0.666687C11.3865 0.816978 12.648 1.4228 13.6111 2.38469C14.5742 3.34658 15.1816 4.60736 15.3336 5.96002M14.6669 11.28V13.28C14.6677 13.4657 14.6297 13.6495 14.5553 13.8196C14.4809 13.9897 14.3718 14.1424 14.235 14.2679C14.0982 14.3934 13.9367 14.489 13.7608 14.5485C13.5849 14.608 13.3985 14.6301 13.2136 14.6134C11.1622 14.3904 9.19161 13.6895 7.46028 12.5667C5.8495 11.5431 4.48384 10.1775 3.46028 8.56669C2.3336 6.82749 1.63244 4.84735 1.41361 2.78669C1.39695 2.60233 1.41886 2.41653 1.47795 2.2411C1.53703 2.06568 1.63199 1.90448 1.75679 1.76777C1.88159 1.63106 2.03348 1.52183 2.20281 1.44704C2.37213 1.37224 2.55517 1.33353 2.74028 1.33335H4.74028C5.06382 1.33017 5.37748 1.44474 5.62279 1.65571C5.8681 1.86668 6.02833 2.15965 6.07361 2.48002C6.15803 3.12006 6.31458 3.74851 6.54028 4.35335C6.62998 4.59197 6.64939 4.8513 6.59622 5.10061C6.54305 5.34992 6.41952 5.57876 6.24028 5.76002L5.39361 6.60669C6.34265 8.27572 7.72458 9.65765 9.39361 10.6067L10.2403 9.76002C10.4215 9.58078 10.6504 9.45725 10.8997 9.40408C11.149 9.35091 11.4083 9.37032 11.6469 9.46002C12.2518 9.68572 12.8802 9.84227 13.5203 9.92669C13.8441 9.97237 14.1399 10.1355 14.3513 10.385C14.5627 10.6345 14.6751 10.9531 14.6669 11.28Z"
                stroke-width="1.5"
                stroke-linecap="round"
                stroke-linejoin="round"
              />
            </g>
            <defs>
              <clipPath id="clip0_11684_5580">
                <rect class="w-4 h-4" fill="white" />
              </clipPath>
            </defs>
          </svg>
          <span v-text="$t('order.service_provider.actions.call')" class="text-sm"></span>
        </ButtonsOutline>
      </a>

      <!-- Send a message -->
      <a :href="`https://wa.me/${order.service_provider?.phone}`" target="_blank" class="w-full *:w-full">
        <ButtonsOutline>
          <svg class="w-4 h-4 mt-0.5 fill-brand-500" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
            <path
              d="M0 16L1.12466 11.8913C0.430666 10.6886 0.0659999 9.32531 0.0666665 7.92732C0.0686665 3.55666 3.62533 0 7.99532 0C10.116 0.000666665 12.1066 0.826665 13.604 2.32533C15.1006 3.82399 15.9246 5.81599 15.924 7.93465C15.922 12.306 12.3653 15.8626 7.99532 15.8626C6.66865 15.862 5.36132 15.5293 4.20332 14.8973L0 16ZM4.39799 13.462C5.51532 14.1253 6.58198 14.5226 7.99265 14.5233C11.6246 14.5233 14.5833 11.5673 14.5853 7.93332C14.5866 4.29199 11.642 1.34 7.99798 1.33866C4.36332 1.33866 1.40666 4.29466 1.40533 7.92798C1.40466 9.41131 1.83933 10.522 2.56933 11.684L1.90333 14.116L4.39799 13.462ZM11.9893 9.81931C11.94 9.73665 11.808 9.68731 11.6093 9.58798C11.4113 9.48865 10.4373 9.00931 10.2553 8.94331C10.074 8.87731 9.94198 8.84398 9.80931 9.04265C9.67731 9.24065 9.29731 9.68731 9.18198 9.81931C9.06665 9.95131 8.95065 9.96798 8.75265 9.86865C8.55465 9.76931 7.91598 9.56065 7.15932 8.88531C6.57065 8.35998 6.17265 7.71132 6.05732 7.51265C5.94199 7.31465 6.04532 7.20732 6.14399 7.10865C6.23332 7.01998 6.34199 6.87732 6.44132 6.76132C6.54199 6.64665 6.57465 6.56399 6.64132 6.43132C6.70732 6.29932 6.67465 6.18332 6.62465 6.08399C6.57465 5.98532 6.17865 5.00999 6.01399 4.61332C5.85265 4.22732 5.68932 4.27932 5.56799 4.27332L5.18799 4.26666C5.05599 4.26666 4.84132 4.31599 4.65999 4.51466C4.47866 4.71332 3.96666 5.19199 3.96666 6.16732C3.96666 7.14265 4.67666 8.08465 4.77532 8.21665C4.87466 8.34865 6.17199 10.35 8.15932 11.208C8.63198 11.412 9.00131 11.534 9.28865 11.6253C9.76331 11.776 10.1953 11.7546 10.5366 11.704C10.9173 11.6473 11.7086 11.2246 11.874 10.762C12.0393 10.2986 12.0393 9.90198 11.9893 9.81931Z"
            />
          </svg>
          <span v-text="$t('order.service_provider.actions.whatsapp')" class="text-sm"></span>
        </ButtonsOutline>
      </a>
    </div>
  </section>
</template>
