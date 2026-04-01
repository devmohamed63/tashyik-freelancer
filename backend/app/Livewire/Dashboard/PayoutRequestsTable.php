<?php

namespace App\Livewire\Dashboard;

use App\Models\PayoutRequest;
use App\Models\User;
use App\Utils\Livewire\DataTable;
use App\Utils\Livewire\Table\Button;
use App\Utils\Livewire\Table\Column;
use App\Utils\Livewire\Table\Modal;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\On;
use Livewire\WithPagination;

class PayoutRequestsTable extends DataTable
{
    use WithPagination;

    protected $modelClass = PayoutRequest::class;

    public function mount()
    {
        $this->authorize('viewAny', User::class);
    }

    /**
     * Table Query Builder
     */
    protected function builder(): Builder
    {
        return PayoutRequest::query()->select([
            'id',
            'service_provider_id',
            'created_at',
        ])->with('serviceProvider:id,name,balance');
    }

    public function show($id)
    {
        $this->dispatch('show-result', $id);

        $this->dispatch('showModal', ['id' => 'showResultModal']);
    }

    protected function columns(): Collection|null
    {
        return new Collection([

            Column::name('check')
                ->checkbox()
                ->hidden(Gate::denies('deleteAny', User::class)),

            Column::name('name')
                ->relation('serviceProvider', 'name'),

            Column::name('balance', __('ui.balance'))
                ->customValue(fn($payoutRequest) => $payoutRequest->serviceProvider?->printBalance()),

            Column::name('created_at', __('ui.requested_at'))
                ->sortable()
                ->dateFormat(),

            Column::name('show', __('ui.show'))
                ->action()
                ->view('components.dashboard.tables.buttons.show')
                ->wireAction('show'),

            Column::name('delete', __('ui.delete'))
                ->action()
                ->wireAction('delete')
                ->view('components.dashboard.tables.buttons.delete'),

        ]);
    }

    protected function buttons(): Collection|null
    {
        return new Collection([

            Button::name('delete')
                ->type('delete')
                ->view('components.dashboard.tables.buttons.delete'),

        ]);
    }

    protected function dropdowns(): Collection|null
    {
        return new Collection([]);
    }

    protected function modals(): Collection|null
    {
        return new Collection([

            Modal::id('showResultModal')
                ->view('dashboard.users.show-payout-request'),

        ]);
    }

    #[On('refreshTable')]
    public function render()
    {
        return view('livewire.dashboard.general-table', [
            'results' => $this->getResults(),
            'fields' => $this->getFields(),
            'buttons' => $this->getButtons(),
            'dropdowns' => $this->getDropdowns(),
            'modals' => $this->getModals(),
        ]);
    }
}
