<x-layouts.dashboard page="edit_user">

    <x-dashboard.breadcrumb :page="__('ui.edit_user')">
        @can('viewAny', App\Models\User::class)
            <x-dashboard.breadcrumb-back
                :url="route('dashboard.users.index')"
                :name="__('ui.view_users')" />
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
            action="{{ route('dashboard.users.update', ['user' => $user->id]) }}"
            enctype="multipart/form-data"
            class="p-4 max-w-xl flex flex-col gap-5">

            @method('PUT')
            @csrf

            <!-- Image -->
            <x-dashboard.inputs.file.single-image
                class="w-48"
                preview-class="w-48 h-48"
                id="image"
                name="image"
                :image-url="$avatar"
                accept=".webp, .png, .jpg, .jpeg" />

            <!-- Name -->
            <x-dashboard.inputs.default
                name="name"
                :value="$user->name"
                :required="true" />

            <!-- Phone -->
            <x-dashboard.inputs.default
                name="phone"
                :value="$user->phone"
                :required="true" />

            <!-- Password -->
            <x-dashboard.inputs.password
                name="password" />

            <!-- Role -->
            <x-dashboard.inputs.select
                label="role"
                name="roles[]"
                :children="$roles"
                child-key="name"
                :selected="old('roles', $userRoles)" />

            <!-- Submit button -->
            <x-dashboard.buttons.primary :name="__('ui.update')" />

        </form>
    </div>

    @vite('resources/js/file-input.js')

</x-layouts.dashboard>
