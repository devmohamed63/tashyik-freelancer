<div>
    <button {{ $attributes->merge([
        'class' => 'bg-red-500 shadow-theme-xs hover:bg-red-600 flex items-center justify-center gap-2 rounded-lg px-4 py-3 text-sm font-medium text-white transition',
        'type' => 'submit',
    ]) }}>
        {{ $name }}
    </button>
</div>
