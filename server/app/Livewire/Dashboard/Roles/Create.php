<?php

namespace App\Livewire\Dashboard\Roles;

use Illuminate\Database\Eloquent\Collection;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class Create extends Component
{
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

    public function store()
    {
        $this->validate(
            [
                'name' => ['required', 'string', 'max:255', 'unique:roles,name'],
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

        $role = Role::create([
            'name' => $this->name,
        ]);

        $role->syncPermissions($this->selected);

        $this->dispatch('hideModal', ['id' => 'createResultModal']);

        $this->dispatch('refreshTable');

        $this->reset();
    }

    public function render()
    {
        return view('livewire.dashboard.roles.create');
    }
}
