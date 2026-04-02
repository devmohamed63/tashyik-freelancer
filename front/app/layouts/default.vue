<script setup>
  const head = useLocaleHead();
  const { load: loadLayout } = useLayoutStore();
  const switchLocalePath = useSwitchLocalePath();
  const { locale, locales } = useI18n();
  const config = useRuntimeConfig();
  const alternateLocales = ref();

  await loadLayout();

  useSeoMeta({
    ogImage: 'https://tashyik.com/images/og.webp',
    ogImageType: 'image/webp',
    ogImageWidth: '1200',
    ogImageHeight: '630',
  });

  alternateLocales.value = locales.value.map(function (language) {
    return { rel: 'alternate', hreflang: language.code, href: `${config.public.appUrl}${switchLocalePath(language.code)}` };
  });

  useHead({
    link: [{ rel: 'canonical', href: `${config.public.appUrl}${switchLocalePath(locale.value)}` }, ...alternateLocales.value, { rel: 'alternate', hreflang: 'x-default', href: `${config.public.appUrl}${switchLocalePath('ar')}` }],
  });
</script>

<template>
  <Html :lang="head.htmlAttrs.lang || 'ar'" :dir="head.htmlAttrs.dir || 'rtl'" class="scroll-smooth">
    <Body>
      <AppHeader />

      <NuxtLoadingIndicator color="repeating-linear-gradient(to right, #FFEB3B 0%, #FFEB3B 50%, #FF9800 100%)" />

      <main class="min-h-[calc(100vh-13rem)] bg-gray-50 flex flex-col overflow-x-hidden w-full max-w-screen-2xl mx-auto">
        <slot />
      </main>

      <ClientOnly>
        <!-- Whatsapp button -->
        <WhatsappButton />

        <!-- Zoho SalesIQ -->
        <ZohoSalesIQ />
      </ClientOnly>

      <AppFooter />
    </Body>
  </Html>
</template>
