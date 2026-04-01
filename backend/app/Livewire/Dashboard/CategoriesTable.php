<?php

namespace App\Livewire\Dashboard;

use App\Models\Category;
use App\Utils\Livewire\DataTable;
use App\Utils\Livewire\Table\Button;
use App\Utils\Livewire\Table\Column;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Gate;
use Livewire\WithPagination;

class CategoriesTable extends DataTable
{
    use WithPagination;

    protected $modelClass = Category::class;

    public bool $draggableItems = true;

    public array|null $searchableColumns = [
        'name',
    ];

    public function mount()
    {
        $this->authorize('viewAny', Category::class);
    }

    /**
     * Table Query Builder
     */
    protected function builder(): Builder
    {
        return Category::query()->select([
            'id',
            'category_id',
            'name',
            'item_order',
        ])->isParent();
    }

    protected function columns(): Collection|null
    {
        return new Collection([

            Column::name('check')
                ->checkbox()
                ->hidden(Gate::denies('deleteAny', Category::class)),

            Column::name('name')
                ->customValue(fn($category) => "($category->item_order) $category->name")
                ->sortable(),

            Column::name('show', __('ui.show'))
                ->action()
                ->url(fn($category) => route('dashboard.categories.show', ['category' => $category->id]))
                ->view('components.dashboard.tables.buttons.show'),

            Column::name('edit', __('ui.edit'))
                ->action()
                ->url(fn($category) => route('dashboard.categories.edit', ['category' => $category->id]))
                ->view('components.dashboard.tables.buttons.edit')
                ->authorize(fn($category) => Gate::allows('update', $category))
                ->hidden(Gate::denies('updateAny', Category::class)),

            Column::name('delete', __('ui.delete'))
                ->action()
                ->wireAction('delete')
                ->view('components.dashboard.tables.buttons.delete')
                ->authorize(fn($category) => Gate::allows('delete', $category))
                ->hidden(Gate::denies('deleteAny', Category::class)),

        ]);
    }

    protected function buttons(): Collection|null
    {
        return new Collection([

            Button::name(__('ui.add_category'))
                ->url(route('dashboard.categories.create'))
                ->view('components.dashboard.tables.buttons.add')
                ->hidden(Gate::denies('create', Category::class)),

            Button::name('delete')
                ->type('delete')
                ->view('components.dashboard.tables.buttons.delete')
                ->hidden(Gate::denies('deleteAny', Category::class)),

        ]);
    }

    protected function dropdowns(): Collection|null
    {
        return new Collection([]);
    }

    protected function modals(): Collection|null
    {
        return new Collection([]);
    }

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
