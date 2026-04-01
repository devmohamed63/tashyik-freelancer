<template>
  <section class="bg-gray-50">
    <div class="container px-4 py-16 md:py-28 flex flex-col gap-14 text-center">
      <!-- Heading -->
      <div class="flex flex-col gap-4">
        <h3 v-text="$t('about.audience.title')" class="text-2xl md:text-3xl text-gray-800 font-medium"></h3>
        <p v-text="$t('about.audience.subtitle')" class="md:text-xl text-gray-500"></p>
      </div>

      <div class="flex flex-col gap-10 max-w-4xl mx-auto">
        <!-- Individuals -->
        <div class="flex flex-col md:grid grid-cols-12 gap-8">
          <div class="col-span-5">
            <img class="w-full" src="/images/individuals.webp" loading="lazy" alt="individuals" />
          </div>
          <div class="col-span-7">
            <div class="flex flex-col items-start gap-3 text-start">
              <div class="w-14 h-14 bg-brand-100/50 rounded-lg flex items-center justify-center">
                <svg class="w-7 h-7 stroke-brand-500" viewBox="0 0 28 28" fill="none" xmlns="http://www.w3.org/2000/svg">
                  <path d="M17.5 24.5V15.1667C17.5 14.8572 17.3771 14.5605 17.1583 14.3417C16.9395 14.1229 16.6428 14 16.3333 14H11.6667C11.3572 14 11.0605 14.1229 10.8417 14.3417C10.6229 14.5605 10.5 14.8572 10.5 15.1667V24.5" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                  <path d="M3.5 11.6667C3.49992 11.3272 3.57389 10.9919 3.71675 10.684C3.85962 10.3761 4.06793 10.1031 4.32717 9.88399L12.4938 2.88399C12.915 2.52805 13.4486 2.33276 14 2.33276C14.5514 2.33276 15.085 2.52805 15.5062 2.88399L23.6728 9.88399C23.9321 10.1031 24.1404 10.3761 24.2832 10.684C24.4261 10.9919 24.5001 11.3272 24.5 11.6667V22.1667C24.5 22.7855 24.2542 23.379 23.8166 23.8166C23.379 24.2542 22.7855 24.5 22.1667 24.5H5.83333C5.21449 24.5 4.621 24.2542 4.18342 23.8166C3.74583 23.379 3.5 22.7855 3.5 22.1667V11.6667Z" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                </svg>
              </div>
              <span v-text="$t('about.audience.individuals.title')" class="font-medium text-xl text-gray-800"></span>
              <p v-text="$t('about.audience.individuals.description')" class="text-gray-500"></p>
            </div>
          </div>
        </div>

        <!-- Institutions -->
        <div class="flex flex-col md:grid grid-cols-12 gap-8">
          <div class="max-md:order-1 col-span-7">
            <div class="flex flex-col items-start gap-3 text-start">
              <div class="w-14 h-14 bg-brand-100/50 rounded-lg flex items-center justify-center">
                <svg class="w-7 h-7 stroke-brand-500" viewBox="0 0 28 28" fill="none" xmlns="http://www.w3.org/2000/svg">
                  <path d="M11.6665 14H16.3332" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                  <path d="M11.6665 9.33325H16.3332" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                  <path d="M16.3332 24.5001V21.0001C16.3332 20.3812 16.0873 19.7878 15.6498 19.3502C15.2122 18.9126 14.6187 18.6667 13.9998 18.6667C13.381 18.6667 12.7875 18.9126 12.3499 19.3502C11.9123 19.7878 11.6665 20.3812 11.6665 21.0001V24.5001" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                  <path d="M7.00016 11.6667H4.66683C4.04799 11.6667 3.4545 11.9126 3.01691 12.3502C2.57933 12.7878 2.3335 13.3812 2.3335 14.0001V22.1667C2.3335 22.7856 2.57933 23.3791 3.01691 23.8167C3.4545 24.2542 4.04799 24.5001 4.66683 24.5001H23.3335C23.9523 24.5001 24.5458 24.2542 24.9834 23.8167C25.421 23.3791 25.6668 22.7856 25.6668 22.1667V10.5001C25.6668 9.88124 25.421 9.28775 24.9834 8.85017C24.5458 8.41258 23.9523 8.16675 23.3335 8.16675H21.0002" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                  <path d="M7 24.5V5.83333C7 5.21449 7.24583 4.621 7.68342 4.18342C8.121 3.74583 8.71449 3.5 9.33333 3.5H18.6667C19.2855 3.5 19.879 3.74583 20.3166 4.18342C20.7542 4.621 21 5.21449 21 5.83333V24.5" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                </svg>
              </div>
              <span v-text="$t('about.audience.institutions.title')" class="font-medium text-xl text-gray-800"></span>
              <p v-text="$t('about.audience.institutions.description')" class="text-gray-500"></p>
            </div>
          </div>
          <div class="col-span-5">
            <img class="w-full" src="/images/institutions.webp" loading="lazy" alt="institutions" />
          </div>
        </div>

        <!-- Service providers -->
        <div class="flex flex-col md:grid grid-cols-12 gap-8">
          <div class="col-span-5">
            <img class="w-full" src="/images/service-providers.webp" loading="lazy" alt="service-providers" />
          </div>
          <div class="col-span-7">
            <div class="flex flex-col items-start gap-3 text-start">
              <div class="w-14 h-14 bg-brand-100/50 rounded-lg flex items-center justify-center">
                <svg class="w-7 h-7 stroke-brand-500" viewBox="0 0 28 28" fill="none" xmlns="http://www.w3.org/2000/svg">
                  <path d="M18.6665 12.8333L20.9998 15.1667L25.6665 10.5" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                  <path d="M18.6668 24.5V22.1667C18.6668 20.929 18.1752 19.742 17.3 18.8668C16.4248 17.9917 15.2378 17.5 14.0002 17.5H7.00016C5.76249 17.5 4.5755 17.9917 3.70033 18.8668C2.82516 19.742 2.3335 20.929 2.3335 22.1667V24.5" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                  <path d="M10.5002 12.8333C13.0775 12.8333 15.1668 10.744 15.1668 8.16667C15.1668 5.58934 13.0775 3.5 10.5002 3.5C7.92283 3.5 5.8335 5.58934 5.8335 8.16667C5.8335 10.744 7.92283 12.8333 10.5002 12.8333Z" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                </svg>
              </div>
              <span v-text="$t('about.audience.service_providers.title')" class="font-medium text-xl text-gray-800"></span>
              <p v-text="$t('about.audience.service_providers.description')" class="text-gray-500"></p>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>
</template>
