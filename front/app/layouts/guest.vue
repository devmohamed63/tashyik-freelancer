<script setup>
  const head = useLocaleHead();
  const layout = useLayoutStore();

  await layout.load();
</script>

<template>
  <Html :lang="head.htmlAttrs.lang" :dir="head.htmlAttrs.dir" class="scroll-smooth">
    <Body>
      <NuxtLoadingIndicator color="repeating-linear-gradient(to right, #FFEB3B 0%, #FFEB3B 50%, #FF9800 100%)" />
      <div class="min-h-dvh bg-brand-50/60 md:bg-gray-50 flex items-center justify-center md:p-4">
        <div class="w-full max-w-lg md:rounded-3xl md:border md:bg-brand-50/60 border-brand-300 py-10 px-5 md:px-10">
          <NuxtLinkLocale :to="{ name: 'index' }" class="block mx-auto w-fit hover:opacity-80 transition-opacity">
            <AppLogo class="h-11" />
          </NuxtLinkLocale>
          <slot />
        </div>
      </div>
    </Body>
  </Html>
</template>
