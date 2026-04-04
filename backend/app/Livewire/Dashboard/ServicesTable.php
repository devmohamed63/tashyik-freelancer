<?php

namespace App\Livewire\Dashboard;

use App\Models\Service;
use App\Utils\Livewire\DataTable;
use App\Utils\Livewire\Table\Button;
use App\Utils\Livewire\Table\Column;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Url;
use Livewire\WithPagination;
use Masterminds\HTML5;
use App\Utils\Livewire\Table\Dropdown;
use App\Utils\Livewire\Table\Modal;

class ServicesTable extends DataTable
{
    use WithPagination;

    protected $modelClass = Service::class;

    public bool $draggableItems = true;

    public array|null $searchableColumns = [
        'name',
    ];

    #[Url(as: 'category')]
    public $categoryFilter = 'all';

    #[Url(as: 'sort')]
    public $sortByFilters = 'latest';

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
            'category:id,category_id,name',
            'category.parent',
            'promotion'
        ])
        ->when($this->categoryFilter !== 'all', function (Builder $query) {
            $query->whereHas('category', function (Builder $q) {
                $q->where('id', $this->categoryFilter)
                  ->orWhere('category_id', $this->categoryFilter);
            });
        })
        ->withCount('orders')
        ->withSum(['orders as revenue' => fn($q) => $q->completed()], 'subtotal')
        ->when($this->sortByFilters === 'most_orders', function ($query) {
            $query->orderBy('orders_count', 'desc');
        })
        ->when($this->sortByFilters === 'least_orders', function ($query) {
            $query->orderBy('orders_count', 'asc');
        });
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

            Column::name('orders_count', __('ui.orders'))
                ->customValue(fn($service) => number_format($service->orders_count))
                ->sortable(),

            Column::name('revenue', __('ui.revenue'))
                ->customValue(fn($service) => number_format($service->revenue ?? 0) . ' ' . __('ui.currency'))
                ->sortable(),

            Column::name('show', __('ui.show'))
                ->action()
                ->view('components.dashboard.tables.buttons.show')
                ->wireAction('show'),

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
        $categories = \App\Models\Category::isParent()->get(['id', 'name']);
        
        $categoryChildren = [
             \App\Utils\Livewire\Table\DropdownChild::name(__('ui.all'))
                 ->wireAction('$set("categoryFilter", "all")')
        ];

        $currentCategoryName = __('ui.category');
        foreach ($categories as $category) {
             if ($this->categoryFilter == $category->id) {
                 $currentCategoryName = $category->name;
             }
             $categoryChildren[] = \App\Utils\Livewire\Table\DropdownChild::name($category->name)
                 ->wireAction('$set("categoryFilter", "' . $category->id . '")');
        }

        $sortChildren = [
            \App\Utils\Livewire\Table\DropdownChild::name('الافتراضي')
                 ->wireAction('$set("sortByFilters", "latest")'),
            \App\Utils\Livewire\Table\DropdownChild::name('الأعلى طلباً')
                 ->wireAction('$set("sortByFilters", "most_orders")'),
            \App\Utils\Livewire\Table\DropdownChild::name('الأقل طلباً')
                 ->wireAction('$set("sortByFilters", "least_orders")'),
        ];
        
        $currentSortName = 'الافتراضي';
        if ($this->sortByFilters === 'most_orders') $currentSortName = 'الأعلى طلباً';
        if ($this->sortByFilters === 'least_orders') $currentSortName = 'الأقل طلباً';

        return new Collection([
             \App\Utils\Livewire\Table\Dropdown::name($currentCategoryName)
                 ->id('categoryFilter')
                 ->children($categoryChildren),

             \App\Utils\Livewire\Table\Dropdown::name('ترتيب: ' . $currentSortName)
                 ->id('sortByFilters')
                 ->children($sortChildren),
        ]);
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
                 ->view('dashboard.services.show')
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
