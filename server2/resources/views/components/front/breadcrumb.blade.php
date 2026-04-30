<nav>
    <ol class="flex gap-1.5">
        <li>
            <a class="inline-flex font-medium gap-1.5 text-sm text-gray-500 hover:text-gray-700" href="{{ route('home') }}">
                <!-- ic:baseline-home -->
                <svg class="w-4 h-4" xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24"><!-- Icon from Google Material Icons by Material Design Authors - https://github.com/material-icons/material-icons/blob/master/LICENSE -->
                    <path fill="currentColor" d="M10 20v-6h4v6h5v-8h3L12 3L2 12h3v8z" />
                </svg>
                {{ __('ui.home') }}
                <svg
                    class="stroke-current rtl:rotate-180"
                    width="17"
                    height="16"
                    viewBox="0 0 17 16"
                    fill="none"
                    xmlns="http://www.w3.org/2000/svg">
                    <path
                        d="M6.0765 12.667L10.2432 8.50033L6.0765 4.33366"
                        stroke=""
                        stroke-width="1.2"
                        stroke-linecap="round"
                        stroke-linejoin="round" />
                </svg>
            </a>
        </li>
        @foreach ($pages as $page)
            <li class="inline-flex items-center gap-1.5 font-medium text-sm hover:text-gray-700 @if (!$loop->last) text-gray-500 @else text-gray-700 @endif">
                @if ($page[1])
                    <a href="{{ $page[1] }}">
                        {{ $page[0] }}
                    </a>
                @else
                    {{ $page[0] }}
                @endif
                @if (!$loop->last)
                    <svg
                        class="stroke-current rtl:rotate-180"
                        width="17"
                        height="16"
                        viewBox="0 0 17 16"
                        fill="none"
                        xmlns="http://www.w3.org/2000/svg">
                        <path
                            d="M6.0765 12.667L10.2432 8.50033L6.0765 4.33366"
                            stroke=""
                            stroke-width="1.2"
                            stroke-linecap="round"
                            stroke-linejoin="round" />
                    </svg>
                @endif
            </li>
        @endforeach
    </ol>
</nav>
