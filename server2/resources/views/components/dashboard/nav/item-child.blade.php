@props(['name', 'url', 'badge' => null])

<li>
    <a href="{{ $url }}" x-bind:class="[page === '{{ $name }}' ? 'menu-dropdown-item-active' : 'menu-dropdown-item-inactive', 'menu-dropdown-item group flex justify-between items-center']">
        <span>{{ __('ui.' . $name) }}</span>
        @if($badge !== null)
            <span class="inline-flex items-center justify-center rounded-full bg-brand-100 px-2 py-0.5 text-brand-700 text-[10px] font-bold">{{ $badge }}</span>
        @endif
    </a>
</li>
