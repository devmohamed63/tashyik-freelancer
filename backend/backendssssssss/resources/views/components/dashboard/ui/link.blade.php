<p class="truncate">
    <a
        {{ $attributes->except(['name'])->merge([
            'class' => 'text-brand-500 hover:text-brand-600 dark:text-brand-400 dark:hover:text-brand-300',
        ]) }}>
        {{ $name }}
    </a>
</p>
