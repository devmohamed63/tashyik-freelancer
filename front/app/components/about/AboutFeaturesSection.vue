<template>
  <section class="bg-gray-50">
    <div class="container px-4 py-16 md:py-28 flex flex-col gap-5 text-center">
      <div class="inline-flex gap-3 text-2xl md:text-3xl text-gray-800 font-medium justify-center">
        <span v-text="$t('about.features.title.part_1')"></span>
        <span v-text="$t('about.features.title.part_2')" class="text-brand-500"></span>
      </div>
      <p v-text="$t('about.features.subtitle')" class="md:text-xl text-gray-500"></p>

      <div class="mt-8 grid md:grid-cols-2 lg:grid-cols-4 gap-6">
        <!-- Feature 1 -->
        <div class="bg-white shadow-xs rounded-xl p-5 md:p-6 flex flex-col items-start gap-3 text-start">
          <div class="w-14 h-14 bg-brand-100/50 rounded-lg flex items-center justify-center">
            <svg class="w-7 h-7 stroke-brand-500" viewBox="0 0 30 30" fill="none" xmlns="http://www.w3.org/2000/svg">
              <path d="M25.0005 16.2501C25.0005 22.5001 20.6255 25.6251 15.4255 27.4376C15.1532 27.5298 14.8574 27.5254 14.588 27.4251C9.37549 25.6251 5.00049 22.5001 5.00049 16.2501V7.50007C5.00049 7.16855 5.13218 6.85061 5.36661 6.61619C5.60103 6.38177 5.91897 6.25007 6.25049 6.25007C8.75049 6.25007 11.8755 4.75007 14.0505 2.85007C14.3153 2.62382 14.6522 2.49951 15.0005 2.49951C15.3488 2.49951 15.6857 2.62382 15.9505 2.85007C18.138 4.76257 21.2505 6.25007 23.7505 6.25007C24.082 6.25007 24.4 6.38177 24.6344 6.61619C24.8688 6.85061 25.0005 7.16855 25.0005 7.50007V16.2501Z" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" />
            </svg>
          </div>
          <h3 v-text="$t('about.features.feature_1.title')" class="font-medium text-xl text-gray-800"></h3>
          <p v-text="$t('about.features.feature_1.description')" class="text-gray-500"></p>
        </div>

        <!-- Feature 2 -->
        <div class="bg-white shadow-xs rounded-xl p-5 md:p-6 flex flex-col items-start gap-3 text-start">
          <div class="w-14 h-14 bg-brand-100/50 rounded-lg flex items-center justify-center">
            <svg class="w-7 h-7 stroke-brand-500" viewBox="0 0 30 30" fill="none" xmlns="http://www.w3.org/2000/svg">
              <path
                d="M5.00036 17.5001C4.76381 17.5009 4.5319 17.4346 4.33155 17.3088C4.1312 17.183 3.97065 17.003 3.86854 16.7897C3.76644 16.5763 3.72697 16.3383 3.75473 16.1034C3.78249 15.8685 3.87633 15.6463 4.02536 15.4626L16.4004 2.71258C16.4932 2.60543 16.6197 2.53303 16.7591 2.50725C16.8985 2.48147 17.0425 2.50385 17.1675 2.57071C17.2925 2.63758 17.3911 2.74495 17.447 2.87522C17.503 3.00548 17.513 3.15089 17.4754 3.28758L15.0754 10.8126C15.0046 11.002 14.9808 11.2057 15.0061 11.4063C15.0314 11.6069 15.1049 11.7984 15.2205 11.9644C15.336 12.1303 15.4901 12.2657 15.6695 12.359C15.8488 12.4523 16.0482 12.5007 16.2504 12.5001H25.0004C25.2369 12.4993 25.4688 12.5656 25.6692 12.6914C25.8695 12.8171 26.0301 12.9971 26.1322 13.2105C26.2343 13.4239 26.2737 13.6618 26.246 13.8968C26.2182 14.1317 26.1244 14.3539 25.9754 14.5376L13.6004 27.2876C13.5075 27.3947 13.381 27.4671 13.2416 27.4929C13.1022 27.5187 12.9582 27.4963 12.8332 27.4295C12.7082 27.3626 12.6096 27.2552 12.5537 27.1249C12.4977 26.9947 12.4878 26.8493 12.5254 26.7126L14.9254 19.1876C14.9961 18.9982 15.0199 18.7944 14.9946 18.5938C14.9693 18.3932 14.8958 18.2017 14.7802 18.0358C14.6647 17.8699 14.5106 17.7344 14.3313 17.6411C14.1519 17.5478 13.9525 17.4994 13.7504 17.5001H5.00036Z"
                stroke-width="2.5"
                stroke-linecap="round"
                stroke-linejoin="round"
              />
            </svg>
          </div>
          <h3 v-text="$t('about.features.feature_2.title')" class="font-medium text-xl text-gray-800"></h3>
          <p v-text="$t('about.features.feature_2.description')" class="text-gray-500"></p>
        </div>

        <!-- Feature 3 -->
        <div class="bg-white shadow-xs rounded-xl p-5 md:p-6 flex flex-col items-start gap-3 text-start">
          <div class="w-14 h-14 bg-brand-100/50 rounded-lg flex items-center justify-center">
            <svg class="w-7 h-7 stroke-brand-500" viewBox="0 0 30 30" fill="none" xmlns="http://www.w3.org/2000/svg">
              <path d="M15.0005 27.5C21.904 27.5 27.5005 21.9036 27.5005 15C27.5005 8.09644 21.904 2.5 15.0005 2.5C8.09693 2.5 2.50049 8.09644 2.50049 15C2.50049 21.9036 8.09693 27.5 15.0005 27.5Z" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" />
              <path d="M11.25 15L13.75 17.5L18.75 12.5" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" />
            </svg>
          </div>
          <h3 v-text="$t('about.features.feature_3.title')" class="font-medium text-xl text-gray-800"></h3>
          <p v-text="$t('about.features.feature_3.description')" class="text-gray-500"></p>
        </div>

        <!-- Feature 4 -->
        <div class="bg-white shadow-xs rounded-xl p-5 md:p-6 flex flex-col items-start gap-3 text-start">
          <div class="w-14 h-14 bg-brand-100/50 rounded-lg flex items-center justify-center">
            <svg class="w-7 h-7 stroke-brand-500" viewBox="0 0 30 30" fill="none" xmlns="http://www.w3.org/2000/svg">
              <path d="M15 27.5C21.9036 27.5 27.5 21.9036 27.5 15C27.5 8.09644 21.9036 2.5 15 2.5C8.09644 2.5 2.5 8.09644 2.5 15C2.5 21.9036 8.09644 27.5 15 27.5Z" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" />
              <path d="M15 7.5V15L20 17.5" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" />
            </svg>
          </div>
          <h3 v-text="$t('about.features.feature_4.title')" class="font-medium text-xl text-gray-800"></h3>
          <p v-text="$t('about.features.feature_4.description')" class="text-gray-500"></p>
        </div>
      </div>
    </div>
  </section>
</template>
