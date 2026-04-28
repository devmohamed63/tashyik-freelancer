<div>
    <button {{ $attributes->merge([
        'class' => 'flex items-center justify-center gap-2 rounded-lg bg-white px-4 py-3 text-sm font-medium text-gray-700 shadow-theme-xs ring-1 ring-inset ring-gray-300 transition hover:bg-gray-50 dark:bg-gray-800 dark:text-gray-400 dark:ring-gray-700 dark:hover:bg-white/[0.03]',
        'type' => 'submit',
    ]) }}>
        {{ $name }}
    </button>
</div>
