<x-layouts.dashboard page="articles">

    @session('status')
        <div class="mb-5">
            <x-dashboard.alerts.success :title="$value" />
        </div>
    @endsession

    <div class="relative overflow-hidden rounded-xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03] overflow-x-auto">
        <div class="mb-4 border-b border-gray-200 dark:border-gray-700 px-4">
            <ul class="flex flex-wrap -mb-px text-sm font-medium text-center" id="articles-tab" data-tabs-toggle="#articles-tab-content" role="tablist" data-tabs-active-classes="text-brand-600 hover:text-brand-600 dark:text-brand-400 dark:hover:text-brand-500 border-brand-600 dark:border-brand-400">
                <li class="me-2" role="presentation">
                    <button class="inline-block p-4 border-b-2 rounded-t-lg hover:text-gray-600 hover:border-gray-300 dark:hover:text-gray-300" id="articles-list-tab" data-tabs-target="#articles-list" type="button" role="tab" aria-controls="articles-list" aria-selected="{{ $tab == 'articles' ? 'true' : 'false' }}">{{ __('ui.articles') }}</button>
                </li>
                <li class="me-2" role="presentation">
                    <button class="inline-block p-4 border-b-2 rounded-t-lg hover:text-gray-600 hover:border-gray-300 dark:hover:text-gray-300" id="seo-automation-tab" data-tabs-target="#seo-automation" type="button" role="tab" aria-controls="seo-automation" aria-selected="{{ $tab == 'seo-automation' ? 'true' : 'false' }}">{{ __('ui.seo_automation') }}</button>
                </li>
            </ul>
        </div>

        <div id="articles-tab-content">
            <div class="hidden px-5 pb-5" id="articles-list" role="tabpanel" aria-labelledby="articles-list-tab">
                <div class="flex items-center justify-between mb-4">
                    <x-dashboard.breadcrumb :page="__('ui.articles')" />
                    <span class="bg-brand-50 text-brand-700 text-sm font-medium px-4 py-1.5 rounded-full border border-brand-200">
                        اجمالي المقالات: {{ \App\Models\Article::count() }}
                    </span>
                </div>
                <livewire:dashboard.articles-table />
            </div>

            <div class="hidden px-5 pb-5" id="seo-automation" role="tabpanel" aria-labelledby="seo-automation-tab">
                @include('dashboard.settings.partials.seo-automation')
            </div>
        </div>
    </div>
</x-layouts.dashboard>
