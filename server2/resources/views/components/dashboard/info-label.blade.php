<div>
    <p class="mb-2 text-xs leading-normal text-gray-500 dark:text-gray-400">
        {{ $name }}
    </p>
    @isset($value)
        <p class="text-sm font-medium text-gray-800 dark:text-white/90 {{ $attributes->get('class') }}">
            {{ $value }}
        </p>
    @else
        <div class="{{ $attributes->only(['class']) }}"">
            {{ $slot }}
        </div>
    @endisset
</div>
