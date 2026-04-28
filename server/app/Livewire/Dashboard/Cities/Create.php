<?php

namespace App\Livewire\Dashboard\Cities;

use App\Models\City;
use Livewire\Component;

class Create extends Component
{
    public array $name;

    public function mount()
    {
        $this->authorize('viewAny', City::class);
    }

    public function store()
    {
        $this->authorize('create', City::class);

        $this->validate([
            'name.*' => ['required', 'string', 'max:255'],
        ]);

        City::create([
            'name' => $this->name,
        ]);

        $this->dispatch('hideModal', ['id' => 'createResultModal']);

        $this->dispatch('refreshTable');

        $this->reset();
    }

    public function render()
    {
        return view('livewire.dashboard.cities.create');
    }
}
