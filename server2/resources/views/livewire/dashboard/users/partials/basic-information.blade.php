<div class="max-w-xl grid grid-cols-1 sm:grid-cols-2 gap-5">

    <!-- Image -->
    <x-dashboard.info-label :name="__('validation.attributes.image')">
        <img class="rounded-md w-32 h-32 object-center object-cover" src="{{ $avatar }}">
    </x-dashboard.info-label>

    <div class="flex flex-col gap-5">
        <!-- Name -->
        <x-dashboard.info-label
            :name="__('validation.attributes.name')"
            :value="$user?->name" />

        <!-- Phone -->
        <x-dashboard.info-label
            :name="__('validation.attributes.phone')"
            :value="$user?->phone" />

    </div>

    <!-- Role -->
    <x-dashboard.info-label :name="__('ui.role')" class="flex flex-wrap gap-2 items-center">

        @forelse ($userRoles as $roleName)
            <x-dashboard.badges.success :name="$roleName" />
        @empty
            <x-dashboard.badges.light :name="__('ui.none')" />
        @endforelse

    </x-dashboard.info-label>

    <!-- Created at -->
    <x-dashboard.info-label
        :name="__('ui.created_at')"
        :value="$user?->created_at->isoFormat(config('app.time_format'))" />

</div>
