<script setup>
  defineProps({
    filled: {
      type: Boolean,
      required: true,
    },
  });
</script>

<template>
  <section :class="[filled ? 'bg-brand-500' : 'bg-brand-50']" class="mb-12 md:mb-20 py-12 md:py-20 text-center flex flex-col gap-5 px-4">
    <span v-text="$t('categories.banner.title')" :class="[filled ? 'text-white' : 'text-gray-800']" class="text-xl md:text-2xl font-medium"></span>
    <span v-text="$t('categories.banner.subtitle')" :class="[filled ? 'text-gray-100' : 'text-gray-500']" class="md:text-lg max-w-md mx-auto"></span>
    <div class="flex flex-col md:flex-row gap-5 justify-center mt-8">
      <NuxtLinkLocale :to="{ name: 'index', hash: '#download-app-section' }">
        <ButtonsFilledInvert v-if="filled" class="w-full max-w-xs">
          <!-- majesticons:device-mobile-line -->
          <svg class="w-4.5 h-4.5 mt-1" xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24">
            <!-- Icon from Majesticons by Gerrit Halfmann - https://github.com/halfmage/majesticons/blob/main/LICENSE -->
            <g fill="currentColor"><path d="M8 22a3 3 0 0 1-3-3V5a3 3 0 0 1 3-3h8a3 3 0 0 1 3 3v14a3 3 0 0 1-3 3H8zm-1-3a1 1 0 0 0 1 1h8a1 1 0 0 0 1-1V5a1 1 0 0 0-1-1H8a1 1 0 0 0-1 1v14zm3-1a1 1 0 0 1 1-1h2a1 1 0 1 1 0 2h-2a1 1 0 0 1-1-1z" /></g>
          </svg>
          {{ $t('navigation.download_app') }}
        </ButtonsFilledInvert>
        <ButtonsFilled v-else class="w-full max-w-xs">
          <!-- majesticons:device-mobile-line -->
          <svg class="w-4.5 h-4.5 mt-1" xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24">
            <!-- Icon from Majesticons by Gerrit Halfmann - https://github.com/halfmage/majesticons/blob/main/LICENSE -->
            <g fill="currentColor"><path d="M8 22a3 3 0 0 1-3-3V5a3 3 0 0 1 3-3h8a3 3 0 0 1 3 3v14a3 3 0 0 1-3 3H8zm-1-3a1 1 0 0 0 1 1h8a1 1 0 0 0 1-1V5a1 1 0 0 0-1-1H8a1 1 0 0 0-1 1v14zm3-1a1 1 0 0 1 1-1h2a1 1 0 1 1 0 2h-2a1 1 0 0 1-1-1z" /></g>
          </svg>
          {{ $t('navigation.download_app') }}
        </ButtonsFilled>
      </NuxtLinkLocale>
      <NuxtLinkLocale :to="{ name: 'contact' }">
        <ButtonsOutlineInvert v-if="filled" class="w-full max-w-xs">
          {{ $t('navigation.contact') }}
        </ButtonsOutlineInvert>
        <ButtonsOutline v-else class="w-full max-w-xs">
          {{ $t('navigation.contact') }}
        </ButtonsOutline>
      </NuxtLinkLocale>
    </div>
  </section>
</template>
