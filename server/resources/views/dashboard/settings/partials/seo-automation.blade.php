<form
    {{-- //comment --}}
    x-data="{ loading: false }"
    x-on:submit="loading = true"
    method="POST"
    action="{{ \Illuminate\Support\Facades\Route::has('dashboard.articles.update_seo_automation') ? route('dashboard.articles.update_seo_automation') : url('/articles/seo-automation') }}"
    class="grid grid-cols-1 md:grid-cols-2 gap-5">

    @csrf

    <div class="col-span-2">
        <input type="hidden" name="ai_blog_automation_enabled" value="0" />
        <label for="ai_blog_automation_enabled" class="flex gap-2 cursor-pointer items-center text-sm font-medium text-gray-700 select-none dark:text-gray-400">
            <div class="relative flex items-center">
                <input
                    type="checkbox"
                    id="ai_blog_automation_enabled"
                    name="ai_blog_automation_enabled"
                    value="1"
                    @checked(old('ai_blog_automation_enabled', (bool) $settings->ai_blog_automation_enabled))
                    class="w-4 h-4 bg-gray-50 rounded-sm text-brand-500 outline-brand-500 dark:border-gray-700 dark:bg-gray-700"
                    style="box-shadow: none !important;">
            </div>
            {{ __('ui.ai_blog_automation_enabled') }}
        </label>
    </div>

    <div>
        <label for="ai_blog_daily_limit" class="mb-1.5 text-sm font-medium text-gray-700 dark:text-gray-400 inline-flex items-center">
            {{ __('ui.ai_blog_daily_limit') }}<span class="text-error-500">*</span>
        </label>
        <input
            type="number"
            name="ai_blog_daily_limit"
            id="ai_blog_daily_limit"
            min="1"
            max="100"
            required
            value="{{ old('ai_blog_daily_limit', $settings->ai_blog_daily_limit ?? 1) }}"
            class="dark:bg-dark-900 shadow-theme-xs focus:border-brand-300 focus:ring-brand-500/10 dark:focus:border-brand-800 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 placeholder:text-gray-400 focus:ring-3 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 @error('ai_blog_daily_limit') input-error @enderror" />
        @error('ai_blog_daily_limit')
            <x-dashboard.inputs.error :message="$message" />
        @enderror
    </div>

    <div>
        <label for="ai_blog_monthly_limit" class="mb-1.5 text-sm font-medium text-gray-700 dark:text-gray-400 inline-flex items-center">
            {{ __('ui.ai_blog_monthly_limit') }}<span class="text-error-500">*</span>
        </label>
        <input
            type="number"
            name="ai_blog_monthly_limit"
            id="ai_blog_monthly_limit"
            min="1"
            max="1000"
            required
            value="{{ old('ai_blog_monthly_limit', $settings->ai_blog_monthly_limit ?? 20) }}"
            class="dark:bg-dark-900 shadow-theme-xs focus:border-brand-300 focus:ring-brand-500/10 dark:focus:border-brand-800 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 placeholder:text-gray-400 focus:ring-3 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 @error('ai_blog_monthly_limit') input-error @enderror" />
        @error('ai_blog_monthly_limit')
            <x-dashboard.inputs.error :message="$message" />
        @enderror
    </div>

    <div class="col-span-2">
        <label for="ai_blog_prompt" class="mb-1.5 text-sm font-medium text-gray-700 dark:text-gray-400 inline-flex items-center">
            {{ __('ui.ai_blog_prompt') }}
        </label>
        <textarea
            name="ai_blog_prompt"
            id="ai_blog_prompt"
            rows="5"
            class="dark:bg-dark-900 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 dark:focus:border-brand-800">{{ old('ai_blog_prompt', $settings->ai_blog_prompt) }}</textarea>
        @error('ai_blog_prompt')
            <x-dashboard.inputs.error :message="$message" />
        @enderror
    </div>

    <div class="col-span-2 text-sm text-gray-500 dark:text-gray-400">
        {{ __('ui.ai_blog_prompt_append_hint') }}
    </div>

    <x-dashboard.buttons.primary :name="__('ui.update_automation')" />
</form>
