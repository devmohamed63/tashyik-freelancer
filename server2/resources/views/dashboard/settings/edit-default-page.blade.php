<x-layouts.dashboard :page="$page->tag">

    <x-dashboard.breadcrumb :page="__('ui.' . $page->tag)" />

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
            action="{{ route('dashboard.settings.update_default_page', ['page' => $page->id]) }}"
            class="p-4 flex flex-col gap-5">

            @method('PUT')
            @csrf

            <div class="max-w-xl">
                <!-- Name ar -->
                <x-dashboard.inputs.default
                    name="name"
                    locale="ar"
                    :value="old('name.ar', $page->getTranslation('name', 'ar'))"
                    id="page-name-ar"
                    :required="true" />
            </div>

            <!-- Body ar -->
            <div>
                <x-dashboard.label name="body" locale="ar" :required="true" />
                <textarea name="body[ar]" class="ck-editor">{!! $page->getTranslation('body', 'ar') !!}</textarea>
                @error('body.ar')
                    <x-dashboard.inputs.error :message="$message" />
                @enderror
            </div>

            <!-- Submit button -->
            <x-dashboard.buttons.primary :name="__('ui.update')" />

        </form>
    </div>

    @vite(['resources/css/ckeditor.css', 'resources/js/ckeditor.js'])

</x-layouts.dashboard>
