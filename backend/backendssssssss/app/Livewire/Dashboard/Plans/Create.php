<?php

namespace App\Livewire\Dashboard\Plans;

use App\Models\Plan;
use App\Models\User;
use Livewire\Component;
use Livewire\Attributes\Computed;
use Illuminate\Validation\Rule;

class Create extends Component
{
    public string $name;

    public int $price = 0;

    public string $target_group;

    public int $duration_in_months = 1;

    public function mount()
    {
        $this->authorize('manage plans');
    }

    #[Computed]
    public function targetGroups(): array
    {
        return User::AVAILABLE_ENTITY_TYPES;
    }

    public function store()
    {
        $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'price' => ['required', 'numeric', 'min:0'],
            'target_group' => ['required', Rule::in($this->targetGroups())],
            'duration_in_months' => ['required', 'integer', 'min:1'],
        ]);

        Plan::create([
            'name' => $this->name,
            'price' => $this->price,
            'target_group' => $this->target_group,
            'duration_in_days' => $this->duration_in_months * 30,
        ]);

        $this->dispatch('hideModal', ['id' => 'createResultModal']);

        $this->dispatch('refreshTable');

        $this->reset();
    }

    public function render()
    {
        return view('livewire.dashboard.plans.create');
    }
}
