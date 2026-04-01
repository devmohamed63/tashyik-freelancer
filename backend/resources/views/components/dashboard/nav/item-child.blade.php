<li>
    <a href="{{ $url }}" x-bind:class="[page === '{{ $name }}' ? 'menu-dropdown-item-active' : 'menu-dropdown-item-inactive', 'menu-dropdown-item group']">
        {{ __('ui.' . $name) }}
    </a>
</li>
