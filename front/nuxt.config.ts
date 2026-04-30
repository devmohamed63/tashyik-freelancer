// https://nuxt.com/docs/api/configuration/nuxt-config

import tailwindcss from '@tailwindcss/vite';

export default defineNuxtConfig({
  compatibilityDate: '2025-07-15',
  devtools: { enabled: true },

  css: ['~/assets/css/app.css'],

  vite: {
    plugins: [tailwindcss()],
  },

  modules: ['@nuxtjs/i18n', 'nuxt-swiper', '@pinia/nuxt', '@nuxt/scripts', '@nuxt/fonts'],

  features: {
    inlineStyles: false,
  },

  scripts: {
    registry: {
      googleMaps: true,
      googleTagManager: true,
      googleAnalytics: true,
    },
  },

  fonts: {
    families: [
      {
        name: 'ExpoArabic',
        weights: [300, 400, 500, 700],
        sources: [
          {
            src: 'fonts/Expo-Arabic-Light.ttf',
            weight: 300,
            style: 'normal',
          },
          {
            src: 'fonts/Expo-Arabic-Book.ttf',
            weight: 400,
            style: 'normal',
          },
          {
            src: 'fonts/Expo-Arabic-Medium.ttf',
            weight: 500,
            style: 'normal',
          },
          {
            src: 'fonts/Expo-Arabic-Bold.ttf',
            weight: 700,
            style: 'normal',
          },
        ],
      },
    ],
    preload: true,
    display: 'swap',
  },

  runtimeConfig: {
    public: {
      appUrl: '',
      apiBaseUrl: '',
      salesIqKey: '',
      whatsappLink: '',
      saudiBusinessCenterCertificate: '',
      i18n: {
        baseUrl: 'https://www.tashyik.com',
      },
      scripts: {
        googleMaps: {
          apiKey: '',
        },
        googleTagManager: {
          id: '',
        },
        googleAnalytics: {
          id: '',
        },
      },
    },
  },

  i18n: {
    baseUrl: 'https://www.tashyik.com',
    detectBrowserLanguage: {
      useCookie: true,
    },
    locales: [
      {
        name: 'العربية',
        code: 'ar',
        language: 'ar',
        file: 'ar.ts',
        dir: 'rtl',
      },
      {
        name: 'English',
        code: 'en',
        language: 'en',
        file: 'en.ts',
        dir: 'ltr',
      },
      {
        name: 'हिन्दी',
        code: 'hi',
        language: 'hi',
        file: 'hi.ts',
        dir: 'ltr',
      },
      {
        name: 'বাংলা',
        code: 'bn',
        language: 'bn',
        file: 'bn.ts',
        dir: 'ltr',
      },
      {
        name: 'اردو',
        code: 'ur',
        language: 'ur',
        file: 'ur.ts',
        dir: 'rtl',
      },
      {
        name: 'Tagalog',
        code: 'tl',
        language: 'tl',
        file: 'tl.ts',
        dir: 'ltr',
      },
      {
        name: 'Bahasa Indonesia',
        code: 'id',
        language: 'id',
        file: 'id.ts',
        dir: 'ltr',
      },
      {
        name: 'Français',
        code: 'fr',
        language: 'fr',
        file: 'fr.ts',
        dir: 'ltr',
      },
    ],
    strategy: 'prefix_except_default',
    defaultLocale: 'ar',
    lazy: false,
    langDir: 'locales',
  },
});
