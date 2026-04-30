<?php

namespace App\Livewire\Dashboard;

use App\Models\Coupon;
use App\Utils\Livewire\DataTable;
use App\Utils\Livewire\Table\Button;
use App\Utils\Livewire\Table\Column;
use App\Utils\Livewire\Table\Modal;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Livewire\Attributes\On;
use Livewire\WithPagination;

class CouponsTable extends DataTable
{
    use WithPagination;

    protected $modelClass = Coupon::class;

    public array|null $searchableColumns = [
        'name',
        'code',
    ];

    public function mount()
    {
        $this->authorize('manage coupons');
    }

    /**
     * Table Query Builder
     */
    protected function builder(): Builder
    {
        return Coupon::query()->select([
            'id',
            'name',
            'welcome',
            'value',
            'type',
            'code',
            'usage_times',
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

            Column::name('welcome', __('ui.welcome_coupon'))
                ->customValue(fn($coupon) => $coupon->welcome ? __('ui.yes') : __('ui.no'))
                ->sortable(),

            Column::name('value')
                ->sortable()
                ->customValue(fn($coupon) => $coupon->getValue()),

            Column::name('code')
                ->sortable(),

            Column::name('usage_times', __('ui.usage_times'))
                ->sortable(),

            Column::name('delete', __('ui.delete'))
                ->action()
                ->wireAction('delete')
                ->view('components.dashboard.tables.buttons.delete'),

        ]);
    }

    protected function buttons(): Collection|null
    {
        return new Collection([

            Button::name(__('ui.create_coupon'))
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
                ->view('dashboard.coupons.create'),

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
