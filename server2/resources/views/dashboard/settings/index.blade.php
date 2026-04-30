<x-layouts.dashboard page="general_settings">

    <x-dashboard.breadcrumb :page="__('ui.general_settings')" />

    @session('status')
        <div class="mb-5">
            <x-dashboard.alerts.success :title="$value" />
        </div>
    @endsession

    <div class="relative overflow-hidden rounded-xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03] overflow-x-auto">

        <div class="mb-4 border-b border-gray-200 dark:border-gray-700 px-4">
            <ul class="flex flex-wrap -mb-px text-sm font-medium text-center" id="settings-tab" data-tabs-toggle="#settings-tab-content" role="tablist" data-tabs-active-classes="text-brand-600 hover:text-brand-600 dark:text-brand-400 dark:hover:text-brand-500 border-brand-600 dark:border-brand-400">
                <li class="me-2" role="presentation">
                    <button class="inline-block p-4 border-b-2 rounded-t-lg hover:text-gray-600 hover:border-gray-300 dark:hover:text-gray-300" id="basic-information-tab" data-tabs-target="#basic-information" type="button" role="tab" aria-controls="basic-information" aria-selected="{{ $tab == 'basic-information' ? 'true' : 'false' }}">{{ __('ui.basic_information') }}</button>
                </li>
                <li class="me-2" role="presentation">
                    <button class="inline-block p-4 border-b-2 rounded-t-lg hover:text-gray-600 hover:border-gray-300 dark:hover:text-gray-300" id="social-links-tab" data-tabs-target="#social-links" type="button" role="tab" aria-controls="social-links" aria-selected="{{ $tab == 'social-links' ? 'true' : 'false' }}">{{ __('ui.social_links') }}</button>
                </li>
                <li class="me-2" role="presentation">
                    <button class="inline-block p-4 border-b-2 rounded-t-lg hover:text-gray-600 hover:border-gray-300 dark:hover:text-gray-300" id="service-collections-tab" data-tabs-target="#service-collections" type="button" role="tab" aria-controls="service-collections" aria-selected="{{ $tab == 'service-collections' ? 'true' : 'false' }}">{{ __('ui.service_collections') }}</button>
                </li>
                <li class="me-2" role="presentation">
                    <button class="inline-block p-4 border-b-2 rounded-t-lg hover:text-gray-600 hover:border-gray-300 dark:hover:text-gray-300" id="questions-tab" data-tabs-target="#questions" type="button" role="tab" aria-controls="questions" aria-selected="{{ $tab == 'questions' ? 'true' : 'false' }}">{{ __('ui.questions') }}</button>
                </li>
            </ul>
        </div>
        <div id="settings-tab-content">
            <div class="hidden px-5 pb-5" id="basic-information" role="tabpanel" aria-labelledby="basic-information-tab">
                @include('dashboard.settings.partials.basic-information')
            </div>
            <div class="hidden px-5 pb-5" id="social-links" role="tabpanel" aria-labelledby="social-links-tab">
                @include('dashboard.settings.partials.social-links')
            </div>
            <div class="hidden px-5 pb-5" id="service-collections" role="tabpanel" aria-labelledby="service-collections-tab">
                @include('dashboard.settings.partials.service-collections')
            </div>
            <div class="hidden px-5 pb-5" id="questions" role="tabpanel" aria-labelledby="questions-tab">
                @include('dashboard.settings.partials.questions')
            </div>
        </div>
    </div>

    @vite('resources/js/file-input.js')

</x-layouts.dashboard>
