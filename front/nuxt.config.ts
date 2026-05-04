// https://nuxt.com/docs/api/configuration/nuxt-config

import tailwindcss from '@tailwindcss/vite';

const isProd = process.env.NODE_ENV === 'production';

/** Applied to HTML responses; static assets use longer TTL via routeRules below. */
const securityHeaders: Record<string, string> = {
  'X-Frame-Options': 'SAMEORIGIN',
  'X-Content-Type-Options': 'nosniff',
  'Referrer-Policy': 'strict-origin-when-cross-origin',
  'Permissions-Policy': 'camera=(), microphone=(), geolocation=(self), interest-cohort=()',
};

if (isProd) {
  securityHeaders['Strict-Transport-Security'] = 'max-age=31536000; includeSubDomains; preload';
}

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
    // Defer third-party registry scripts (GTM) until the main thread is idle — reduces TBT on mobile PSI.
    defaultScriptOptions: {
      trigger: { idleTimeout: 2500 },
    },
    registry: {
      googleTagManager: true,
      // googleAnalytics omitted intentionally — load GA4 via GTM only to avoid duplicate gtag/gtm.js work.
      // googleMaps omitted — Maps JS API loads only via useScriptGoogleMaps() in AppMap (address flows).
    },
  },

  nitro: {
    routeRules: {
      '/_nuxt/**': {
        headers: {
          'cache-control': 'public, max-age=31536000, immutable',
        },
      },
      '/images/**': {
        headers: {
          'cache-control': 'public, max-age=31536000, immutable',
        },
      },
      '/**': {
        headers: securityHeaders,
      },
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
      keywords: '',
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
