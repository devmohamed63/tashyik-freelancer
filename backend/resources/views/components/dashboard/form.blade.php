<div>
    <div class="space-y-6">
        <div class="rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]">
            <div class="px-5 py-4 sm:px-6 sm:py-5">
                <h3 class="text-base font-medium text-gray-800 dark:text-white/90">
                    {{ $title }}
                </h3>
            </div>
            <div {{ $attributes->merge(['class' => 'border-t border-gray-100 p-5 sm:p-6 dark:border-gray-800']) }}>
                {{ $slot }}
            </div>
        </div>
    </div>
</div>
