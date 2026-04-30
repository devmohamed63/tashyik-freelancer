<?php

namespace App\Livewire\Dashboard\Categories;

use App\Models\Category;
use App\Models\Service;
use App\Models\User;
use Livewire\Attributes\On;
use Livewire\Component;

class Show extends Component
{
    public $category;
    public $servicesData = [];

    #[On('show-result')]
    public function showResult($id)
    {
        $this->category = Category::find($id);

        if (!$this->category) return;
        
        $services = $this->category->childrenServices()
            ->withCount(['orders', 'orders as completed_orders' => fn($q) => $q->completed()])
            ->get();

        $servicesData = [];

        foreach ($services as $service) {
            $catIds = [$service->category_id, $this->category->id];

            $providersCount = User::isServiceProvider()
                ->whereHas('categories', function($q) use ($catIds) {
                    $q->whereIn('categories.id', $catIds);
                })->count();

            $successRate = $service->orders_count > 0 
                ? round(($service->completed_orders / $service->orders_count) * 100) 
                : 0;

            $servicesData[] = [
                'name' => $service->name,
                'providers_count' => $providersCount,
                'orders_count' => $service->orders_count,
                'success_rate' => $successRate
            ];
        }

        // Sort by orders count
        usort($servicesData, function($a, $b) {
            return $b['orders_count'] <=> $a['orders_count'];
        });

        $this->servicesData = $servicesData;

        $this->dispatch('initModal');
    }

    public function render()
    {
        return view('livewire.dashboard.categories.show');
    }
}
