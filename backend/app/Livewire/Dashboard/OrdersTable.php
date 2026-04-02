<?php

namespace App\Livewire\Dashboard;

use App\Models\Order;
use App\Utils\Livewire\DataTable;
use App\Utils\Livewire\Table\Button;
use App\Utils\Livewire\Table\Column;
use App\Utils\Livewire\Table\Modal;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Livewire\Attributes\On;
use Livewire\Attributes\Url;
use Livewire\WithPagination;

class OrdersTable extends DataTable
{
    use WithPagination;

    protected $modelClass = Order::class;

    public bool $tableHasStatus = true;

    public array $availableStatusTypes = Order::AVAILABLE_STATUS_TYPES;

    public array|null $searchableColumns = [
        'id',
    ];

    #[Url]
    public string|null $categoryFilter = null;

    #[Url]
    public string|null $serviceFilter = null;

    public function mount()
    {
        $this->authorize('manage orders');
    }

    /**
     * Table Query Builder
     */
    protected function builder(): Builder
    {
        $query = Order::query()->select([
            'id',
            'service_id',
            'subtotal',
            'status',
            'created_at',
            'updated_at',
        ])->with([
            'service:id,name'
        ]);

        if ($this->statusFilter) {
            switch ($this->statusFilter) {
                case Order::NEW_STATUS:
                    $query->isNew();
                    break;

                case Order::SERVICE_PROVIDER_ON_THE_WAY:
                    $query->started();
                    break;

                case Order::SERVICE_PROVIDER_ARRIVED:
                    $query->started();
                    break;

                case Order::STARTED_STATUS:
                    $query->started();
                    break;

                case Order::COMPLETED_STATUS:
                    $query->completed();
                    break;
            }
        }

        if ($this->categoryFilter) {
            $query->whereHas('service', function ($q) {
                $q->where('category_id', $this->categoryFilter);
            });
        }

        if ($this->serviceFilter) {
            $query->where('service_id', $this->serviceFilter);
        }

        return $query;
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
                ->checkbox(),

            Column::name('id', __('ui.order_id'))
                ->sortable(),

            Column::name('service', __('ui.service'))
                ->relation('service', 'name'),

            Column::name('price')
                ->customValue(fn($order) => $order->subtotal . ' ' . __('ui.currency'))
                ->sortable(),

            Column::name('status', __('ui.status'))
                ->callback(function ($order) {
                    switch ($order->status) {
                        case Order::NEW_STATUS:
                            $badge = view('components.dashboard.badges.primary', ['name' => __('ui.' . $order->status)]);
                            break;

                        case Order::SERVICE_PROVIDER_ON_THE_WAY:
                            $badge = view('components.dashboard.badges.light', ['name' => __('ui.' . $order->status)]);
                            break;

                        case Order::SERVICE_PROVIDER_ARRIVED:
                            $badge = view('components.dashboard.badges.light', ['name' => __('ui.' . $order->status)]);
                            break;

                        case Order::STARTED_STATUS:
                            $badge = view('components.dashboard.badges.warning', ['name' => __('ui.' . $order->status)]);
                            break;

                        case Order::COMPLETED_STATUS:
                            $badge = view('components.dashboard.badges.success', ['name' => __('ui.' . $order->status)]);
                            break;
                    }

                    return $badge ?? '';
                }),

            Column::name('created_at', __('ui.created_at'))
                ->sortable()
                ->dateFormat(),

            Column::name('updated_at', __('ui.last_update'))
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
        $categories = \App\Models\Category::isParent()->get(['id', 'name']);
        
        $categoryChildren = [
             \App\Utils\Livewire\Table\DropdownChild::name(__('ui.all'))
                 ->wireAction('$set("categoryFilter", null)')
        ];

        $currentCategoryName = __('ui.category');
        foreach ($categories as $category) {
             if ($this->categoryFilter == $category->id) {
                 $currentCategoryName = $category->name;
             }
             $categoryChildren[] = \App\Utils\Livewire\Table\DropdownChild::name($category->name)
                 ->wireAction('$set("categoryFilter", ' . $category->id . ')');
        }

        $services = \App\Models\Service::get(['id', 'name']);
        
        $serviceChildren = [
             \App\Utils\Livewire\Table\DropdownChild::name(__('ui.all'))
                 ->wireAction('$set("serviceFilter", null)')
        ];

        $currentServiceName = __('ui.service');
        foreach ($services as $service) {
             if ($this->serviceFilter == $service->id) {
                 $currentServiceName = $service->name;
             }
             $serviceChildren[] = \App\Utils\Livewire\Table\DropdownChild::name($service->name)
                 ->wireAction('$set("serviceFilter", ' . $service->id . ')');
        }

        return new Collection([
             \App\Utils\Livewire\Table\Dropdown::name($currentCategoryName)
                 ->id('categoryFilter')
                 ->children($categoryChildren),
                 
             \App\Utils\Livewire\Table\Dropdown::name($currentServiceName)
                 ->id('serviceFilter')
                 ->children($serviceChildren),
        ]);
    }

    protected function modals(): Collection|null
    {
        return new Collection([

            Modal::id('showResultModal')
                ->view('dashboard.orders.show'),

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
