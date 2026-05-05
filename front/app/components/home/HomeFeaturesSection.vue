<template>
  <section class="bg-brand-50">
    <div class="container px-4 py-16 flex flex-col gap-5 text-center">
      <h2 v-text="$t('home.features.title')" class="text-2xl md:text-3xl text-gray-800 font-medium"></h2>
      <p v-text="$t('home.features.subtitle')" class="md:text-xl text-gray-600"></p>

      <div class="mt-8 grid md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-5 gap-10">
        <!-- Feature 1 -->
        <div class="flex flex-col justify-center items-center md:items-start gap-1">
          <div class="w-12 h-12 bg-brand-100/50 rounded-lg flex items-center justify-center">
            <svg class="w-6 h-6 stroke-brand-500" width="22" height="22" viewBox="0 0 22 22" fill="none" xmlns="http://www.w3.org/2000/svg">
              <path
                d="M13.7005 5.30369C13.5172 5.49062 13.4146 5.74194 13.4146 6.00369C13.4146 6.26544 13.5172 6.51676 13.7005 6.70369L15.3005 8.30369C15.4874 8.48692 15.7387 8.58955 16.0005 8.58955C16.2622 8.58955 16.5135 8.48692 16.7005 8.30369L19.8065 5.19869C20.1265 4.87669 20.6695 4.97869 20.7895 5.41669C21.0916 6.51557 21.0745 7.67776 20.7402 8.76728C20.406 9.85681 19.7683 10.8286 18.9019 11.5689C18.0354 12.3093 16.9761 12.7875 15.8477 12.9478C14.7194 13.108 13.5688 12.9436 12.5305 12.4737L4.62047 20.3837C4.22264 20.7814 3.68313 21.0048 3.12061 21.0047C2.5581 21.0046 2.01866 20.781 1.62097 20.3832C1.22327 19.9854 0.999906 19.4459 1 18.8833C1.00009 18.3208 1.22364 17.7814 1.62147 17.3837L9.53147 9.47369C9.06161 8.4354 8.89717 7.28477 9.0574 6.15644C9.21763 5.02811 9.69589 3.96874 10.4362 3.10231C11.1766 2.23587 12.1484 1.5982 13.2379 1.26392C14.3274 0.929633 15.4896 0.912558 16.5885 1.21469C17.0265 1.33469 17.1285 1.87669 16.8075 2.19869L13.7005 5.30369Z"
                stroke-width="2"
                stroke-linecap="round"
                stroke-linejoin="round"
              />
            </svg>
          </div>
          <h3 v-text="$t('home.features.feature_1.title')" class="font-bold text-3xl mt-3 text-brand-500"></h3>
          <p v-text="$t('home.features.feature_1.description')" class="text-gray-800 text-xl"></p>
        </div>

        <!-- Feature 2 -->
        <div class="flex flex-col justify-center items-center md:items-start gap-1">
          <div class="w-12 h-12 bg-brand-100/50 rounded-lg flex items-center justify-center">
            <svg class="w-6 h-6 stroke-brand-500" width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
              <path d="M16 11L18 13L22 9" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
              <path d="M16 21V19C16 17.9391 15.5786 16.9217 14.8284 16.1716C14.0783 15.4214 13.0609 15 12 15H6C4.93913 15 3.92172 15.4214 3.17157 16.1716C2.42143 16.9217 2 17.9391 2 19V21" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
              <path d="M9 11C11.2091 11 13 9.20914 13 7C13 4.79086 11.2091 3 9 3C6.79086 3 5 4.79086 5 7C5 9.20914 6.79086 11 9 11Z" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
            </svg>
          </div>
          <h3 v-text="$t('home.features.feature_2.title')" class="font-bold text-3xl mt-3 text-brand-500"></h3>
          <p v-text="$t('home.features.feature_2.description')" class="text-gray-800 text-xl"></p>
        </div>

        <!-- Feature 3 -->
        <div class="flex flex-col justify-center items-center md:items-start gap-1">
          <div class="w-12 h-12 bg-brand-100/50 rounded-lg flex items-center justify-center">
            <svg class="w-6 h-6 stroke-brand-500" width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
              <path d="M21.8011 9.99999C22.2578 12.2413 21.9323 14.5714 20.879 16.6018C19.8256 18.6322 18.108 20.24 16.0126 21.1573C13.9172 22.0746 11.5707 22.2458 9.3644 21.6424C7.15807 21.0389 5.22529 19.6974 3.88838 17.8414C2.55146 15.9854 1.89122 13.7272 2.01776 11.4434C2.14431 9.15952 3.04998 6.98808 4.58375 5.29116C6.11752 3.59424 8.18668 2.47442 10.4462 2.11844C12.7056 1.76247 15.0189 2.19185 17.0001 3.33499" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
              <path d="M9 11L12 14L22 4" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
            </svg>
          </div>
          <h3 v-text="$t('home.features.feature_3.title')" class="font-bold text-3xl mt-3 text-brand-500"></h3>
          <p v-text="$t('home.features.feature_3.description')" class="text-gray-800 text-xl"></p>
        </div>

        <!-- Feature 4 -->
        <div class="flex flex-col justify-center items-center md:items-start gap-1">
          <div class="w-12 h-12 bg-brand-100/50 rounded-lg flex items-center justify-center">
            <svg class="w-6 h-6 stroke-brand-500" width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
              <path d="M20 10C20 14.993 14.461 20.193 12.601 21.799C12.4277 21.9293 12.2168 21.9998 12 21.9998C11.7832 21.9998 11.5723 21.9293 11.399 21.799C9.539 20.193 4 14.993 4 10C4 7.87827 4.84285 5.84344 6.34315 4.34315C7.84344 2.84285 9.87827 2 12 2C14.1217 2 16.1566 2.84285 17.6569 4.34315C19.1571 5.84344 20 7.87827 20 10Z" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
              <path d="M12 13C13.6569 13 15 11.6569 15 10C15 8.34315 13.6569 7 12 7C10.3431 7 9 8.34315 9 10C9 11.6569 10.3431 13 12 13Z" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
            </svg>
          </div>
          <h3 v-text="$t('home.features.feature_4.title')" class="font-bold text-3xl mt-3 text-brand-500"></h3>
          <p v-text="$t('home.features.feature_4.description')" class="text-gray-800 text-xl"></p>
        </div>

        <!-- Feature 5 -->
        <div class="flex flex-col justify-center items-center md:items-start gap-1">
          <div class="w-12 h-12 bg-brand-100/50 rounded-lg flex items-center justify-center">
            <svg class="w-6 h-6 stroke-brand-500" width="22" height="22" viewBox="0 0 22 22" fill="none" xmlns="http://www.w3.org/2000/svg">
              <path
                d="M10.5268 1.29489C10.5706 1.20635 10.6383 1.13183 10.7223 1.07972C10.8062 1.02761 10.903 1 11.0018 1C11.1006 1 11.1974 1.02761 11.2813 1.07972C11.3653 1.13183 11.433 1.20635 11.4768 1.29489L13.7868 5.97389C13.939 6.28186 14.1636 6.5483 14.4414 6.75035C14.7192 6.95239 15.0419 7.08401 15.3818 7.13389L20.5478 7.88989C20.6457 7.90408 20.7376 7.94537 20.8133 8.00909C20.8889 8.07282 20.9452 8.15644 20.9758 8.2505C21.0064 8.34456 21.0101 8.4453 20.9864 8.54133C20.9627 8.63736 20.9126 8.72485 20.8418 8.79389L17.1058 12.4319C16.8594 12.672 16.6751 12.9684 16.5686 13.2955C16.4622 13.6227 16.4369 13.9708 16.4948 14.3099L17.3768 19.4499C17.3941 19.5477 17.3835 19.6485 17.3463 19.7406C17.3091 19.8327 17.2467 19.9125 17.1663 19.9709C17.086 20.0293 16.9908 20.0639 16.8917 20.0708C16.7926 20.0777 16.6935 20.0566 16.6058 20.0099L11.9878 17.5819C11.6835 17.4221 11.345 17.3386 11.0013 17.3386C10.6576 17.3386 10.3191 17.4221 10.0148 17.5819L5.3978 20.0099C5.31013 20.0563 5.2112 20.0772 5.11225 20.0701C5.0133 20.0631 4.91832 20.0285 4.83809 19.9701C4.75787 19.9118 4.69563 19.8321 4.65846 19.7401C4.62128 19.6482 4.61066 19.5476 4.6278 19.4499L5.5088 14.3109C5.567 13.9716 5.54178 13.6233 5.43534 13.2959C5.32889 12.9686 5.14441 12.672 4.8978 12.4319L1.1618 8.79489C1.09039 8.72593 1.03979 8.63829 1.01576 8.54197C0.991731 8.44565 0.995237 8.34451 1.02588 8.25008C1.05652 8.15566 1.11307 8.07174 1.18908 8.00788C1.26509 7.94402 1.3575 7.90279 1.4558 7.88889L6.6208 7.13389C6.96106 7.08439 7.28419 6.95295 7.56238 6.75088C7.84058 6.54881 8.0655 6.28216 8.2178 5.97389L10.5268 1.29489Z"
                stroke-width="2"
                stroke-linecap="round"
                stroke-linejoin="round"
              />
            </svg>
          </div>
          <h3 v-text="$t('home.features.feature_5.title')" class="font-bold text-3xl mt-3 text-brand-500"></h3>
          <p v-text="$t('home.features.feature_5.description')" class="text-gray-800 text-xl"></p>
        </div>
      </div>
    </div>
  </section>
</template>
