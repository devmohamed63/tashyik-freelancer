<x-layouts.dashboard page="articles">

    <div class="flex items-center justify-between mb-4">
        <x-dashboard.breadcrumb :page="__('ui.articles')" />
        <span class="bg-brand-50 text-brand-700 text-sm font-medium px-4 py-1.5 rounded-full border border-brand-200">
            اجمالي المقالات: {{ \App\Models\Article::count() }}
        </span>
    </div>
    <livewire:dashboard.articles-table />

</x-layouts.dashboard>
