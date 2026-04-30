<?php

namespace App\Livewire\Dashboard\Coupons;

use App\Models\Category;
use App\Models\Coupon;
use App\Models\Service;
use Illuminate\Support\Collection;
use Illuminate\Validation\Rule;
use Livewire\Component;

class Create extends Component
{
    public string $name = '';

    public string $code = '';

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

    public bool $welcome = false;

    public function mount()
    {
        $this->authorize('manage coupons');

        $this->availableTypes = array_map(fn($type) => [
            'value' => $type,
            'label' => __('ui.' . $type),
        ], Coupon::AVAILABLE_TYPES);

        $this->availableTargets = array_map(fn($target) => [
            'value' => $target,
            'label' => __('ui.' . $target),
        ], Coupon::AVAILABLE_TARGET_TYPES);
    }

    public function store()
    {
        $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'code' => ['required', 'string', 'max:255'],
            'type' => ['required', Rule::in(Coupon::AVAILABLE_TYPES)],
            'target' => ['required', Rule::in(Coupon::AVAILABLE_TARGET_TYPES)],
            'selectedCategories' => ['nullable', 'required_if:target,' . Coupon::CATEGORIES_TARGET_TYPE, 'array'],
            'selectedCategories.*' => ['integer', 'exists:categories,id'],
            'selectedSubcategories' => ['nullable', 'required_if:target,' . Coupon::SUBCATEGORIES_TARGET_TYPE, 'array'],
            'selectedSubcategories.*' => ['integer', 'exists:categories,id'],
            'selectedServices' => ['nullable', 'required_if:target,' . Coupon::SERVICES_TARGET_TYPE, 'array'],
            'selectedServices.*' => ['integer', 'exists:services,id'],
            'welcome' => ['nullable', 'boolean'],
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

        // Toggle the welcome coupon
        if ($this->welcome) {
            Coupon::where('welcome', true)->update([
                'welcome' => false
            ]);
        }

        $coupon = Coupon::create([
            'name' => $this->name,
            'code' => $this->code,
            'target' => $this->target,
            'type' => $this->type,
            'value' => $this->value,
            'welcome' => $this->welcome,
        ]);

        switch ($this->target) {
            case Coupon::CATEGORIES_TARGET_TYPE:
                $coupon->categories()->attach($this->selectedCategories);
                break;

            case Coupon::SUBCATEGORIES_TARGET_TYPE:
                $coupon->categories()->attach($this->selectedSubcategories);
                break;

            case Coupon::SERVICES_TARGET_TYPE:
                $coupon->services()->attach($this->selectedServices);
                break;
        }


        $this->dispatch('hideModal', ['id' => 'createResultModal']);

        $this->dispatch('refreshTable');

        $this->reset();
        $this->mount();
    }

    public function render()
    {
        switch ($this->target) {
            case Coupon::CATEGORIES_TARGET_TYPE:
                $this->categories = Category::isParent()->get(['id', 'name']);
                break;

            case Coupon::SUBCATEGORIES_TARGET_TYPE:
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

            case Coupon::SERVICES_TARGET_TYPE:
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

        return view('livewire.dashboard.coupons.create');
    }
}
