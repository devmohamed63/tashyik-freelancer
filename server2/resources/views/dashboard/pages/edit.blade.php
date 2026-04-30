<x-layouts.dashboard page="edit_page">

    <x-dashboard.breadcrumb :page="__('ui.edit_page')">
        @can('viewAny', App\Models\Page::class)
            <x-dashboard.breadcrumb-back
                :url="route('dashboard.pages.index')"
                :name="__('ui.view_pages')" />
        @endcan
    </x-dashboard.breadcrumb>

    @session('status')
        <div class="mb-5">
            <x-dashboard.alerts.success :title="$value" />
        </div>
    @endsession

    <div class="relative overflow-hidden rounded-xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03] overflow-x-auto">
        <form
            x-data="{ loading: false }"
            x-on:submit="loading = true"
            method="POST"
            action="{{ route('dashboard.pages.update', ['page' => $page->id]) }}"
            class="p-4 grid grid-cols-1 md:grid-cols-2 gap-5">

            @method('PUT')
            @csrf

            <!-- Name ar -->
            <x-dashboard.inputs.default
                name="name"
                locale="ar"
                :value="old('name.ar', $page->getTranslation('name', 'ar'))"
                id="page-name-ar"
                :required="true" />

            <!-- Name en -->
            <x-dashboard.inputs.default
                name="name"
                locale="en"
                :value="old('name.en', $page->getTranslation('name', 'en'))"
                id="page-name-en"
                :required="true" />

            <!-- Body ar -->
            <div>
                <x-dashboard.label name="body" locale="ar" :required="true" />
                <textarea name="body[ar]" class="ck-editor">{!! $page->getTranslation('body', 'ar') !!}</textarea>
                @error('body.ar')
                    <x-dashboard.inputs.error :message="$message" />
                @enderror
            </div>

            <!-- Body en -->
            <div>
                <x-dashboard.label name="body" locale="en" :required="true" />
                <textarea name="body[en]" class="ck-editor" locale="en">{!! $page->getTranslation('body', 'en') !!}</textarea>
                @error('body.en')
                    <x-dashboard.inputs.error :message="$message" />
                @enderror
            </div>

            <!-- Status -->
            <div class="w-fit col-span-2">
                <x-dashboard.inputs.checkbox
                    name="status"
                    :title="__('ui.active')"
                    :checked="old('status', $page->isActive())" />
            </div>

            <!-- Submit button -->
            <x-dashboard.buttons.primary :name="__('ui.update')" />

        </form>
    </div>

    @vite(['resources/css/ckeditor.css', 'resources/js/ckeditor.js'])

</x-layouts.dashboard>
