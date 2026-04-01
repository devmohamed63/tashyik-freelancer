<?php

namespace App\Livewire\Dashboard;

use App\Models\City;
use App\Utils\Livewire\DataTable;
use App\Utils\Livewire\Table\Button;
use App\Utils\Livewire\Table\Column;
use App\Utils\Livewire\Table\Modal;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\On;
use Livewire\WithPagination;

class CitiesTable extends DataTable
{
    use WithPagination;

    protected $modelClass = City::class;

    public bool $draggableItems = true;

    public array|null $searchableColumns = [
        'name',
    ];

    public function mount()
    {
        $this->authorize('viewAny', City::class);
    }

    /**
     * Table Query Builder
     */
    protected function builder(): Builder
    {
        return City::query()->select([
            'id',
            'name',
            'item_order',
        ]);
    }

    public function create()
    {
        $this->dispatch('showModal', ['id' => 'createResultModal']);
    }

    public function edit($id)
    {
        $this->dispatch('edit-result', $id);

        $this->dispatch('showModal', ['id' => 'editResultModal']);
    }

    protected function columns(): Collection|null
    {
        return new Collection([

            Column::name('check')
                ->checkbox(),

            Column::name('name')
                ->sortable(),

            Column::name('edit', __('ui.edit'))
                ->action()
                ->view('components.dashboard.tables.buttons.edit')
                ->authorize(fn($city) => Gate::allows('update', $city))
                ->wireAction('edit'),

            Column::name('delete', __('ui.delete'))
                ->action()
                ->wireAction('delete')
                ->view('components.dashboard.tables.buttons.delete')
                ->authorize(fn($city) => Gate::allows('delete', $city)),

        ]);
    }

    protected function buttons(): Collection|null
    {
        return new Collection([

            Button::name(__('ui.add_city'))
                ->wireAction('create')
                ->view('components.dashboard.tables.buttons.add'),

            Button::name('delete')
                ->type('delete')
                ->view('components.dashboard.tables.buttons.delete'),

        ]);
    }

    protected function dropdowns(): Collection|null
    {
        return new Collection([
            //
        ]);
    }

    protected function modals(): Collection|null
    {
        return new Collection([

            Modal::id('createResultModal')
                ->view('dashboard.cities.create'),

            Modal::id('editResultModal')
                ->view('dashboard.cities.edit'),

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
