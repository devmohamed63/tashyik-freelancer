<?php

namespace App\Livewire\Dashboard;

use App\Models\Promotion;
use App\Utils\Livewire\DataTable;
use App\Utils\Livewire\Table\Button;
use App\Utils\Livewire\Table\Column;
use App\Utils\Livewire\Table\Modal;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Livewire\Attributes\On;
use Livewire\WithPagination;

class PromotionsTable extends DataTable
{
    use WithPagination;

    protected $modelClass = Promotion::class;

    public array|null $searchableColumns = [
        'name',
    ];

    public function mount()
    {
        $this->authorize('manage promotions');
    }

    /**
     * Table Query Builder
     */
    protected function builder(): Builder
    {
        return Promotion::query()->select([
            'id',
            'name',
            'type',
            'value',
        ]);
    }

    public function create()
    {
        $this->dispatch('showModal', ['id' => 'createResultModal']);
    }

    protected function columns(): Collection|null
    {
        return new Collection([

            Column::name('check')
                ->checkbox(),

            Column::name('name')
                ->sortable(),

            Column::name('value')
                ->sortable()
                ->customValue(fn($promotion) => $promotion->getValue()),

            Column::name('delete', __('ui.delete'))
                ->action()
                ->wireAction('delete')
                ->view('components.dashboard.tables.buttons.delete'),

        ]);
    }

    protected function buttons(): Collection|null
    {
        return new Collection([

            Button::name(__('ui.create_promotion'))
                ->wireAction('create')
                ->view('components.dashboard.tables.buttons.add'),

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

            Modal::id('createResultModal')
                ->view('dashboard.promotions.create'),

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
