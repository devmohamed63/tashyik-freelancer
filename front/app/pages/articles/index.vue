<script setup>

  const { t, locale } = useI18n();
  const localePath = useLocalePath();

  const { data: articlesData, error, status, refresh } = await useApiFetch('/articles?limit=100', {}, true);

  if (import.meta.client && error.value) {
    await refresh();
  }

  const articles = computed(() => articlesData.value?.data || []);
  const featuredArticles = computed(() => articles.value.filter(a => a.is_featured));
  const regularArticles = computed(() => articles.value.filter(a => !a.is_featured));

  useSeoMeta({
    title: t('articles.seo.title'),
    description: t('articles.seo.description'),
    ogTitle: t('articles.seo.title'),
    ogDescription: t('articles.seo.description'),
    ogType: 'website',
  });
</script>

<template>
  <div class="bg-gray-50 min-h-screen">
    <!-- Hero Section -->
    <section class="relative bg-gradient-to-br from-brand-900 via-brand-800 to-brand-950 text-white py-16 md:py-24 overflow-hidden">
      <div class="absolute inset-0 opacity-10">
        <div class="absolute -top-24 -right-24 w-96 h-96 bg-brand-400 rounded-full blur-3xl"></div>
        <div class="absolute -bottom-24 -left-24 w-96 h-96 bg-brand-300 rounded-full blur-3xl"></div>
      </div>
      <div class="container relative z-10 text-center max-md:px-4">
        <h1 class="text-3xl md:text-5xl font-bold mb-4">{{ $t('articles.hero.title') }}</h1>
        <p class="text-lg md:text-xl text-brand-100 max-w-2xl mx-auto">{{ $t('articles.hero.subtitle') }}</p>
      </div>
    </section>

    <div class="container max-md:px-4 py-12 md:py-16">
      <!-- Loading -->
      <div v-if="status === 'pending' || status === 'error'" class="flex justify-center py-20">
        <AppSpinner />
      </div>

      <!-- Empty State -->
      <div v-else-if="!articles.length" class="text-center py-20">
        <svg class="w-16 h-16 mx-auto text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z" />
        </svg>
        <p class="text-gray-500 text-lg">{{ $t('articles.empty') }}</p>
      </div>

      <template v-else>
        <!-- Articles Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
          <NuxtLinkLocale
            v-for="article in articles"
            :key="article.id"
            :to="{ name: 'articles-slug', params: { slug: article.slug } }"
            class="group block"
            :id="`article-${article.id}`">
            <article class="bg-white rounded-lg shadow-sm hover:shadow-md border border-gray-100 overflow-hidden h-full flex flex-col transition-all duration-300 transform hover:-translate-y-1">
              <!-- Image -->
              <div class="aspect-[16/9] overflow-hidden bg-gray-50">
                <img
                  v-if="article.featured_image"
                  :src="article.featured_image"
                  :alt="article.title"
                  class="w-full h-full object-cover object-center transition-transform duration-500 group-hover:scale-105"
                  loading="lazy" />
                <div v-else class="w-full h-full bg-gradient-to-br from-brand-100 to-brand-200 flex items-center justify-center">
                  <svg class="w-12 h-12 text-brand-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z" />
                  </svg>
                </div>
              </div>

              <!-- Content -->
              <div class="p-5 flex flex-col flex-1 relative">
                <span v-if="article.is_featured" class="absolute -top-3 rtl:left-5 ltr:right-5 inline-flex items-center gap-1 bg-brand-50 text-brand-700 text-xs font-medium px-2 py-1 rounded shadow-sm border border-brand-100 z-10 w-fit">
                  <svg class="w-3 h-3 text-brand-500" fill="currentColor" viewBox="0 0 20 20">
                    <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                  </svg>
                  {{ $t('articles.featured_badge') }}
                </span>
                <h3 class="text-lg font-bold text-gray-900 mb-2 group-hover:text-brand-700 transition-colors line-clamp-2">
                  {{ article.title }}
                </h3>
                <p class="text-gray-500 text-sm leading-relaxed flex-1 line-clamp-3 mb-4">{{ article.excerpt }}</p>
                <div class="flex items-center justify-between text-sm text-gray-400 pt-3 border-t border-gray-50">
                  <time v-if="article.published_at_formatted">{{ article.published_at_formatted }}</time>
                  <span class="text-brand-600 font-medium group-hover:gap-2 inline-flex items-center gap-1 transition-all">
                    {{ $t('articles.read_more') }}
                    <svg class="w-4 h-4 rtl:rotate-180" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3" />
                    </svg>
                  </span>
                </div>
              </div>
            </article>
          </NuxtLinkLocale>
        </div>
      </template>
    </div>

    <!-- Ready to Join Section -->
    <ReadyToJoinSection />
  </div>
</template>
