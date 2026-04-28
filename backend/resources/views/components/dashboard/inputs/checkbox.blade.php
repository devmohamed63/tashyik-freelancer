@props([
    'id' => null,
    'name' => null,
    'title' => null,
    'type' => 'checkbox',
])

@php
    $inputId = $id ?? $name;
@endphp

<div>
    <label for="{{ $inputId }}" class="flex gap-2 cursor-pointer items-center text-sm font-medium text-gray-700 select-none dark:text-gray-400">
        <div class="relative flex items-center">
            <input
                type="{{ $type }}"
                id="{{ $inputId }}"
                @if (filled($name))
                    name="{{ $name }}"
                @endif
                {{ $attributes->merge(['class' => 'w-4 h-4 bg-gray-50 rounded-sm text-brand-500 outline-brand-500 dark:border-gray-700 dark:bg-gray-700']) }}
                style="box-shadow: none !important;">
        </div>
        {{ $title ?? __("validation.attributes.$name") }}
    </label>
</div>
