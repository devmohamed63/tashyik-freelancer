<div>
    <button {{ $attributes->merge([
        'class' => 'bg-brand-500 disabled:bg-brand-400 disabled:hover:bg-brand-400 dark:disabled:bg-brand-500/70 dark:disabled:hover:bg-brand-500/70 shadow-theme-xs hover:bg-brand-600 flex items-center justify-center gap-2 rounded-lg px-4 py-3 text-sm font-medium text-white transition',
        'type' => 'submit',
    ]) }}
        x-bind:disabled="loading">
        <span x-show="!loading">{{ $name }}</span>
        <span x-show="loading" style="display: none">{{ __('ui.loading') }}</span>
        <!-- gg:spinner -->
        <svg x-show="loading" style="display: none" xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 animate-spin" width="32" height="32" viewBox="0 0 24 24"><!-- Icon from css.gg by Astrit - https://github.com/astrit/css.gg/blob/master/LICENSE -->
            <g fill="currentColor">
                <path fill-rule="evenodd" d="M12 19a7 7 0 1 0 0-14a7 7 0 0 0 0 14m0 3c5.523 0 10-4.477 10-10S17.523 2 12 2S2 6.477 2 12s4.477 10 10 10" clip-rule="evenodd" opacity=".2" />
                <path d="M2 12C2 6.477 6.477 2 12 2v3a7 7 0 0 0-7 7z" />
            </g>
        </svg>
    </button>
</div>
