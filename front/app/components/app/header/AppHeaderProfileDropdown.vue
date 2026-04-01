<script setup>
  const auth = useAuthStore();

  async function handleLogout() {
    await auth.logout();
  }
</script>

<template>
  <ul class="py-2 text-sm text-gray-700 dark:text-gray-200" aria-labelledby="dropdownInformdropdownProfileButtonationButton">
    <!-- Profile -->
    <li>
      <NuxtLinkLocale :to="{ name: 'profile' }" data-drawer-hide="mobile-navigation-drawer" aria-controls="mobile-navigation-drawer" class="flex flex-row gap-2 items-center w-full p-4 hover:bg-gray-50">
        <div class="inline-flex gap-3">
          <!-- majesticons:user-line -->
          <svg class="w-10 h-10 p-2.5 bg-gray-100 rounded-lg text-gray-400" xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24">
            <!-- Icon from Majesticons by Gerrit Halfmann - https://github.com/halfmage/majesticons/blob/main/LICENSE -->
            <g fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2">
              <circle cx="12" cy="8" r="5" />
              <path d="M20 21a8 8 0 1 0-16 0m16 0a8 8 0 1 0-16 0" />
            </g>
          </svg>
          <div class="flex flex-col">
            <span v-text="$t('navigation.profile.title')" class="font-medium text-gray-800"></span>
            <span v-text="$t('navigation.profile.subtitle')" class="text-gray-400"></span>
          </div>
        </div>
      </NuxtLinkLocale>
    </li>

    <!-- Orders -->
    <li>
      <NuxtLinkLocale :to="{ name: 'orders' }" data-drawer-hide="mobile-navigation-drawer" aria-controls="mobile-navigation-drawer" class="flex flex-row gap-2 items-center w-full p-4 hover:bg-gray-50">
        <div class="inline-flex gap-3">
          <svg class="w-10 h-10 p-2.5 bg-gray-100 rounded-lg stroke-gray-400" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
            <path d="M9.16667 18.1084C9.42003 18.2546 9.70744 18.3316 10 18.3316C10.2926 18.3316 10.58 18.2546 10.8333 18.1084L16.6667 14.775C16.9198 14.6289 17.13 14.4188 17.2763 14.1657C17.4225 13.9127 17.4997 13.6256 17.5 13.3334V6.66669C17.4997 6.37442 17.4225 6.08736 17.2763 5.83432C17.13 5.58128 16.9198 5.37116 16.6667 5.22502L10.8333 1.89169C10.58 1.74541 10.2926 1.6684 10 1.6684C9.70744 1.6684 9.42003 1.74541 9.16667 1.89169L3.33333 5.22502C3.08022 5.37116 2.86998 5.58128 2.72372 5.83432C2.57745 6.08736 2.5003 6.37442 2.5 6.66669V13.3334C2.5003 13.6256 2.57745 13.9127 2.72372 14.1657C2.86998 14.4188 3.08022 14.6289 3.33333 14.775L9.16667 18.1084Z" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
            <path d="M10 18.3333V10" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
            <path d="M2.74219 5.83331L10.0005 9.99998L17.2589 5.83331" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
            <path d="M6.25 3.55835L13.75 7.85002" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
          </svg>
          <div class="flex flex-col">
            <span v-text="$t('navigation.orders.title')" class="font-medium text-gray-800"></span>
            <span v-text="$t('navigation.orders.subtitle')" class="text-gray-400"></span>
          </div>
        </div>
      </NuxtLinkLocale>
    </li>

    <!-- Addresses -->
    <li>
      <NuxtLinkLocale :to="{ name: 'addresses' }" data-drawer-hide="mobile-navigation-drawer" aria-controls="mobile-navigation-drawer" class="flex flex-row gap-2 items-center w-full p-4 hover:bg-gray-50">
        <div class="inline-flex gap-3">
          <svg class="w-10 h-10 p-2.5 bg-gray-100 rounded-lg stroke-gray-400" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
            <path d="M17.5 8.33331C17.5 14.1666 10 19.1666 10 19.1666C10 19.1666 2.5 14.1666 2.5 8.33331C2.5 6.34419 3.29018 4.43653 4.6967 3.03001C6.10322 1.62349 8.01088 0.833313 10 0.833313C11.9891 0.833313 13.8968 1.62349 15.3033 3.03001C16.7098 4.43653 17.5 6.34419 17.5 8.33331Z" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
            <path d="M10 10.8333C11.3807 10.8333 12.5 9.71402 12.5 8.33331C12.5 6.9526 11.3807 5.83331 10 5.83331C8.61929 5.83331 7.5 6.9526 7.5 8.33331C7.5 9.71402 8.61929 10.8333 10 10.8333Z" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
          </svg>
          <div class="flex flex-col">
            <span v-text="$t('navigation.addresses.title')" class="font-medium text-gray-800"></span>
            <span v-text="$t('navigation.addresses.subtitle')" class="text-gray-400"></span>
          </div>
        </div>
      </NuxtLinkLocale>
    </li>
  </ul>
  <div class="py-2">
    <!-- Logout -->
    <button @click="handleLogout()" data-drawer-hide="mobile-navigation-drawer" aria-controls="mobile-navigation-drawer" class="flex flex-row gap-2 items-center w-full py-2 px-4 hover:bg-gray-50">
      <div class="inline-flex gap-3 items-center">
        <svg class="w-10 h-10 p-2.5 bg-red-50 rounded-lg stroke-red-500" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
          <path d="M7.5 17.5H4.16667C3.72464 17.5 3.30072 17.3244 2.98816 17.0118C2.67559 16.6993 2.5 16.2754 2.5 15.8333V4.16667C2.5 3.72464 2.67559 3.30072 2.98816 2.98816C3.30072 2.67559 3.72464 2.5 4.16667 2.5H7.5" stroke-width="1.66667" stroke-linecap="round" stroke-linejoin="round" />
          <path d="M13.332 14.1667L17.4987 10L13.332 5.83337" stroke-width="1.66667" stroke-linecap="round" stroke-linejoin="round" />
          <path d="M17.5 10H7.5" stroke-width="1.66667" stroke-linecap="round" stroke-linejoin="round" />
        </svg>
        <span v-text="$t('navigation.logout')" class="text-sm font-medium text-red-500"></span>
      </div>
    </button>
  </div>
</template>
