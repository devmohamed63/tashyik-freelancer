<?php

namespace App\Livewire\Dashboard\Cities;

use App\Models\City;
use Livewire\Attributes\On;
use Livewire\Component;

class Edit extends Component
{
    public int|null $cityId;

    public City $city;

    public array $name;

    public function mount()
    {
        $this->authorize('viewAny', City::class);
    }

    #[On('edit-result')]
    public function getResult($id)
    {
        $this->cityId = $id;

        $this->city = City::findOrFail($this->cityId);

        $this->name = $this->city->getTranslations('name');

        $this->authorize('update', $this->city);
    }

    public function update()
    {
        $this->validate([
            'name.*' => ['required', 'string', 'max:255'],
        ]);

        $this->city->update([
            'name' => $this->name,
        ]);

        $this->dispatch('hideModal', ['id' => 'editResultModal']);

        $this->dispatch('refreshTable');

        $this->reset();
    }

    public function render()
    {
        return view('livewire.dashboard.cities.edit');
    }
}
