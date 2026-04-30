<?php

namespace App\Livewire\Dashboard\ServiceCollections;

use App\Models\Category;
use App\Models\ServiceCollection;
use App\Models\Service;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Validation\Rule;
use Livewire\Component;

class Create extends Component
{
    public string $title = '';

    public string $target = '';

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
        $this->authorize('manage settings');

        $this->availableTargets = array_map(fn($target) => [
            'value' => $target,
            'label' => __('ui.' . $target),
        ], ServiceCollection::AVAILABLE_TARGET_TYPES);
    }

    public function store()
    {
        $this->validate([
            'title' => ['required', 'string', 'max:255'],
            'target' => ['required', Rule::in(ServiceCollection::AVAILABLE_TARGET_TYPES)],
            'selectedCategories' => ['nullable', 'required_if:target,' . ServiceCollection::CATEGORIES_TARGET_TYPE, 'array'],
            'selectedCategories.*' => ['integer', 'exists:categories,id'],
            'selectedSubcategories' => ['nullable', 'required_if:target,' . ServiceCollection::SUBCATEGORIES_TARGET_TYPE, 'array'],
            'selectedSubcategories.*' => ['integer', 'exists:categories,id'],
            'selectedServices' => ['nullable', 'required_if:target,' . ServiceCollection::SERVICES_TARGET_TYPE, 'array'],
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

        $serviceCollection = ServiceCollection::create([
            'title' => $this->title,
        ]);

        switch ($this->target) {
            case ServiceCollection::CATEGORIES_TARGET_TYPE:
                $serviceIds = Service::whereHas('category', function (Builder $query) {
                    $query->whereIn('category_id', $this->selectedCategories);
                })
                    ->limit('10')
                    ->pluck('id')
                    ->toArray();
                break;

            case ServiceCollection::SUBCATEGORIES_TARGET_TYPE:
                $serviceIds = Service::whereIn('category_id', $this->selectedSubcategories)
                    ->limit('10')
                    ->pluck('id')
                    ->toArray();
                break;

            case ServiceCollection::SERVICES_TARGET_TYPE:
                $serviceIds = $this->selectedServices;
                break;
        }

        $serviceCollection->services()->attach($serviceIds);

        $this->dispatch('hideModal', ['id' => 'createResultModal']);

        $this->dispatch('refreshTable');

        $this->reset();
        $this->mount();
    }

    public function render()
    {
        switch ($this->target) {
            case ServiceCollection::CATEGORIES_TARGET_TYPE:
                $this->categories = Category::isParent()->get(['id', 'name']);
                break;

            case ServiceCollection::SUBCATEGORIES_TARGET_TYPE:
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

            case ServiceCollection::SERVICES_TARGET_TYPE:
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

        return view('livewire.dashboard.service-collections.create');
    }
}
