<?php

namespace App\Livewire\Dashboard\Plans;

use App\Models\Category;
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

    public ?string $badge = null;

    public array $features = [''];

    public array $selectedCategories = [];

    public function mount()
    {
        $this->authorize('manage plans');
    }

    public function addFeature()
    {
        $this->features[] = '';
    }

    public function removeFeature($index)
    {
        unset($this->features[$index]);
        $this->features = array_values($this->features);
    }

    #[Computed]
    public function targetGroups(): array
    {
        return User::AVAILABLE_ENTITY_TYPES;
    }

    #[Computed]
    public function categoriesList()
    {
        return Category::isChild()->orderBy('name')->get();
    }

    public function store()
    {
        $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'price' => ['required', 'numeric', 'min:0'],
            'target_group' => ['required', Rule::in($this->targetGroups())],
            'duration_in_months' => ['required', 'integer', 'min:1'],
            'badge' => ['nullable', 'string', Rule::in(array_keys(Plan::BADGES))],
            'features' => ['nullable', 'array'],
            'features.*' => ['nullable', 'string', 'max:255'],
            'selectedCategories' => ['nullable', 'array'],
            'selectedCategories.*' => ['exists:categories,id'],
        ], [], [
            'features.*' => __('ui.feature'),
        ]);

        $plan = Plan::create([
            'name' => $this->name,
            'price' => $this->price,
            'target_group' => $this->target_group,
            'badge' => $this->badge ?: null,
            'duration_in_days' => $this->duration_in_months * 30,
        ]);

        $featuresList = array_filter($this->features, fn($f) => trim($f ?? '') !== '');
        if (!empty($featuresList)) {
            $featuresData = array_map(fn($f) => ['title' => $f], $featuresList);
            $plan->features()->createMany($featuresData);
        }

        if (!empty($this->selectedCategories)) {
            $plan->categories()->attach($this->selectedCategories);
        }

        $this->dispatch('hideModal', ['id' => 'createResultModal']);

        $this->dispatch('resetPage');

        $this->dispatch('refreshTable');

        $this->reset();
    }

    public function render()
    {
        return view('livewire.dashboard.plans.create');
    }
}
