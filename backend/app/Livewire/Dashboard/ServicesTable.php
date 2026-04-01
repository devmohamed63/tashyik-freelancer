<?php

namespace App\Livewire\Dashboard;

use App\Models\Service;
use App\Utils\Livewire\DataTable;
use App\Utils\Livewire\Table\Button;
use App\Utils\Livewire\Table\Column;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Gate;
use Livewire\WithPagination;
use Masterminds\HTML5;

class ServicesTable extends DataTable
{
    use WithPagination;

    protected $modelClass = Service::class;

    public bool $draggableItems = true;

    public array|null $searchableColumns = [
        'name',
    ];

    public function mount()
    {
        $this->authorize('viewAny', Service::class);
    }

    /**
     * Table Query Builder
     */
    protected function builder(): Builder
    {
        return Service::query()->select([
            'id',
            'category_id',
            'promotion_id',
            'name',
            'price',
            'item_order',
        ])->with([
            'category:id,name',
            'promotion'
        ]);
    }

    protected function columns(): Collection|null
    {
        return new Collection([

            Column::name('check')
                ->checkbox()
                ->hidden(Gate::denies('deleteAny', Service::class)),

            Column::name('name')
                ->customValue(fn($service) => "($service->item_order) $service->name")
                ->sortable(),

            Column::name('price')
                ->callback(function ($service) {
                    $price = $service->getPrice();
                    $currency = __('ui.currency');

                    if ($price['has_discount']) {
                        $priceCode = <<<HTML
                        <div class="text-gray-500 text-theme-sm dark:text-gray-400">
                            <span>{$price['after_discount']} $currency</span>
                            <del class="text-xs mx-1">{$price['original']}</del>
                            <span class="text-xs text-green-600">{$price['discount_percintage']}%</span>
                        </div>
                        HTML;
                    } else {
                        $priceCode = <<<HTML
                        <div class="text-gray-500 text-theme-sm dark:text-gray-400">
                            <span>{$price['after_discount']} $currency</span>
                        </div>
                        HTML;
                    }

                    if ($price['original'] == 0) {
                        $label = __('ui.no_price');

                        $priceCode = <<<HTML
                        <div class="text-gray-500 text-theme-sm dark:text-gray-400">
                            <span>$label</span>
                        </div>
                        HTML;
                    }

                    return $priceCode;
                })
                ->sortable(),

            Column::name('category_id', __('ui.category'))
                ->relation('category', 'name'),

            Column::name('show', __('ui.show'))
                ->action()
                ->url(fn($service) => route('dashboard.services.show', ['service' => $service->id]))
                ->view('components.dashboard.tables.buttons.show'),

            Column::name('edit', __('ui.edit'))
                ->action()
                ->url(fn($service) => route('dashboard.services.edit', ['service' => $service->id]))
                ->view('components.dashboard.tables.buttons.edit')
                ->authorize(fn($service) => Gate::allows('update', $service))
                ->hidden(Gate::denies('updateAny', Service::class)),

            Column::name('delete', __('ui.delete'))
                ->action()
                ->wireAction('delete')
                ->view('components.dashboard.tables.buttons.delete')
                ->authorize(fn($service) => Gate::allows('delete', $service))
                ->hidden(Gate::denies('deleteAny', Service::class)),

        ]);
    }

    protected function buttons(): Collection|null
    {
        return new Collection([

            Button::name(__('ui.add_service'))
                ->url(route('dashboard.services.create'))
                ->view('components.dashboard.tables.buttons.add')
                ->hidden(Gate::denies('create', Service::class)),

            Button::name('delete')
                ->type('delete')
                ->view('components.dashboard.tables.buttons.delete')
                ->hidden(Gate::denies('deleteAny', Service::class)),

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
