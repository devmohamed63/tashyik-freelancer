<div>
    <button id="{{ $dropdown['id'] }}Button" data-dropdown-toggle="{{ $dropdown['id'] }}" class="inline-flex items-center text-gray-500 border border-gray-200 focus:outline-none hover:bg-gray-50 focus:ring-4 focus:ring-gray-100 font-medium rounded-lg text-sm px-4 py-3 dark:bg-gray-900 dark:border-transparent dark:text-gray-400 dark:hover:bg-gray-800 dark:focus:ring-gray-800" type="button">
        {{ $dropdown['name'] }}
        <svg class="w-2.5 h-2.5 ms-2.5" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 10 6">
            <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m1 1 4 4 4-4" />
        </svg>
    </button>
    <!-- Dropdown menu -->
    <div wire:ignore.self id="{{ $dropdown['id'] }}" class="z-10 hidden bg-white divide-y divide-gray-100 rounded-lg shadow-sm w-44 dark:bg-gray-900 dark:divide-gray-800">
        <ul class="py-1 text-sm text-gray-600 dark:text-gray-400" aria-labelledby="{{ $dropdown['id'] }}Button">
            @foreach ($dropdown['children'] as $child)
                <li>
                    <button @if ($child['wireAction']) wire:click="{{ $child['wireAction'] }}" @endif type="button" class="text-start block px-4 py-2 hover:bg-gray-100 dark:hover:bg-gray-600 dark:hover:text-white w-full">{{ $child['name'] }}</button>
                </li>
            @endforeach
        </ul>
    </div>
</div>
