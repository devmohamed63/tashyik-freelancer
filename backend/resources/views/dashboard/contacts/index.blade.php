<x-layouts.dashboard page="contact_requests">

    <div class="flex items-center justify-between mb-4">
        <x-dashboard.breadcrumb :page="__('ui.contact_requests')" />
        <div class="flex items-center gap-3">
            <span class="bg-gray-50 text-gray-700 text-sm font-medium px-4 py-1.5 rounded-full border border-gray-200">
                الكل: {{ \App\Models\Contact::count() }}
            </span>
            <span class="bg-green-50 text-green-700 text-sm font-medium px-4 py-1.5 rounded-full border border-green-200">
                المقروءة: {{ \App\Models\Contact::where('is_read', true)->count() }}
            </span>
            <span class="bg-red-50 text-red-700 text-sm font-medium px-4 py-1.5 rounded-full border border-red-200">
                الغير مقروءة: {{ \App\Models\Contact::where('is_read', false)->count() }}
            </span>
        </div>
    </div>
    <livewire:dashboard.contacts-table />

</x-layouts.dashboard>
