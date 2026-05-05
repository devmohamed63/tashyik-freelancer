<div>
    <div class="space-y-5">

        <!-- Modal loader -->
        <x-dashboard.loaders.centered wire:loading.class.remove="hidden" />

        <!-- Modal title -->
        <x-dashboard.modals.title :value="__('ui.show_role')" />

        <div class="space-y-5">

            <!-- Name -->
            <x-dashboard.info-label :name="__('validation.attributes.name')" :value="$name" />

            <x-dashboard.ui.hr />

            <!-- Permissions -->
            @foreach ($this->permissions as $groupName => $groupPermissions)
                <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-400">{{ __('permissions.' . $groupName) }}</label>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    @foreach ($groupPermissions as $permission)
                        <!-- Permissions -->
                        <x-dashboard.inputs.checkbox
                            :id="'showPermission' . $permission->id"
                            :value="$permission->name"
                            :title="__('permissions.' . $permission->name)"
                            disabled
                            checked />
                    @endforeach
                </div>
            @endforeach

        </div>
    </div>
</div>
