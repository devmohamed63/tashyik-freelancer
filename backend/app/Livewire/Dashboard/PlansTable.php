<?php

namespace App\Livewire\Dashboard;

use App\Models\Plan;
use App\Utils\Livewire\DataTable;
use App\Utils\Livewire\Table\Button;
use App\Utils\Livewire\Table\Column;
use App\Utils\Livewire\Table\Modal;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Livewire\Attributes\On;
use Livewire\WithPagination;

class PlansTable extends DataTable
{
    use WithPagination;

    protected $modelClass = Plan::class;

    public array|null $searchableColumns = [
        'name',
    ];

    public function mount()
    {
        $this->authorize('manage plans');
    }

    /**
     * Table Query Builder
     */
    protected function builder(): Builder
    {
        return Plan::query()->select([
            'id',
            'name',
            'target_group',
            'price',
            'duration_in_days',
        ]);
    }

    public function create()
    {
        $this->dispatch('showModal', ['id' => 'createResultModal']);
    }

    public function edit($id)
    {
        $this->dispatch('editResult', id: $id)->to(\App\Livewire\Dashboard\Plans\Edit::class);
        $this->dispatch('showModal', ['id' => 'editResultModal']);
    }

    protected function columns(): Collection|null
    {
        return new Collection([

            Column::name('check')
                ->checkbox(),

            Column::name('name')
                ->sortable(),

            Column::name('target_group')
                ->sortable()
                ->customValue(fn($plan) => __('ui.' . $plan->target_group)),

            Column::name('price')
                ->sortable()
                ->customValue(fn($plan) => number_format($plan->price, config('app.decimal_places')) . ' ' . __('ui.currency')),

            Column::name('duration_in_days', __('validation.attributes.duration_in_months'))
                ->sortable()
                ->customValue(fn($plan) => $plan->duration_in_months),

            Column::name('edit', __('ui.edit'))
                ->action()
                ->wireAction('edit')
                ->view('components.dashboard.tables.buttons.edit'),

            Column::name('delete', __('ui.delete'))
                ->action()
                ->wireAction('delete')
                ->view('components.dashboard.tables.buttons.delete'),

        ]);
    }

    protected function buttons(): Collection|null
    {
        return new Collection([

            Button::name(__('ui.add_plan'))
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
                ->view('dashboard.plans.create'),

            Modal::id('editResultModal')
                ->view('dashboard.plans.edit'),

        ]);
    }

    #[On('resetPage')]
    public function resetTablePage()
    {
        $this->resetPage();
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
