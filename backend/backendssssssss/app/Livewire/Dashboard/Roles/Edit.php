<?php

namespace App\Livewire\Dashboard\Roles;

use Illuminate\Database\Eloquent\Collection;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Livewire\Component;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class Edit extends Component
{
    public int|null $roleId;

    public Role $role;

    public string $name;

    public array $selected;

    public bool $selectAllCheckbox = false;

    public function mount()
    {
        $this->authorize('manage roles and permissions');
    }

    #[Computed]
    public function permissions(): Collection
    {
        return Permission::get(['id', 'name', 'tag_name'])->groupBy('tag_name');
    }

    public function toggleSelected()
    {
        $this->selected = $this->selectAllCheckbox
            ? Permission::pluck('name')->toArray()
            : [];
    }

    #[On('edit-result')]
    public function getResult($id)
    {
        $this->roleId = $id;

        $this->role = Role::findOrFail($this->roleId);

        $this->name = $this->role->name;

        $this->selected = $this->role->permissions->pluck('name')->toArray();
    }

    public function update()
    {
        $this->validate(
            [
                'name' => ['required', 'string', 'max:255', 'unique:roles,name,' . $this->roleId],
                'selected' => ['required', 'array'],
                'selected.*' => ['string', 'exists:permissions,name'],
            ],
            [
                'selected.required' => __('validation.min.array', [
                    'attribute' => __('ui.role'),
                    'min' => 1
                ])
            ]
        );

        $this->role->update([
            'name' => $this->name,
        ]);

        $this->role->syncPermissions($this->selected);

        $this->dispatch('hideModal', ['id' => 'editResultModal']);

        $this->dispatch('refreshTable');

        $this->reset();
    }

    public function render()
    {
        return view('livewire.dashboard.roles.edit');
    }
}
