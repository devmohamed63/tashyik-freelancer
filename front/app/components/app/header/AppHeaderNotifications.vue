<script setup>
  const notifications = ref([]);
  const loader = ref(false);
  const currentPage = ref(1);
  const lastPage = ref(1);
  const hasMoreNotifications = computed(() => currentPage.value < lastPage.value);

  async function loadNotifications(refresh = false) {
    if (loader.value) return;

    loader.value = true;

    try {
      const response = await useApiFetch(`/notifications?page=${currentPage.value}`);

      notifications.value = refresh ? response.data : [...notifications.value, ...response.data];

      currentPage.value = response.meta.current_page;
      lastPage.value = response.meta.last_page;
    } catch (error) {
      console.error('Failed to load notifications:', error);
    }

    loader.value = false;
  }

  async function loadMore() {
    currentPage.value++;

    await loadNotifications();
  }
</script>

<template>
  <button @click="loadNotifications(true)" id="notificationDropdownButton" class="relative inline-flex items-center p-2 w-10 h-10 justify-center text-sm text-gray-500 rounded-xl hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-gray-200 dark:text-gray-400 dark:hover:bg-gray-700 dark:focus:ring-gray-600" data-dropdown-toggle="notificationDropdown" data-dropdown-placement="bottom" type="button">
    <svg class="w-5 h-5 stroke-gray-500" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
      <path d="M18 8C18 6.4087 17.3679 4.88258 16.2426 3.75736C15.1174 2.63214 13.5913 2 12 2C10.4087 2 8.88258 2.63214 7.75736 3.75736C6.63214 4.88258 6 6.4087 6 8C6 15 3 17 3 17H21C21 17 18 15 18 8Z" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
      <path d="M13.7295 21C13.5537 21.3031 13.3014 21.5547 12.9978 21.7295C12.6941 21.9044 12.3499 21.9965 11.9995 21.9965C11.6492 21.9965 11.3049 21.9044 11.0013 21.7295C10.6977 21.5547 10.4453 21.3031 10.2695 21" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
    </svg>
    <span class="sr-only">Notifications</span>
  </button>

  <!-- Notifications dropdown menu -->
  <div id="notificationDropdown" class="border-t sm:border border-gray-200 z-10 hidden bg-white sm:rounded-xl shadow w-full max-w-sm dark:bg-gray-700">
    <div class="h-[calc(100dvh-63px)] sm:max-h-[450px] md:max-h-[600px] flex flex-col">
      <div class="bg-white border-b border-gray-200 flex flex-col p-3 sm:rounded-t-xl">
        <span class="font-medium text-lg" v-text="$t('notifications.title')"></span>
      </div>

      <!-- Loader -->
      <div v-if="loader" class="z-10 w-full h-full bg-white/30 absolute top-0 rounded-xl flex items-center justify-center">
        <AppSpinner />
      </div>

      <template v-else-if="notifications.length">
        <!-- Notifications list -->
        <ul class="p-2 overflow-y-auto h-full" aria-labelledby="notificationDropdownButton">
          <li v-for="notification in notifications" :key="notification.id">
            <Notification :notification />
          </li>
        </ul>

        <!-- Load more -->
        <button v-if="hasMoreNotifications && !loader" @click="loadMore()" class="text-center bg-gray-100 w-full hover:text-brand-500 transition-colors mx-auto text-gray-700 p-3 font-medium flex gap-1 items-center justify-center">
          {{ $t('common.load_more') }}
          <!-- material-symbols:arrow-downward-alt -->
          <svg class="w-6 h-6 pt-1" xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24">
            <!-- Icon from Material Symbols by Google - https://github.com/google/material-design-icons/blob/master/LICENSE -->
            <path fill="currentColor" d="m12 18l-6-6l1.4-1.4l3.6 3.6V5h2v9.2l3.6-3.6L18 12z" />
          </svg>
        </button>
      </template>

      <!-- Empty list -->
      <div v-else class="h-full flex flex-col gap-6 items-center justify-center p-12 text-center">
        <!-- ic:sharp-notifications -->
        <svg class="w-16 h-16 text-gray-300" xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24">
          <!-- Icon from Google Material Icons by Material Design Authors - https://github.com/material-icons/material-icons/blob/master/LICENSE -->
          <path fill="currentColor" d="M12 22c1.1 0 2-.9 2-2h-4a2 2 0 0 0 2 2m6-6v-5c0-3.07-1.64-5.64-4.5-6.32V2.5h-3v2.18C7.63 5.36 6 7.92 6 11v5l-2 2v1h16v-1z" />
        </svg>
        <span class="text-lg" v-text="$t('notifications.no_notifications')"></span>
      </div>
    </div>
  </div>
</template>
