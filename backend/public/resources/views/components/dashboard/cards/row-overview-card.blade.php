<div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03] md:p-6">
    <div class="flex h-12 w-12 items-center justify-center rounded-xl {{ $iconBackgroundColor }}">
        <svg
            class="{{ "$iconColor $iconClass" }}"
            width="32"
            height="32"
            viewBox="{{ $viewBox }}"
            fill="none"
            xmlns="http://www.w3.org/2000/svg">
            {{ $slot }}
        </svg>
    </div>

    <div class="mt-5 flex items-end justify-between">
        <div>
            <span class="text-sm text-gray-500 dark:text-gray-400">{{ $title }}</span>
            <h4 class="mt-2 text-title-sm font-bold text-gray-800 dark:text-white/90">{{ $count }}</h4>
        </div>

        @if (isset($authorize) && $authorize && isset($link))
            <a href="{{ $link }}" class="text-brand-500 text-brand-400 hover:underline text-sm">
                {{ __('ui.view_all') }}
            </a>
        @endif
    </div>

</div>
