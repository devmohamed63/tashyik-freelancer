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
            :index="1"
            title="نسبة 1 نجمة"
            :count="$oneStarPct . '%'"
            :authorize="true"
            :link="null">
            <path fill="currentColor" d="m8.85 17.825l3.15-1.9l3.15 1.925l-.825-3.6l2.775-2.4l-3.65-.325l-1.45-3.4l-1.45 3.375l-3.65.325l2.775 2.425zM5.825 22l1.625-7.025L2 10.25l7.2-.625L12 3l2.8 6.625l7.2.625l-5.45 4.725L18.175 22L12 18.275zM12 13.25" />
        </x-dashboard.cards.overview>
    </div>

    {{-- ── Reviews Table ── --}}
    <livewire:dashboard.reviews-table />

</x-layouts.dashboard>
