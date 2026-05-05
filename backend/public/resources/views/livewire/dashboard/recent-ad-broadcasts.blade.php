<div class="mt-8 rounded-xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03] overflow-hidden">
    <div class="border-b border-gray-200 px-4 py-3 dark:border-gray-800">
        <h2 class="text-base font-semibold text-gray-900 dark:text-white">{{ __('ui.recent_ad_broadcasts') }}</h2>
        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
            @php
                $hintKey = match (true) {
                    $useModalForResend => 'ui.recent_ad_broadcasts_hint',
                    $redirectOnResend => 'ui.recent_ad_broadcasts_hint_list',
                    default => 'ui.recent_ad_broadcasts_hint_page',
                };
            @endphp
            {{ __($hintKey) }}
        </p>
    </div>

    @if ($broadcasts->isEmpty())
        <p class="px-4 py-6 text-sm text-gray-500 dark:text-gray-400">{{ __('ui.no_ad_broadcasts_yet') }}</p>
    @else
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-800">
                <thead class="bg-gray-50 dark:bg-gray-900/40">
                    <tr>
                        <th scope="col" class="px-4 py-2 text-start text-xs font-medium text-gray-500 dark:text-gray-400">{{ __('validation.attributes.title') }}</th>
                        <th scope="col" class="px-4 py-2 text-start text-xs font-medium text-gray-500 dark:text-gray-400">{{ __('validation.attributes.audiences') }}</th>
                        <th scope="col" class="px-4 py-2 text-start text-xs font-medium text-gray-500 dark:text-gray-400">{{ __('ui.created_at') }}</th>
                        @can('create', App\Models\Banner::class)
                            <th scope="col" class="px-4 py-2 text-start text-xs font-medium text-gray-500 dark:text-gray-400">{{ __('ui.actions') }}</th>
                        @endcan
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-gray-800">
                    @foreach ($broadcasts as $row)
                        <tr class="text-sm text-gray-800 dark:text-gray-200">
                            <td class="px-4 py-2 font-medium">{{ $row->title }}</td>
                            <td class="px-4 py-2">
                                {{ collect($row->audienceKeys())->map(fn (string $k) => __('ui.'.$k))->join(', ') }}
                            </td>
                            <td class="px-4 py-2 whitespace-nowrap text-gray-600 dark:text-gray-400">
                                {{ $row->created_at?->timezone(config('app.timezone'))->isoFormat(config('app.time_format')) }}
                            </td>
                            @can('create', App\Models\Banner::class)
                                <td class="px-4 py-2">
                                    <button
                                        type="button"
                                        wire:click="promptResend({{ $row->id }})"
                                        class="text-sm font-medium text-brand-600 hover:text-brand-700 dark:text-brand-400 dark:hover:text-brand-300">
                                        {{ __('ui.resend_ad') }}
                                    </button>
                                </td>
                            @endcan
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</div>
