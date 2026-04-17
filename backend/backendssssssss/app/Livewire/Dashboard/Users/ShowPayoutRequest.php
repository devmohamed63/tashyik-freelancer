<?php

namespace App\Livewire\Dashboard\Users;

use App\Events\NewBankTransfer;
use App\Models\PayoutRequest;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Livewire\Attributes\On;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\Features\SupportPagination\WithoutUrlPagination;
use Livewire\WithPagination;

class ShowPayoutRequest extends Component
{
    use WithPagination, WithoutUrlPagination;

    public User|Collection|null $serviceProvider = null;

    #[Url]
    public $showResult = '';

    public function mount()
    {
        $this->authorize('viewAny', User::class);

        if ($this->showResult) {
            $this->dispatch('show-result', $this->showResult);

            $this->dispatch('showModal', ['id' => 'showResultModal']);

            $this->showResult = null;
        }
    }

    #[On('show-result')]
    public function getResult($id)
    {
        $payoutRequest = PayoutRequest::findOrFail($id);
        $this->serviceProvider = $payoutRequest->serviceProvider;
    }

    public function zeroThebalance()
    {
        $amount = $this->serviceProvider?->balance;

        $this->serviceProvider?->update(['balance' => 0]);

        $this->serviceProvider?->payoutRequest->delete();

        NewBankTransfer::dispatch($this->serviceProvider, $amount);

        $this->dispatch('refreshTable');
    }

    public function render()
    {
        return view('livewire.dashboard.users.show-payout-request', [
            'invoices' => $this->serviceProvider?->invoices()
                ->orderByDesc('id')
                ->paginate(25, [
                    'id',
                    'target_id',
                    'service_provider_id',
                    'type',
                    'action',
                    'amount',
                    'created_at',
                ])
        ]);
    }
}
