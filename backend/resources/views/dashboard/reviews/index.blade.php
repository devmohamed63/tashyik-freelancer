<x-layouts.dashboard page="reviews">

    <x-dashboard.breadcrumb :page="'إدارة التقييمات'" />

    {{-- ── Summary Cards ── --}}
    <div class="grid grid-cols-2 md:grid-cols-4 gap-5 mb-5 mt-5">
        <x-dashboard.cards.overview
            style="col"
            :index="2"
            title="إجمالي التقييمات"
            :count="number_format($totalReviews)"
            :authorize="true"
            :link="null">
            <path fill="currentColor" d="M12 17.27L18.18 21l-1.64-7.03L22 9.24l-7.19-.61L12 2L9.19 8.63L2 9.24l5.46 4.73L5.82 21z" />
        </x-dashboard.cards.overview>

        <x-dashboard.cards.overview
            style="col"
            :index="4"
            title="متوسط التقييم"
            :count="$averageRating . ' / 5'"
            :authorize="true"
            :link="null">
            <path fill="currentColor" d="M12 17.27L18.18 21l-1.64-7.03L22 9.24l-7.19-.61L12 2L9.19 8.63L2 9.24l5.46 4.73L5.82 21z" />
        </x-dashboard.cards.overview>

        <x-dashboard.cards.overview
            style="col"
            :index="3"
            title="نسبة 5 نجوم"
            :count="$fiveStarPct . '%'"
            :authorize="true"
            :link="null">
            <path fill="currentColor" d="m5.825 22l1.625-7.025L2 10.25l7.2-.625L12 3l2.8 6.625l7.2.625l-5.45 4.725L18.175 22L12 18.275z" />
        </x-dashboard.cards.overview>

        <x-dashboard.cards.overview
            style="col"
            :index="6"
            :title="__('ui.recent_reviews')"
            :count="number_format($recentReviewsCount)"
            :authorize="true"
            :link="null">
            <path fill="currentColor" d="M11.99 2C6.47 2 2 6.48 2 12s4.47 10 9.99 10C17.52 22 22 17.52 22 12S17.52 2 11.99 2M12 20c-4.42 0-8-3.58-8-8s3.58-8 8-8 8 3.58 8 8-3.58 8-8 8m.5-13H11v6l5.25 3.15.75-1.23-4.5-2.67z" />
        </x-dashboard.cards.overview>
    </div>

    {{-- ── Rating Distribution ── --}}
    <div class="rounded-2xl border border-gray-200 bg-white p-5 md:p-6 mb-5 dark:border-gray-800 dark:bg-white/[0.03]">
        <h3 class="text-lg font-semibold text-gray-800 dark:text-white mb-4">{{ __('ui.rating_distribution') }}</h3>
        <div class="space-y-3">
            @foreach ($ratingDistribution as $stars => $data)
            @php
                $barColor = match(true) {
                    $stars >= 4 => '#22c55e',
                    $stars == 3 => '#eab308',
                    default     => '#ef4444',
                };
            @endphp
            <div class="flex items-center gap-3">
                <div class="flex items-center gap-1 w-12 shrink-0 justify-end">
                    <span class="text-sm font-bold text-gray-700 dark:text-gray-300">{{ $stars }}</span>
                    <svg class="w-4 h-4 text-yellow-400" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M12 17.27L18.18 21l-1.64-7.03L22 9.24l-7.19-.61L12 2L9.19 8.63L2 9.24l5.46 4.73L5.82 21z" />
                    </svg>
                </div>
                <div class="flex-1 h-5 bg-gray-200 dark:bg-gray-700 rounded-full overflow-hidden">
                    <div class="h-full rounded-full" style="width: {{ $data['pct'] }}%; background-color: {{ $barColor }}; min-width: {{ $data['pct'] > 0 ? '8px' : '0' }}"></div>
                </div>
                <div class="w-28 shrink-0 text-end">
                    <span class="text-sm font-bold text-gray-700 dark:text-gray-300">{{ $data['pct'] }}%</span>
                    <span class="text-xs text-gray-400 ms-1">({{ number_format($data['count']) }})</span>
                </div>
            </div>
            @endforeach
        </div>
    </div>

    {{-- ── Reviews Table ── --}}
    <livewire:dashboard.reviews-table />

</x-layouts.dashboard>
