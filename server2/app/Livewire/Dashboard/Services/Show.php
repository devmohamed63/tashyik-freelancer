<?php

namespace App\Livewire\Dashboard\Services;

use App\Models\City;
use App\Models\Service;
use App\Models\User;
use Livewire\Attributes\On;
use Livewire\Component;

class Show extends Component
{
    public $service;
    public $citiesData = [];

    #[On('show-result')]
    public function showResult($id)
    {
        $this->service = Service::with('category')->find($id);

        if (!$this->service) return;

        $catIds = [$this->service->category_id];
        if ($this->service->category && $this->service->category->category_id) {
            $catIds[] = $this->service->category->category_id;
        }

        $cities = City::all();
        $citiesData = [];

        foreach ($cities as $city) {
            // Count service providers in this city that have the required category
            $providersCount = User::isServiceProvider()
                ->where('city_id', $city->id)
                ->whereHas('categories', function($q) use ($catIds) {
                    $q->whereIn('categories.id', $catIds);
                })
                ->count();

            // Count orders for this service made by customers in this city
            $ordersCount = $this->service->orders()
                ->whereHas('customer', function($q) use ($city) {
                    $q->where('city_id', $city->id);
                })
                ->count();

            if ($providersCount > 0 || $ordersCount > 0) {
                // Gap Analysis logic
                $status = 'ok';
                $message = 'الوضع مستقر';
                if ($ordersCount > 0 && $providersCount == 0) {
                    $status = 'critical';
                    $message = 'لا يوجد فنيين لتغطية الطلبات!';
                } elseif ($ordersCount > ($providersCount * 5)) { // Assuming each provider can handle 5 comfortably
                    $status = 'warning';
                    $message = 'نقص في الفنيين';
                } elseif ($providersCount > 0 && $ordersCount == 0) {
                    $status = 'idle';
                    $message = 'عدد فنيين بلا طلبات';
                }

                $citiesData[] = [
                    'name' => $city->name,
                    'providers_count' => $providersCount,
                    'orders_count' => $ordersCount,
                    'status' => $status,
                    'status_message' => $message,
                ];
            }
        }

        // Sort by orders descending to highlight critical areas first
        usort($citiesData, function($a, $b) {
            if ($a['status'] === 'critical' && $b['status'] !== 'critical') return -1;
            if ($b['status'] === 'critical' && $a['status'] !== 'critical') return 1;
            return $b['orders_count'] <=> $a['orders_count'];
        });

        $this->citiesData = $citiesData;

        $this->dispatch('initModal');
    }

    public function render()
    {
        return view('livewire.dashboard.services.show');
    }
}
