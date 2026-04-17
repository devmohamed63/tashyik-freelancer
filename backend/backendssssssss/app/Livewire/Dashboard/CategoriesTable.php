<?php

namespace App\Livewire\Dashboard;

use App\Models\Category;
use App\Utils\Livewire\DataTable;
use App\Utils\Livewire\Table\Button;
use App\Utils\Livewire\Table\Column;
use App\Utils\Livewire\Table\Modal;
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
        ])
        ->isParent()
        ->withCount('children')
        ->withCount('childrenServices')
        ->withCount('serviceProviders')
        ->addSelect([
            'revenue' => \App\Models\Order::selectRaw('COALESCE(sum(subtotal), 0)')
                ->where('orders.status', \App\Models\Order::COMPLETED_STATUS)
                ->whereExists(function (\Illuminate\Database\Query\Builder $query) {
                    $query->select(\Illuminate\Support\Facades\DB::raw(1))
                          ->from('services')
                          ->whereColumn('services.id', 'orders.service_id')
                          ->join('categories as subcats', 'subcats.id', '=', 'services.category_id')
                          ->whereColumn('subcats.category_id', 'categories.id');
                })
        ]);
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

            Column::name('children_count', __('ui.subcategories'))
                ->customValue(fn($category) => number_format($category->children_count))
                ->sortable(),

            Column::name('children_services_count', __('ui.services'))
                ->customValue(fn($category) => number_format($category->children_services_count))
                ->sortable(),

            Column::name('service_providers_count', __('ui.service_providers'))
                ->customValue(fn($category) => number_format($category->service_providers_count))
                ->sortable(),

            Column::name('revenue', __('ui.revenue'))
                ->customValue(fn($category) => number_format($category->revenue ?? 0, config('app.decimal_places')) . ' ' . __('ui.currency'))
                ->sortable(),

            Column::name('show', __('ui.show'))
                ->action()
                ->view('components.dashboard.tables.buttons.show')
                ->wireAction('show'),

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

    public function show($id)
    {
        $this->dispatch('show-result', $id);
        $this->dispatch('showModal', ['id' => 'showResultModal']);
    }

    protected function modals(): Collection|null
    {
        return new Collection([
            Modal::id('showResultModal')
                 ->view('dashboard.categories.show') // wait, it's a wire component? the view is 'livewire.dashboard.categories.show'? Actually for Cities, how was it done?
        ]);
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
