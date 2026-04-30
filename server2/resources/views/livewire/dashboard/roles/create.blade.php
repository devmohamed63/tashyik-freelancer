<div>
    <div class="space-y-5">

        <!-- Modal loader -->
        <x-dashboard.loaders.centered wire:loading.class.remove="hidden" />

        <!-- Modal title -->
        <x-dashboard.modals.title :value="__('ui.create_role')" />

        <form class="space-y-5" wire:submit="store" x-data="{ loading: false }">

            <!-- Name -->
            <x-dashboard.inputs.default
                name="name"
                wire:model="name"
                :required="true" />

            <!-- Select all -->
            <x-dashboard.inputs.checkbox
                id="selectAllCheckbox"
                :title="__('ui.select_all')"
                wire:model.live="selectAllCheckbox"
                wire:click="toggleSelected" />

            <x-dashboard.ui.hr />

            <!-- Permissions -->
            @foreach ($this->permissions as $groupName => $groupPermissions)
                <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-400">{{ __('permissions.' . $groupName) }}</label>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    @foreach ($groupPermissions as $permission)
                        <!-- Permissions -->
                        <x-dashboard.inputs.checkbox
                            :id="'permission' . $permission->id"
                            :value="$permission->name"
                            wire:model="selected"
                            :title="__('permissions.' . $permission->name)" />
                    @endforeach
                </div>
            @endforeach

            @error('selected')
                <x-dashboard.inputs.error :message="$message" />
            @enderror

            <!-- Submit button -->
            <x-dashboard.buttons.primary :name="__('ui.create')" />

        </form>

    </div>
</div>
