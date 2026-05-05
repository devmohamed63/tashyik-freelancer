<th class="px-5 py-3 sm:px-6">
    <div class="flex items-center">
        @if ($field['type'] == 'checkbox')
            @if (!isset($footerFields))
                <x-dashboard.tables.inputs.checkbox name="selectAll" wire:model.live="selectAllCheckbox" wire:click="toggleSelected" />
            @endif
        @else
            @if (!isset($footerFields) && $field['sortable'])
                {{-- Sortable column in table head --}}
                <button wire:click="sortBy('{{ $field['name'] }}')" class="font-bold text-gray-500 text-theme-xs dark:text-gray-400 inline-flex gap-2 items-center">
                    {{ $field['label'] }}

                    @if ($orderColumn == $field['name'])
                        @if ($orderType == 'desc')
                            <!-- fa6-solid:sort-down -->
                            <svg class="w-4 h-4 text-gray-500" xmlns="http://www.w3.org/2000/svg" width="20" height="32" viewBox="0 0 320 512"><!-- Icon from Font Awesome Solid by Dave Gandy - https://creativecommons.org/licenses/by/4.0/ -->
                                <path fill="currentColor" d="M182.6 470.6c-12.5 12.5-32.8 12.5-45.3 0l-128-128c-9.2-9.2-11.9-22.9-6.9-34.9S19 287.9 32 287.9h256c12.9 0 24.6 7.8 29.6 19.8s2.2 25.7-6.9 34.9l-128 128z" />
                            </svg>
                        @else
                            <!-- typcn:arrow-sorted-up -->
                            <svg class="w-4 h-4 text-gray-500" xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24"><!-- Icon from Typicons by Stephen Hutchings - https://creativecommons.org/licenses/by-sa/4.0/ -->
                                <path fill="currentColor" d="M18.2 13.3L12 7l-6.2 6.3c-.2.2-.3.5-.3.7s.1.5.3.7s.4.3.7.3h11c.3 0 .5-.1.7-.3s.3-.5.3-.7s-.1-.5-.3-.7" />
                            </svg>
                        @endif
                    @else
                        <!-- bxs:sort-alt -->
                        <svg class="w-4 h-4 text-gray-400 dark:text-gray-600" xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24"><!-- Icon from BoxIcons Solid by Atisa - https://creativecommons.org/licenses/by/4.0/ -->
                            <path fill="currentColor" d="M6.227 11h11.547c.862 0 1.32-1.02.747-1.665L12.748 2.84a.998.998 0 0 0-1.494 0L5.479 9.335C4.906 9.98 5.364 11 6.227 11m5.026 10.159a.998.998 0 0 0 1.494 0l5.773-6.495c.574-.644.116-1.664-.747-1.664H6.227c-.862 0-1.32 1.02-.747 1.665z" />
                        </svg>
                    @endif
                </button>
            @else
                {{-- Normal column --}}
                <p class="font-bold text-gray-500 text-theme-xs dark:text-gray-400">
                    {{ $field['label'] }}
                </p>
            @endif
        @endif
    </div>
</th>
