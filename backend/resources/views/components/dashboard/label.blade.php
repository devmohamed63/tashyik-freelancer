<div>
    <label {{ $attributes->only(['for']) }} class="mb-1.5 text-sm font-medium text-gray-700 dark:text-gray-400 inline-flex items-center">
        @if ($required)
            {{ isset($locale) ? __('validation.attributes.' . $name) . ' ' . __('ui.' . $locale) : __('validation.attributes.' . $name) }}<span class="text-error-500">*</span>
        @else
            {{ isset($locale) ? __('validation.attributes.' . $name) . ' ' . __('ui.' . $locale) : __('validation.attributes.' . $name) }}
            <x-dashboard.badges.primary :name="__('ui.optional')" />
        @endif
    </label>
</div>
