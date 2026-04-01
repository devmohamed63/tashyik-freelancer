<script setup>
  const auth = useAuthStore();
  const walletBalance = ref(null);

  onMounted(async () => {
    useFlowbite(() => {
      initDropdowns();
      initDrawers();
    });

    if (auth.isLoggedIn) {
      try {
        const response = await useApiFetch('/wallet/balance');

        walletBalance.value = `${response.data.formated} ${response.data.currency}`;
      } catch (error) {
        console.error('Failed to load wallet balance:', error);
      }
    }
  });
</script>

<template>
  <nav class="bg-white border-gray-200 dark:bg-gray-900 text-gray-600 shadow-sm sticky top-0 z-30 w-full">
    <div class="max-w-screen-xl flex flex-row items-center justify-between mx-auto p-3 xl:py-5 xl:px-0">
      <NuxtLinkLocale :to="{ name: 'index' }">
        <AppLogo class="h-[38px]" />
      </NuxtLinkLocale>
      <div class="hidden w-full lg:block lg:w-auto" id="desktop-navbar">
        <ul class="flex flex-row space-x-8">
          <li>
            <NuxtLinkLocale :to="{ name: 'index' }">
              {{ $t('navigation.home') }}
            </NuxtLinkLocale>
          </li>
          <li>
            <NuxtLinkLocale :to="{ name: 'about' }">
              {{ $t('navigation.about') }}
            </NuxtLinkLocale>
          </li>
          <li>
            <NuxtLinkLocale :to="{ name: 'categories' }">
              {{ $t('navigation.categories') }}
            </NuxtLinkLocale>
          </li>
          <li>
            <NuxtLinkLocale :to="{ name: 'contact' }">
              {{ $t('navigation.contact') }}
            </NuxtLinkLocale>
          </li>
        </ul>
      </div>
      <div class="flex flex-row md:gap-3 items-center ms-auto lg:mx-0">
        <div v-if="auth.isLoggedIn" class="ms-auto flex items-center justify-center gap-2">
          <button id="dropdownProfileButton" data-dropdown-toggle="dropdownProfile" class="hidden md:flex items-center text-sm p-1 px-3 rounded-lg hover:bg-gray-100 md:me-0 focus:ring-4 focus:ring-gray-100" type="button">
            <span class="sr-only">Open user menu</span>
            <img class="w-7 h-7 me-2 rounded-full object-center object-cover" :src="auth.user.picture" alt="user photo" />
            {{ auth.user.name }}
            <svg class="w-2.5 h-2.5 ms-3 text-gray-500" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 10 6">
              <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m1 1 4 4 4-4" />
            </svg>
          </button>

          <!-- Profile dropdown menu -->
          <div id="dropdownProfile" class="z-10 hidden bg-white rounded-xl shadow-lg w-72 border border-gray-100 divide-y divide-gray-100">
            <div class="flex flex-col gap-1 p-5">
              <span v-text="auth.user.name" class="font-medium truncate text-gray-800"></span>
              <div class="inline-flex gap-1">
                <span v-text="`${$t('common.wallet_balance')}: `" class="text-sm truncate text-gray-400"></span>
                <span v-text="walletBalance" class="text-sm truncate text-gray-400"></span>
              </div>
            </div>
            <AppHeaderProfileDropdown />
          </div>
        </div>
        <div v-else class="hidden lg:inline-flex gap-4">
          <ButtonsFilled>
            <NuxtLinkLocale :to="{ name: 'index', hash: '#download-app-section' }" class="inline-flex gap-2 static-color">
              <!-- majesticons:device-mobile-line -->
              <svg class="w-4.5 h-4.5 mt-1" xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24">
                <!-- Icon from Majesticons by Gerrit Halfmann - https://github.com/halfmage/majesticons/blob/main/LICENSE -->
                <g fill="currentColor"><path d="M8 22a3 3 0 0 1-3-3V5a3 3 0 0 1 3-3h8a3 3 0 0 1 3 3v14a3 3 0 0 1-3 3H8zm-1-3a1 1 0 0 0 1 1h8a1 1 0 0 0 1-1V5a1 1 0 0 0-1-1H8a1 1 0 0 0-1 1v14zm3-1a1 1 0 0 1 1-1h2a1 1 0 1 1 0 2h-2a1 1 0 0 1-1-1z" /></g>
              </svg>
              {{ $t('navigation.download_app') }}
            </NuxtLinkLocale>
          </ButtonsFilled>
          <ButtonsOutline>
            <NuxtLinkLocale :to="{ name: 'login' }" class="static-color">
              {{ $t('navigation.login') }}
            </NuxtLinkLocale>
          </ButtonsOutline>
        </div>
        <AppHeaderLanguage />
        <AppHeaderNotifications v-if="auth.isLoggedIn" />
      </div>
      <button data-drawer-target="mobile-navigation-drawer" data-drawer-show="mobile-navigation-drawer" aria-controls="mobile-navigation-drawer" type="button" class="inline-flex items-center p-2 w-10 h-10 justify-center text-sm text-gray-500 rounded-lg lg:hidden hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-gray-200 dark:text-gray-400 dark:hover:bg-gray-700 dark:focus:ring-gray-600">
        <span class="sr-only">Open main menu</span>
        <svg class="w-5 h-5" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 17 14">
          <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M1 1h15M1 7h15M1 13h15" />
        </svg>
      </button>
    </div>
  </nav>

  <ClientOnly>
    <!-- Mobile navigation drawer -->
    <div id="mobile-navigation-drawer" aria-labelledby="navigation drawer" class="text-gray-600 fixed top-0 left-0 z-40 h-screen overflow-y-auto transition-transform -translate-x-full bg-white w-full max-w-md dark:bg-gray-800" tabindex="-1">
      <div class="flex flex-row justify-between items-center p-3">
        <NuxtLinkLocale :to="{ name: 'index' }" tabindex="-1">
          <AppLogo class="h-[38px]" />
        </NuxtLinkLocale>
        <button tabindex="-1" type="button" data-drawer-hide="mobile-navigation-drawer" aria-controls="mobile-navigation-drawer" class="inline-flex items-center p-2 w-10 h-10 justify-center text-sm text-gray-500 rounded-lg md:hidden hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-gray-200 dark:text-gray-400 dark:hover:bg-gray-700 dark:focus:ring-gray-600">
          <svg class="w-4 h-4" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 14 14">
            <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m1 1 6 6m0 0 6 6M7 7l6-6M7 7l-6 6" />
          </svg>
          <span class="sr-only">Close menu</span>
        </button>
      </div>
      <hr class="w-full border-t border-gray-200" />
      <div class="p-4 flex flex-col gap-5">
        <div class="flex flex-col gap-1 text-gray-500 font-normal">
          <NuxtLinkLocale :to="{ name: 'index' }" tabindex="-1" data-drawer-hide="mobile-navigation-drawer" aria-controls="mobile-navigation-drawer" class="p-3 w-full rounded-lg hover:bg-gray-50 flex flex-row gap-3 items-center justify-start" activeClass="bg-gray-50">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path>
            </svg>
            <span>{{ $t('navigation.home') }}</span>
          </NuxtLinkLocale>
          <NuxtLinkLocale :to="{ name: 'about' }" tabindex="-1" data-drawer-hide="mobile-navigation-drawer" aria-controls="mobile-navigation-drawer" class="p-3 w-full rounded-lg hover:bg-gray-50 flex flex-row gap-3 items-center justify-start" activeClass="bg-gray-50">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
            <span>{{ $t('navigation.about') }}</span>
          </NuxtLinkLocale>
          <NuxtLinkLocale :to="{ name: 'categories' }" tabindex="-1" data-drawer-hide="mobile-navigation-drawer" aria-controls="mobile-navigation-drawer" class="p-3 w-full rounded-lg hover:bg-gray-50 flex flex-row gap-3 items-center justify-start" activeClass="bg-gray-50">
            <!-- mdi:tag-multiple-outline -->
            <svg class="w-5 h-5" xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24">
              <!-- Icon from Material Design Icons by Pictogrammers - https://github.com/Templarian/MaterialDesign/blob/master/LICENSE -->
              <path fill="currentColor" d="M6.5 10C7.3 10 8 9.3 8 8.5S7.3 7 6.5 7S5 7.7 5 8.5S5.7 10 6.5 10M9 6l7 7l-5 5l-7-7V6zm0-2H4c-1.1 0-2 .9-2 2v5c0 .6.2 1.1.6 1.4l7 7c.3.4.8.6 1.4.6s1.1-.2 1.4-.6l5-5c.4-.4.6-.9.6-1.4c0-.6-.2-1.1-.6-1.4l-7-7C10.1 4.2 9.6 4 9 4m4.5 1.7l1-1l6.9 6.9c.4.4.6.9.6 1.4s-.2 1.1-.6 1.4L16 19.8l-1-1l5.7-5.8z" />
            </svg>
            <span>{{ $t('navigation.categories') }}</span>
          </NuxtLinkLocale>
          <NuxtLinkLocale :to="{ name: 'contact' }" tabindex="-1" data-drawer-hide="mobile-navigation-drawer" aria-controls="mobile-navigation-drawer" class="p-3 w-full rounded-lg hover:bg-gray-50 flex flex-row gap-3 items-center justify-start" activeClass="bg-gray-50">
            <!-- humbleicons:mail -->
            <svg class="w-5 h-5" xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24">
              <!-- Icon from Humbleicons by Jiří Zralý - https://github.com/zraly/humbleicons/blob/master/license -->
              <path fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 7v10a1 1 0 0 1-1 1H4a1 1 0 0 1-1-1V7m18 0a1 1 0 0 0-1-1H4a1 1 0 0 0-1 1m18 0l-7.72 6.433a2 2 0 0 1-2.56 0L3 7" />
            </svg>
            <span>{{ $t('navigation.contact') }}</span>
          </NuxtLinkLocale>
        </div>
        <div v-if="auth.isLoggedIn" class="flex flex-col divide-y divide-gray-100 border-t border-gray-100">
          <div class="flex flex-col gap-2 p-2">
            <div class="inline-flex gap-2 items-center">
              <img class="w-9 h-9 rounded-full" :src="auth.user.picture" alt="user photo" />
              <div class="flex flex-col gap-1 p-2">
                <span v-text="auth.user.name" class="truncate text-gray-700"></span>
                <div class="inline-flex gap-1">
                  <span v-text="`${$t('common.wallet_balance')}: `" class="text-sm truncate text-gray-400"></span>
                  <span v-text="walletBalance" class="text-sm truncate text-gray-400"></span>
                </div>
              </div>
            </div>
          </div>
          <AppHeaderProfileDropdown />
        </div>
        <template v-else>
          <NuxtLinkLocale :to="{ name: 'login' }" tabindex="-1" data-drawer-hide="mobile-navigation-drawer" aria-controls="mobile-navigation-drawer">
            <ButtonsOutline class="w-full">
              {{ $t('navigation.login') }}
            </ButtonsOutline>
          </NuxtLinkLocale>
          <NuxtLinkLocale :to="{ name: 'index', hash: '#download-app-section' }" data-drawer-hide="mobile-navigation-drawer" aria-controls="mobile-navigation-drawer">
            <ButtonsFilled class="w-full">
              <!-- majesticons:device-mobile-line -->
              <svg class="w-4.5 h-4.5 mt-1" xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24">
                <!-- Icon from Majesticons by Gerrit Halfmann - https://github.com/halfmage/majesticons/blob/main/LICENSE -->
                <g fill="currentColor"><path d="M8 22a3 3 0 0 1-3-3V5a3 3 0 0 1 3-3h8a3 3 0 0 1 3 3v14a3 3 0 0 1-3 3H8zm-1-3a1 1 0 0 0 1 1h8a1 1 0 0 0 1-1V5a1 1 0 0 0-1-1H8a1 1 0 0 0-1 1v14zm3-1a1 1 0 0 1 1-1h2a1 1 0 1 1 0 2h-2a1 1 0 0 1-1-1z" /></g>
              </svg>
              {{ $t('navigation.download_app') }}
            </ButtonsFilled>
          </NuxtLinkLocale>
        </template>
      </div>
    </div>
  </ClientOnly>
</template>
