<?php

namespace App\Livewire\Dashboard\Orders;

use App\Models\Order;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\Attributes\Url;

class Show extends Component
{
    public $order;

    #[Url]
    public $showResult = '';

    public function mount()
    {
        $this->authorize('viewAny', Order::class);

        if ($this->showResult) {
            $this->dispatch('show-result', $this->showResult);

            $this->dispatch('showModal', ['id' => 'showResultModal']);

            $this->showResult = null;
        }
    }

    #[On('show-result')]
    public function getResult($id)
    {
        $this->order = Order::findOrFail($id);
    }

    public function render()
    {
        return view('livewire.dashboard.orders.show');
    }
}
