<x-layouts.dashboard page="analytics">

    <div class="flex flex-col gap-5">
        <span class="text-2xl text-gray-700 font-medium dark:text-gray-400">{{ __('ui.daily_orders') }}</span>
        <div class="flex flex-col md:grid grid-cols-2 xl:grid-cols-3 2xl:grid-cols-4 gap-8 bg-white dark:bg-gray-800 rounded-xl p-5 shadow">

            @foreach ($categories as $category)
                <div class="inline-flex items-center bg-white dark:bg-gray-900 dark:border border-gray-700 shadow-sm rounded-xl">
                    <div class="overflow-hidden rounded-s-lg w-24 shrink-0 h-full">
                        <img class="rounded-s-lg dark:opacity-40 w-24 h-full object-center object-cover scale-125" src="{{ $category->getImageUrl('sm') ?? 'https://cdn.tashyik.com/media/819/conversions/miMUSTHvQ4ZeIN3E1760967576-sm.webp' }}" alt="{{ $category->name }}">
                    </div>
                    <div class="flex flex-col items-start justify-start w-full h-full gap-2 text-start font-bold p-3">
                        <p class="text-lg text-brand-500 truncate max-w-[90%]">{{ $category->name }}</p>
                        <span class="text-2xl text-gray-600 dark:text-gray-400" id="category-{{ $category->id }}">0</span>
                    </div>
                </div>
            @endforeach

        </div>

        <div class="flex flex-col md:grid grid-cols-2 lg:grid-cols-3 2xl:grid-cols-6 gap-6 bg-white dark:bg-gray-800 rounded-xl p-6 shadow">
            @foreach ($cities as $city)
                <x-dashboard.cards.city :city="$city" />
            @endforeach
        </div>
    </div>

    <script src="https://www.gstatic.com/firebasejs/9.23.0/firebase-app-compat.js"></script>
    <script src="https://www.gstatic.com/firebasejs/9.23.0/firebase-firestore-compat.js"></script>

    @vite('resources/js/analytics.js')

</x-layouts.dashboard>
