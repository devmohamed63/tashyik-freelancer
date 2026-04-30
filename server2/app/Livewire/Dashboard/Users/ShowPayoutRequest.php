<?php

namespace App\Livewire\Dashboard\Users;

use App\Events\NewBankTransfer;
use App\Models\Notification;
use App\Models\PayoutRequest;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\On;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\Features\SupportPagination\WithoutUrlPagination;
use Livewire\WithPagination;

class ShowPayoutRequest extends Component
{
    use WithoutUrlPagination, WithPagination;

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
        if (! $this->serviceProvider) {
            return;
        }

        $sp = $this->serviceProvider;
        $amount = (float) $sp->balance;
        $payoutRequestId = $sp->payoutRequest?->id;

        DB::transaction(function () use ($sp): void {
            $sp->refresh();
            $sp->update(['balance' => 0]);
            $sp->payoutRequest?->delete();
        });

        $sp->refresh();
        NewBankTransfer::dispatch($sp, $amount, $payoutRequestId);

        $this->dispatch('refreshTable');
    }

    public function markBankTransferRecorded(int $invoiceId): void
    {
        if (! $this->serviceProvider) {
            return;
        }

        $invoice = $this->serviceProvider->invoices()
            ->where('id', $invoiceId)
            ->where('type', \App\Models\Invoice::BANK_TRANSFER_TYPE)
            ->first();

        if (! $invoice || $invoice->recorded_in_daftra) {
            return;
        }

        $invoice->update([
            'recorded_in_daftra' => true,
            'recorded_in_daftra_at' => now(),
            'recorded_in_daftra_by' => auth()->id(),
        ]);

        $pendingNotification = Notification::query()
            ->where('type', 'bank-transfer-daftra-pending')
            ->orderByDesc('id')
            ->get()
            ->first(function (Notification $notification) use ($invoice) {
                $payload = json_decode((string) $notification->data, true);

                return (int) ($payload['invoice_id'] ?? 0) === (int) $invoice->id;
            });

        if ($pendingNotification) {
            $payload = json_decode((string) $pendingNotification->data, true) ?: [];
            $payload['recorded_in_daftra'] = true;
            $payload['recorded_in_daftra_at'] = now()->toDateTimeString();
            $payload['recorded_in_daftra_by'] = auth()->id();

            $pendingNotification->update([
                'data' => json_encode($payload),
            ]);
        }
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
                    'recorded_in_daftra',
                    'created_at',
                ]),
        ]);
    }
}
