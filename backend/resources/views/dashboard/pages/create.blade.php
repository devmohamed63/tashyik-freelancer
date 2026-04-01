<x-layouts.dashboard page="create_page">

    <x-dashboard.breadcrumb :page="__('ui.create_page')">
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
            action="{{ route('dashboard.pages.store') }}"
            class="p-4 grid grid-cols-1 md:grid-cols-2 gap-5">

            @csrf

            <!-- Name ar -->
            <x-dashboard.inputs.default
                name="name"
                locale="ar"
                id="page-name-ar"
                :required="true" />

            <!-- Name en -->
            <x-dashboard.inputs.default
                name="name"
                locale="en"
                id="page-name-en"
                :required="true" />

            <!-- Body ar -->
            <div>
                <x-dashboard.label name="body" locale="ar" :required="true" />
                <textarea name="body[ar]" class="ck-editor">{!! old('body.ar') !!}</textarea>
                @error('body.ar')
                    <x-dashboard.inputs.error :message="$message" />
                @enderror
            </div>

            <!-- Body en -->
            <div>
                <x-dashboard.label name="body" locale="en" :required="true" />
                <textarea name="body[en]" class="ck-editor" locale="en">{!! old('body.en') !!}</textarea>
                @error('body.en')
                    <x-dashboard.inputs.error :message="$message" />
                @enderror
            </div>

            <!-- Status -->
            <div class="w-fit col-span-2">
                <x-dashboard.inputs.checkbox
                    name="status"
                    :title="__('ui.active')"
                    :checked="old('status', true)" />
            </div>

            <!-- Submit button -->
            <x-dashboard.buttons.primary :name="__('ui.create')" />

        </form>
    </div>

    @vite(['resources/css/ckeditor.css', 'resources/js/ckeditor.js'])

</x-layouts.dashboard>
