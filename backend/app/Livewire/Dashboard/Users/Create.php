<?php

namespace App\Livewire\Dashboard\Users;

use App\Models\User;
use App\Models\City;
use App\Models\Category;
use Illuminate\Support\Facades\Hash;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Validate;

class Create extends Component
{
    use WithFileUploads;

    public string $institution;

    public string $name;

    public string $city;

    public string $phone;

    public string $password;

    public array $selectedCategories;

    public string $residence_name;

    public string $residence_number;

    #[Validate]
    public $residence_image;

    public string $bank_name;

    public string $iban;

    protected function rules()
    {
        return [
            'institution' => ['required', 'string', 'max:255'],
            'name' => ['required', 'string', 'max:255'],
            'city' => ['required', 'integer', 'exists:cities,id'],
            'phone' => ['required', 'string', 'max:255', 'unique:users,phone'],
            'password' => ['required', 'string', 'max:255'],
            'selectedCategories' => ['required', 'array'],
            'selectedCategories.*' => ['required', 'integer', 'exists:categories,id'],
            'residence_name' => ['required', 'string', 'max:255'],
            'residence_image' => ['required', 'image', 'mimes:' . config('app.allowed_image_mimes'), 'max:' . config('app.upload_max_size')],
            'bank_name' => ['required', 'string', 'max:255'],
            'iban' => ['required', 'string', 'max:255'],
        ];
    }

    public function mount()
    {
        $this->authorize('viewAny', User::class);
    }

    #[Computed]
    public function categories(): array
    {
        return Category::isParent()
            ->orderBy('name')
            ->pluck('name', 'id')
            ->toArray();
    }

    #[Computed]
    public function institutions(): array
    {
        return User::whereIn('entity_type', [User::INSTITUTION_ENTITY_TYPE, User::COMPANY_ENTITY_TYPE])
            ->orderBy('name')
            ->pluck('name', 'id')
            ->toArray();
    }

    #[Computed]
    public function cities(): array
    {
        return City::orderBy('name')
            ->pluck('name', 'id')
            ->toArray();
    }

    public function store()
    {
        $this->validate();

        $user = new User();

        // Basic information
        $user->name = $this->name;
        $user->phone = $this->phone;
        $user->password = Hash::make($this->password);
        $user->institution_id = $this->institution;
        $user->city_id = $this->city;
        $user->type = User::SERVICE_PROVIDER_ACCOUNT_TYPE;

        // More information
        $user->entity_type = User::INDIVIDUAL_ENTITY_TYPE;
        $user->residence_name = $this->residence_name;
        $user->residence_number = $this->residence_number;
        $user->bank_name = $this->bank_name;
        $user->iban = $this->iban;

        // Status
        $user->status = User::ACTIVE_STATUS;

        $user->save();

        if ($this->selectedCategories) $user->categories()->attach($this->selectedCategories);

        $user->addMedia($this->residence_image)
            ->toMediaCollection('residence_image');

        $this->dispatch('hideModal', ['id' => 'createResultModal']);

        $this->dispatch('refreshTable');

        $this->reset();
    }

    public function render()
    {
        return view('livewire.dashboard.users.create');
    }
}
