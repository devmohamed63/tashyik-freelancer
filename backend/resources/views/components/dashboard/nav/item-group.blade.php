@props(['name', 'badge' => null, 'children' => []])

<li>
    <a
        href="#"
        @click.prevent="selected = (selected === '{{ $name }}' ? '':'{{ $name }}')"
        class="menu-item group"
        x-bind:class="(selected === '{{ $name }}') || ([
            @foreach ($children as $child)
                    '{{ $child[0] }}'
                @if (!$loop->last)
                    ,
                @endif @endforeach
        ].includes(page)) ? 'menu-item-active' : 'menu-item-inactive'">

        <svg
            x-bind:class="(selected === '{{ $name }}') || ([
                @foreach ($children as $child)
                    '{{ $child[0] }}'
                @if (!$loop->last)
                    ,
                @endif @endforeach
            ].includes(page)) ? 'menu-item-icon-active' : 'menu-item-icon-inactive'"
            class="h-6 w-5"
            width="{{ $attributes->get('icon-width') ?? '32' }}"
            height="{{ $attributes->get('icon-height') ?? '32' }}"
            viewBox="{{ $attributes->get('icon-viewBox') ?? '0 0 24 24' }}"
            fill="{{ $attributes->get('icon-fill') ?? 'none' }}"
            xmlns="http://www.w3.org/2000/svg">
            {{ $slot }}
        </svg>

        <span class="menu-item-text" :class="sidebarToggle ? 'lg:hidden' : ''">
            {{ __('ui.' . $name) }}
            @if($badge !== null)
                <span class="mr-2 rtl:ml-2 rtl:mr-0 inline-flex items-center justify-center rounded-full bg-brand-100 px-2 py-0.5 text-brand-700 text-[10px] font-bold">{{ $badge }}</span>
            @endif
        </span>

        <!-- ic:outline-keyboard-arrow-down -->
        <svg x-bind:class="[(selected === '{{ $name }}') ? 'menu-item-arrow-active' : 'menu-item-arrow-inactive', sidebarToggle ? 'lg:hidden' : '']" class="ms-auto h-4 w-4" xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24">
            <!-- Icon from Google Material Icons by Material Design Authors - https://github.com/material-icons/material-icons/blob/master/LICENSE -->
            <path fill="currentColor" d="M7.41 8.59L12 13.17l4.59-4.58L18 10l-6 6l-6-6z" />
        </svg>
    </a>

    <!-- Dropdown Menu Start -->
    <div class="translate transform overflow-hidden" x-bind:class="(selected === '{{ $name }}') ? 'block' : 'hidden'">
        <ul :class="sidebarToggle ? 'lg:hidden' : 'flex'" class="menu-dropdown mt-2 flex flex-col gap-1 ps-9">

            @foreach ($children as $child)
                @if ($child[2])
                    <x-dashboard.nav.item-child :name="$child[0]" :url="$child[1]" />
                @endif
            @endforeach

        </ul>
    </div>
    <!-- Dropdown Menu End -->
</li>
