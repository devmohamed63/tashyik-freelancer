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

        <span
            class="menu-item-text"
            :class="sidebarToggle ? 'lg:hidden' : ''">
            {{ __('ui.' . $name) }}
        </span>
    </a>
</li>
