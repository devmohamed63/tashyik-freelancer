<?php

namespace App\Livewire\Dashboard\Roles;

use Illuminate\Database\Eloquent\Collection;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Livewire\Component;
use Spatie\Permission\Models\Role;

class Show extends Component
{
    public string $name;

    public Role|Collection $role;

    public function mount()
    {
        $this->authorize('manage roles and permissions');
    }

    #[Computed()]
    public function permissions(): Collection|array
    {
        if (!isset($this->role)) {
            return [];
        }

        return $this->role->permissions()->get(['id', 'name', 'tag_name'])->groupBy('tag_name');
    }

    #[On('show-result')]
    public function getResult($id)
    {
        $this->role = Role::findOrFail($id);

        $this->name = $this->role->name;
    }

    public function render()
    {
        return view('livewire.dashboard.roles.show');
    }
}
