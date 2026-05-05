<x-layouts.dashboard page="edit_article">

    <x-dashboard.breadcrumb :page="__('ui.edit_article')">
        @can('viewAny', App\Models\Article::class)
            <x-dashboard.breadcrumb-back
                :url="route('dashboard.articles.index')"
                :name="__('ui.view_articles')" />
        @endcan
    </x-dashboard.breadcrumb>

    @session('status')
        <div class="mb-5">
            <x-dashboard.alerts.success :title="$value" />
        </div>
    @endsession

    <div class="relative overflow-hidden rounded-xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03] overflow-x-auto">
        <form
            x-data="{ loading: false }"
            x-on:submit="loading = true"
            method="POST"
            action="{{ route('dashboard.articles.update', ['article' => $article->id]) }}"
            enctype="multipart/form-data"
            class="p-4 grid grid-cols-1 md:grid-cols-2 gap-5">

            @method('PUT')
            @csrf

            <!-- Featured Image -->
            <div class="col-span-2">
                <x-dashboard.inputs.file.single-image
                    class="w-full"
                    preview-class="w-full h-36 sm:h-64 aspect-video"
                    id="featured_image"
                    name="featured_image"
                    accept=".webp, .png, .jpg, .jpeg"
                    :preview="$image"
                    :required="false" />
            </div>

            <!-- Title ar -->
            <x-dashboard.inputs.default
                name="title"
                locale="ar"
                :value="old('title.ar', $article->getTranslation('title', 'ar'))"
                id="article-title-ar"
                :required="true" />

            <!-- Title en -->
            <x-dashboard.inputs.default
                name="title"
                locale="en"
                :value="old('title.en', $article->getTranslation('title', 'en'))"
                id="article-title-en"
                :required="true" />

            <!-- Slug -->
            <x-dashboard.inputs.default
                name="slug"
                id="article-slug"
                :value="old('slug', $article->slug)"
                :required="false" />

            <!-- Published At -->
            <div>
                <x-dashboard.label name="published_at" :required="false" />
                <input
                    type="datetime-local"
                    name="published_at"
                    id="article-published-at"
                    value="{{ old('published_at', $article->published_at?->format('Y-m-d\TH:i')) }}"
                    class="dark:bg-dark-900 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 dark:focus:border-brand-800" />
                @error('published_at')
                    <x-dashboard.inputs.error :message="$message" />
                @enderror
            </div>

            <!-- Linked service (optional) -->
            <div class="md:col-span-2">
                <label for="article-service-id" class="mb-1.5 text-sm font-medium text-gray-700 dark:text-gray-400 inline-flex items-center">
                    {{ __('ui.article_linked_service') }}
                </label>
                <select
                    name="service_id"
                    id="article-service-id"
                    class="dark:bg-dark-900 shadow-theme-xs focus:border-brand-300 focus:ring-brand-500/10 dark:focus:border-brand-800 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 focus:ring-3 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:focus:border-brand-800">
                    <option value="">{{ __('ui.none') }}</option>
                    @foreach ($services as $svc)
                        <option value="{{ $svc->id }}" @selected(old('service_id', $article->service_id) == $svc->id)>
                            {{ $svc->getTranslation('name', 'ar') }} — {{ $svc->getTranslation('name', 'en') }}
                        </option>
                    @endforeach
                </select>
                @error('service_id')
                    <x-dashboard.inputs.error :message="$message" />
                @enderror
                <p class="mt-1.5 text-xs text-gray-500 dark:text-gray-400">{{ __('ui.article_linked_service_hint') }}</p>
            </div>

            <!-- Excerpt ar -->
            <div>
                <x-dashboard.label name="excerpt" locale="ar" :required="true" />
                <textarea
                    name="excerpt[ar]"
                    id="article-excerpt-ar"
                    rows="3"
                    class="dark:bg-dark-900 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 dark:focus:border-brand-800"
                    required>{{ old('excerpt.ar', $article->getTranslation('excerpt', 'ar')) }}</textarea>
                @error('excerpt.ar')
                    <x-dashboard.inputs.error :message="$message" />
                @enderror
            </div>

            <!-- Excerpt en -->
            <div>
                <x-dashboard.label name="excerpt" locale="en" :required="true" />
                <textarea
                    name="excerpt[en]"
                    id="article-excerpt-en"
                    rows="3"
                    class="dark:bg-dark-900 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 dark:focus:border-brand-800"
                    required>{{ old('excerpt.en', $article->getTranslation('excerpt', 'en')) }}</textarea>
                @error('excerpt.en')
                    <x-dashboard.inputs.error :message="$message" />
                @enderror
            </div>

            <!-- Body ar -->
            <div>
                <x-dashboard.label name="body" locale="ar" :required="true" />
                <textarea name="body[ar]" class="ck-editor">{!! old('body.ar', $article->getTranslation('body', 'ar')) !!}</textarea>
                @error('body.ar')
                    <x-dashboard.inputs.error :message="$message" />
                @enderror
            </div>

            <!-- Body en -->
            <div>
                <x-dashboard.label name="body" locale="en" :required="true" />
                <textarea name="body[en]" class="ck-editor" locale="en">{!! old('body.en', $article->getTranslation('body', 'en')) !!}</textarea>
                @error('body.en')
                    <x-dashboard.inputs.error :message="$message" />
                @enderror
            </div>

            <!-- SEO Section -->
            <div class="col-span-2 border-t border-gray-200 dark:border-gray-700 pt-4 mt-2">
                <h3 class="text-lg font-medium text-gray-800 dark:text-white/90 mb-4">{{ __('ui.seo_settings') }}</h3>
            </div>

            <!-- Meta Title ar -->
            <x-dashboard.inputs.default
                name="meta_title"
                locale="ar"
                :value="old('meta_title.ar', $article->getTranslation('meta_title', 'ar', false))"
                id="article-meta-title-ar"
                :required="false" />

            <!-- Meta Title en -->
            <x-dashboard.inputs.default
                name="meta_title"
                locale="en"
                :value="old('meta_title.en', $article->getTranslation('meta_title', 'en', false))"
                id="article-meta-title-en"
                :required="false" />

            <!-- Meta Description ar -->
            <div>
                <x-dashboard.label name="meta_description" locale="ar" :required="false" />
                <textarea
                    name="meta_description[ar]"
                    id="article-meta-description-ar"
                    rows="2"
                    class="dark:bg-dark-900 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 dark:focus:border-brand-800">{{ old('meta_description.ar', $article->getTranslation('meta_description', 'ar', false)) }}</textarea>
                @error('meta_description.ar')
                    <x-dashboard.inputs.error :message="$message" />
                @enderror
            </div>

            <!-- Meta Description en -->
            <div>
                <x-dashboard.label name="meta_description" locale="en" :required="false" />
                <textarea
                    name="meta_description[en]"
                    id="article-meta-description-en"
                    rows="2"
                    class="dark:bg-dark-900 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 dark:focus:border-brand-800">{{ old('meta_description.en', $article->getTranslation('meta_description', 'en', false)) }}</textarea>
                @error('meta_description.en')
                    <x-dashboard.inputs.error :message="$message" />
                @enderror
            </div>

            <!-- Status & Featured -->
            <div class="w-fit col-span-2 flex flex-row gap-6">
                <x-dashboard.inputs.checkbox
                    name="status"
                    :title="__('ui.active')"
                    :checked="old('status', $article->isActive())" />

                <x-dashboard.inputs.checkbox
                    name="is_featured"
                    :title="__('ui.featured')"
                    :checked="old('is_featured', $article->is_featured)" />
            </div>

            <!-- Submit button -->
            <x-dashboard.buttons.primary :name="__('ui.update')" />

        </form>
    </div>

    @vite(['resources/css/ckeditor.css', 'resources/js/ckeditor.js', 'resources/js/file-input.js'])

</x-layouts.dashboard>
