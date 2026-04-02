<script setup>
  const route = useRoute();
  const { t, locale } = useI18n();
  const config = useRuntimeConfig();
  const switchLocalePath = useSwitchLocalePath();

  const slug = route.params.slug;

  const { data: articleData, error, refresh } = await useApiFetch(`/articles/${slug}`, {}, true);

  // If SSR failed because of network (not a 404 from the backend), try fetching again on client
  if (import.meta.client && error.value && error.value?.statusCode !== 404) {
    await refresh();
  }

  if (error.value && error.value.statusCode === 404) {
    throw createError({ statusCode: 404, statusMessage: 'Article not found' });
  }

  const article = computed(() => articleData.value?.data || articleData.value);

  // SEO Meta
  const seoTitle = computed(() => article.value?.meta_title || article.value?.title);
  const seoDescription = computed(() => article.value?.meta_description || article.value?.excerpt);
  const ogImage = computed(() => article.value?.og_image || article.value?.featured_image_lg || article.value?.featured_image);
  const canonicalUrl = computed(() => `${config.public.appUrl}${switchLocalePath(locale.value)}`);

  useSeoMeta({
    title: seoTitle.value,
    description: seoDescription.value,
    ogTitle: seoTitle.value,
    ogDescription: seoDescription.value,
    ogImage: ogImage.value,
    ogType: 'article',
    ogUrl: canonicalUrl.value,
    twitterCard: 'summary_large_image',
    twitterTitle: seoTitle.value,
    twitterDescription: seoDescription.value,
    twitterImage: ogImage.value,
  });

  // JSON-LD Structured Data
  useHead({
    script: [
      {
        type: 'application/ld+json',
        innerHTML: JSON.stringify({
          '@context': 'https://schema.org',
          '@type': 'Article',
          headline: article.value?.title,
          description: article.value?.excerpt,
          image: ogImage.value,
          datePublished: article.value?.published_at,
          url: canonicalUrl.value,
          publisher: {
            '@type': 'Organization',
            name: t('common.brand'),
          },
        }),
      },
    ],
  });

  // Share URLs
  const shareUrl = computed(() => canonicalUrl.value);
  const shareTitle = computed(() => seoTitle.value);

  const twitterShareUrl = computed(() => `https://twitter.com/intent/tweet?text=${encodeURIComponent(shareTitle.value)}&url=${encodeURIComponent(shareUrl.value)}`);
  const facebookShareUrl = computed(() => `https://www.facebook.com/sharer/sharer.php?u=${encodeURIComponent(shareUrl.value)}`);
  const whatsappShareUrl = computed(() => `https://wa.me/?text=${encodeURIComponent(shareTitle.value + '\n\n' + shareUrl.value)}`);
</script>

<template>
  <div>
    <div class="bg-gray-50 min-h-screen w-full flex flex-col" v-if="article">
      <!-- Article Header -->
      <section class="relative bg-gradient-to-br from-brand-900 via-brand-800 to-brand-950 text-white pt-12 pb-12 md:pt-20 md:pb-16 overflow-hidden">
        <div class="absolute inset-0 opacity-10">
          <div class="absolute -top-24 -right-24 w-96 h-96 bg-brand-400 rounded-full blur-3xl"></div>
          <div class="absolute -bottom-24 -left-24 w-96 h-96 bg-brand-300 rounded-full blur-3xl"></div>
        </div>
        <div class="container relative z-10 max-w-4xl mx-auto px-4">
          <!-- Breadcrumb -->
          <nav class="flex items-center gap-2 text-sm text-brand-200 mb-6">
            <NuxtLinkLocale :to="{ name: 'index' }" class="hover:text-white transition-colors static-color">{{ $t('navigation.home') }}</NuxtLinkLocale>
            <svg class="w-4 h-4 rtl:rotate-180" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
            </svg>
            <NuxtLinkLocale to="/articles" class="hover:text-white transition-colors static-color">{{ $t('navigation.articles') }}</NuxtLinkLocale>
            <svg class="w-4 h-4 rtl:rotate-180" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
            </svg>
            <span class="text-white/70 line-clamp-1">{{ article.title }}</span>
          </nav>

          <h1 class="text-2xl md:text-3xl lg:text-4xl font-bold leading-tight">{{ article.title }}</h1>

          <div class="flex items-center gap-4 mt-6 text-brand-200 text-sm">
            <time v-if="article.published_at_formatted" class="inline-flex items-center gap-1.5">
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
              </svg>
              {{ article.published_at_formatted }}
            </time>
          </div>
        </div>
      </section>

      <!-- Featured Image -->
      <div v-if="article.featured_image_lg" class="w-full max-w-4xl mx-auto px-4 mt-8 relative z-10 mb-8 md:mb-12">
        <div class="rounded-xl overflow-hidden shadow-xl bg-white w-full">
          <img
            :src="article.featured_image_lg"
            :alt="article.title"
            class="w-full aspect-[16/9] object-cover object-center"
            loading="eager" />
        </div>
      </div>

      <!-- Article Content -->
      <div class="w-full max-w-4xl mx-auto px-4 pb-16 md:pb-24">
        <div class="w-full">
          <!-- Article Body -->
          <article class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 md:p-10 lg:p-14 mb-8">
            <div class="prose prose-lg max-w-none prose-headings:text-gray-900 prose-p:text-gray-600 prose-p:leading-relaxed prose-a:text-brand-600 prose-a:no-underline hover:prose-a:underline prose-img:rounded-xl prose-strong:text-gray-800 ck-content" v-html="article.body"></div>
          </article>

          <!-- Share Section -->
          <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 md:p-8">
            <h3 class="text-lg font-bold text-gray-900 mb-4">{{ $t('articles.share') }}</h3>
            <div class="flex items-center gap-3">
              <!-- Twitter/X -->
              <a :href="twitterShareUrl" target="_blank" rel="nofollow noopener" class="flex items-center justify-center w-10 h-10 bg-gray-900 text-white rounded-lg hover:bg-gray-700 transition-colors" aria-label="Share on X">
                <svg class="w-4 h-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 16 16">
                  <path fill="currentColor" d="M12.6.75h2.454l-5.36 6.142L16 15.25h-4.937l-3.867-5.07l-4.425 5.07H.316l5.733-6.57L0 .75h5.063l3.495 4.633L12.601.75Zm-.86 13.028h1.36L4.323 2.145H2.865z" />
                </svg>
              </a>

              <!-- Facebook -->
              <a :href="facebookShareUrl" target="_blank" rel="nofollow noopener" class="flex items-center justify-center w-10 h-10 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors" aria-label="Share on Facebook">
                <svg class="w-5 h-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">
                  <path fill="currentColor" d="M14.2 2.875A4.625 4.625 0 0 0 9.575 7.5v2.575H7.1c-.124 0-.225.1-.225.225v3.4c0 .124.1.225.225.225h2.475V20.9c0 .124.1.225.225.225h3.4c.124 0 .225-.1.225-.225v-6.975h2.497c.103 0 .193-.07.218-.17l.85-3.4a.225.225 0 0 0-.218-.28h-3.347V7.5a.775.775 0 0 1 .775-.775h2.6c.124 0 .225-.1.225-.225V3.1c0-.124-.1-.225-.225-.225z" />
                </svg>
              </a>

              <!-- WhatsApp -->
              <a :href="whatsappShareUrl" target="_blank" rel="nofollow noopener" class="flex items-center justify-center w-10 h-10 bg-green-500 text-white rounded-lg hover:bg-green-600 transition-colors" aria-label="Share on WhatsApp">
                <svg class="w-5 h-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">
                  <path fill="currentColor" d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967c-.273-.099-.471-.148-.67.15c-.197.297-.767.966-.94 1.164c-.173.199-.347.223-.644.075c-.297-.15-1.255-.463-2.39-1.475c-.883-.788-1.48-1.761-1.653-2.059c-.173-.297-.018-.458.13-.606c.134-.133.298-.347.446-.52c.149-.174.198-.298.298-.497c.099-.198.05-.371-.025-.52c-.075-.149-.669-1.612-.916-2.207c-.242-.579-.487-.5-.669-.51a12 12 0 0 0-.57-.01c-.198 0-.52.074-.792.372c-.272.297-1.04 1.016-1.04 2.479c0 1.462 1.065 2.875 1.213 3.074c.149.198 2.096 3.2 5.077 4.487c.709.306 1.262.489 1.694.625c.712.227 1.36.195 1.871.118c.571-.085 1.758-.719 2.006-1.413c.248-.694.248-1.289.173-1.413c-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 0 1-5.031-1.378l-.361-.214l-3.741.982l.998-3.648l-.235-.374a9.86 9.86 0 0 1-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884c2.64 0 5.122 1.03 6.988 2.898a9.82 9.82 0 0 1 2.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.82 11.82 0 0 0 12.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.88 11.88 0 0 0 5.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.82 11.82 0 0 0-3.48-8.413Z" />
                </svg>
              </a>
            </div>
          </div>
        </div>
      </div>

      <!-- Ready to Join Section -->
      <ReadyToJoinSection />
    </div>

    <ClientOnly>
      <div v-if="!article" class="bg-gray-50 min-h-screen w-full flex items-center justify-center py-20">
        <AppSpinner />
      </div>
    </ClientOnly>
  </div>
</template>
