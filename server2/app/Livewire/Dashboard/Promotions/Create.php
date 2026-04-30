<?php

namespace App\Livewire\Dashboard\Promotions;

use App\Models\Category;
use App\Models\Promotion;
use App\Models\Service;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Validation\Rule;
use Livewire\Component;

class Create extends Component
{
    public string $name = '';

    public float $value = 0;

    public string $type = '';

    public string $target = '';

    public array $availableTypes = [];

    public array $availableTargets = [];

    public int|null $selectedCategory = null;

    public int|null $selectedSubcategory = null;

    public Collection $categories;

    public Collection $subcategories;

    public Collection $services;

    public array $selectedCategories = [];

    public array $selectedSubcategories = [];

    public array $selectedServices = [];

    public function mount()
    {
        $this->authorize('manage promotions');

        $this->availableTypes = array_map(fn($type) => [
            'value' => $type,
            'label' => __('ui.' . $type),
        ], Promotion::AVAILABLE_TYPES);

        $this->availableTargets = array_map(fn($target) => [
            'value' => $target,
            'label' => __('ui.' . $target),
        ], Promotion::AVAILABLE_TARGET_TYPES);
    }

    public function store()
    {
        $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'type' => ['required', Rule::in(Promotion::AVAILABLE_TYPES)],
            'target' => ['required', Rule::in(Promotion::AVAILABLE_TARGET_TYPES)],
            'selectedCategories' => ['nullable', 'required_if:target,' . Promotion::CATEGORIES_TARGET_TYPE, 'array'],
            'selectedCategories.*' => ['integer', 'exists:categories,id'],
            'selectedSubcategories' => ['nullable', 'required_if:target,' . Promotion::SUBCATEGORIES_TARGET_TYPE, 'array'],
            'selectedSubcategories.*' => ['integer', 'exists:categories,id'],
            'selectedServices' => ['nullable', 'required_if:target,' . Promotion::SERVICES_TARGET_TYPE, 'array'],
            'selectedServices.*' => ['integer', 'exists:services,id'],
        ], [
            'selectedCategories.required_if' => __('validation.required', [
                'attribute' => __('validation.attributes.categories')
            ]),
            'selectedSubcategories.required_if' => __('validation.required', [
                'attribute' => __('validation.attributes.subcategories')
            ]),
            'selectedServices.required_if' => __('validation.required', [
                'attribute' => __('validation.attributes.services')
            ]),
        ]);

        $promotion = Promotion::create([
            'name' => $this->name,
            'type' => $this->type,
            'value' => $this->value,
        ]);

        switch ($this->target) {
            case Promotion::CATEGORIES_TARGET_TYPE:
                $services = Service::whereHas('category', function (Builder $query) {
                    $query->whereIn('category_id', $this->selectedCategories);
                });
                break;

            case Promotion::SUBCATEGORIES_TARGET_TYPE:
                $services = Service::whereIn('category_id', $this->selectedSubcategories);
                break;

            case Promotion::SERVICES_TARGET_TYPE:
                $services = Service::whereIn('id', $this->selectedServices);
                break;
        }

        $services->update(['promotion_id' => $promotion->id]);

        $this->dispatch('hideModal', ['id' => 'createResultModal']);

        $this->dispatch('refreshTable');

        $this->reset();
        $this->mount();
    }

    public function render()
    {
        switch ($this->target) {
            case Promotion::CATEGORIES_TARGET_TYPE:
                $this->categories = Category::isParent()->get(['id', 'name']);
                break;

            case Promotion::SUBCATEGORIES_TARGET_TYPE:
                $this->categories = Category::isParent()->get(['id', 'name']);

                $categoryExists = $this->categories->contains(function ($value) {
                    return $value->id == $this->selectedCategory;
                });

                if ($this->selectedCategory && $categoryExists) {
                    $this->selectedSubcategories = [];
                    $this->subcategories = Category::where('category_id', $this->selectedCategory)->get(['id', 'name']);
                } else {
                    $this->selectedCategory = null;
                    $this->subcategories = collect();
                }

                break;

            case Promotion::SERVICES_TARGET_TYPE:
                $this->categories = Category::isParent()->get(['id', 'name']);

                $categoryExists = $this->categories->contains(function ($value) {
                    return $value->id == $this->selectedCategory;
                });

                if ($this->selectedCategory && $categoryExists) {
                    $this->subcategories = Category::where('category_id', $this->selectedCategory)->get(['id', 'name']);
                } else {
                    $this->selectedCategory = null;
                    $this->subcategories = collect();
                }

                $subcategoryExists = $this->subcategories->contains(function ($value) {
                    return $value->id == $this->selectedSubcategory;
                });

                if ($this->selectedSubcategory && $subcategoryExists) {
                    $this->selectedServices = [];
                    $this->services = Service::where('category_id', $this->selectedSubcategory)->get(['id', 'name']);
                } else {
                    $this->selectedSubcategory = null;
                    $this->services = collect();
                }

                break;
        }

        return view('livewire.dashboard.promotions.create');
    }
}
