<li>
    <a
        href="{{ $url }}"
        x-on:click="selected = (selected === '{{ $name }}' ? '' : '{{ $name }}')"
        class="menu-item group"
        x-bind:class="(selected === '{{ $name }}') && (page === '{{ $name }}') ? 'menu-item-active' : 'menu-item-inactive'">
        <svg
            x-bind:class="[(selected === '{{ $name }}') && (page === '{{ $name }}') ? 'menu-item-icon-active' : 'menu-item-icon-inactive', 'w-5 h-6']"
            {{ $attributes->merge(['width' => 32, 'height' => 32, 'viewBox' => '0 0 24 24', 'fill' => 'none']) }}
            xmlns="http://www.w3.org/2000/svg">
            {{ $slot }}
        </svg>

        <div class="flex items-center justify-between w-full" :class="sidebarToggle ? 'lg:hidden' : ''">
            <span class="menu-item-text">
                {{ __('ui.' . $name) }}
            </span>
            @if (isset($badge))
                <span class="inline-flex items-center justify-center px-2 py-0.5 ms-3 text-xs font-medium text-red-500 bg-red-100 rounded-full dark:bg-red-900 dark:text-red-300">
                    {{ $badge }}
                </span>
            @endif
        </div>
    </a>
</li>
