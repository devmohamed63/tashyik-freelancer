<?php

namespace App\Livewire\Dashboard\Cities;

use App\Models\City;
use App\Models\Service;
use App\Models\User;
use Livewire\Attributes\On;
use Livewire\Component;

class Show extends Component
{
    public $city;
    public $servicesWithCount = [];

    #[On('show-result')]
    public function showResult($id)
    {
        $city = City::find($id);
        $this->city = $city;

        $services = Service::with('category')->get();
        $servicesWithCount = [];

        foreach ($services as $service) {
            $catIds = [];
            if ($service->category_id) {
                $catIds[] = $service->category_id;
                if ($service->category && $service->category->category_id) {
                    $catIds[] = $service->category->category_id;
                }
            }

            $count = User::isServiceProvider()
                ->where('city_id', $city->id)
                ->whereHas('categories', function($q) use ($catIds) {
                    $q->whereIn('categories.id', $catIds);
                })
                ->count();

            if ($count > 0) {
                $servicesWithCount[] = [
                    'name' => $service->name,
                    'category' => $service->category ? $service->category->name : '',
                    'count' => $count
                ];
            }
        }

        // sort by count
        usort($servicesWithCount, function($a, $b) {
            return $b['count'] <=> $a['count'];
        });

        $this->servicesWithCount = $servicesWithCount;

        $this->dispatch('initModal');
    }

    public function render()
    {
        return view('livewire.dashboard.cities.show');
    }
}
