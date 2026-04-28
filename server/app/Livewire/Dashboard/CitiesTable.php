<?php

namespace App\Livewire\Dashboard;

use App\Models\City;
use App\Models\Order;
use App\Models\User;
use App\Utils\Livewire\DataTable;
use App\Utils\Livewire\Table\Button;
use App\Utils\Livewire\Table\Column;
use App\Utils\Livewire\Table\Dropdown;
use App\Utils\Livewire\Table\DropdownChild;
use App\Utils\Livewire\Table\Modal;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\On;
use Livewire\Attributes\Url;
use Livewire\WithPagination;

class CitiesTable extends DataTable
{
    use WithPagination;

    protected $modelClass = City::class;

    public bool $draggableItems = true;

    public array|null $searchableColumns = [
        'name',
    ];

    #[Url(as: 'sort')]
    public $sortByFilters = 'latest';

    #[Url(as: 'activity')]
    public $activityFilter = null;

    #[Url(as: 'coverage')]
    public $coverageFilter = null;

    #[Url(as: 'revenue_tier')]
    public $revenueTierFilter = null;

    public function mount()
    {
        $this->authorize('viewAny', City::class);
    }

    /**
     * Table Query Builder
     */
    protected function builder(): Builder
    {
        // ── Subqueries for orders & revenue ──────────────────
        // Match orders where provider is in city OR customer is in city
        $cityProviderOrCustomer = "(`service_provider_id` IN (SELECT `id` FROM `users` WHERE `type` = 'service-provider' AND `city_id` = `cities`.`id` AND `users`.`deleted_at` IS NULL) OR `customer_id` IN (SELECT `id` FROM `users` WHERE `city_id` = `cities`.`id` AND `users`.`deleted_at` IS NULL))";

        $ordersCountSub = Order::query()
            ->selectRaw('COUNT(*)')
            ->whereRaw($cityProviderOrCustomer);

        $completedOrdersSub = Order::query()
            ->selectRaw('COUNT(*)')
            ->where('status', Order::COMPLETED_STATUS)
            ->whereRaw($cityProviderOrCustomer);

        $revenueSub = Order::query()
            ->selectRaw('COALESCE(SUM(subtotal), 0)')
            ->where('status', Order::COMPLETED_STATUS)
            ->whereRaw($cityProviderOrCustomer);

        $query = City::query()
            ->select([
                'id',
                'name',
                'item_order',
            ])
            ->withCount('serviceProviders')
            ->withCount(['users' => fn($q) => $q->where('type', User::USER_ACCOUNT_TYPE)])
            ->selectSub($ordersCountSub, 'orders_count')
            ->selectSub($completedOrdersSub, 'completed_orders_count')
            ->selectSub($revenueSub, 'revenue');

        // ── Sorting ──────────────────────────────────────────
        switch ($this->sortByFilters) {
            case 'most_providers':
                $query->orderByDesc('service_providers_count');
                break;
            case 'least_providers':
                $query->orderBy('service_providers_count', 'asc');
                break;
            case 'most_orders':
                $query->orderByDesc('orders_count');
                break;
            case 'most_revenue':
                $query->orderByDesc('revenue');
                break;
            case 'most_users':
                $query->orderByDesc('users_count');
                break;
        }

        // ── Activity Filter ──────────────────────────────────
        if ($this->activityFilter) {
            switch ($this->activityFilter) {
                case 'active':
                    $query->having('orders_count', '>', 0);
                    break;
                case 'inactive':
                    $query->having('orders_count', '=', 0);
                    break;
                case 'high':
                    $query->having('orders_count', '>=', 10);
                    break;
            }
        }

        // ── Provider Coverage Filter ─────────────────────────
        if ($this->coverageFilter) {
            switch ($this->coverageFilter) {
                case 'none':
                    $query->having('service_providers_count', '=', 0);
                    break;
                case 'low':
                    $query->having('service_providers_count', '>=', 1)
                          ->having('service_providers_count', '<=', 5);
                    break;
                case 'medium':
                    $query->having('service_providers_count', '>=', 6)
                          ->having('service_providers_count', '<=', 20);
                    break;
                case 'good':
                    $query->having('service_providers_count', '>=', 20);
                    break;
            }
        }

        // ── Revenue Tier Filter ──────────────────────────────
        if ($this->revenueTierFilter) {
            switch ($this->revenueTierFilter) {
                case 'none':
                    $query->having('revenue', '<=', 0);
                    break;
                case 'low':
                    $query->having('revenue', '>', 0)->having('revenue', '<', 1000);
                    break;
                case 'medium':
                    $query->having('revenue', '>=', 1000)->having('revenue', '<', 10000);
                    break;
                case 'high':
                    $query->having('revenue', '>=', 10000);
                    break;
            }
        }

        return $query;
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

    public function show($id)
    {
        return redirect()->route('dashboard.cities.show', $id);
    }

    protected function columns(): Collection|null
    {
        $decimal = config('app.decimal_places');

        return new Collection([

            Column::name('check')
                ->checkbox(),

            Column::name('name')
                ->sortable(),

            Column::name('service_providers_count', __('ui.service_providers'))
                ->customValue(fn($city) => number_format($city->service_providers_count))
                ->sortable(),

            Column::name('users_count', __('ui.customers'))
                ->customValue(fn($city) => number_format($city->users_count))
                ->sortable(),

            Column::name('orders_count', __('ui.orders'))
                ->customValue(fn($city) => number_format($city->orders_count))
                ->sortable(),

            Column::name('completed_orders_count', __('ui.completed_orders'))
                ->customValue(fn($city) => number_format($city->completed_orders_count))
                ->sortable(),

            Column::name('revenue', __('ui.revenue'))
                ->customValue(fn($city) => number_format($city->revenue ?? 0, $decimal) . ' ' . __('ui.currency'))
                ->sortable(),

            Column::name('show', __('ui.show'))
                ->action()
                ->view('components.dashboard.tables.buttons.show')
                ->wireAction('show'),

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
        // ── Sort Dropdown ────────────────────────────────────
        $sortLabels = [
            'latest'         => __('ui.all'),
            'most_providers' => __('ui.most_providers'),
            'least_providers'=> __('ui.least_providers'),
            'most_orders'    => __('ui.most_orders'),
            'most_revenue'   => __('ui.highest_revenue'),
            'most_users'     => __('ui.most_users'),
        ];

        $sortChildren = [];
        foreach ($sortLabels as $key => $label) {
            $sortChildren[] = DropdownChild::name($label)
                ->wireAction("\$set('sortByFilters', '$key')");
        }

        // ── Activity Filter ──────────────────────────────────
        $activityLabels = [
            null     => __('ui.all'),
            'active' => __('ui.active_cities'),
            'inactive' => __('ui.inactive_cities'),
            'high'   => __('ui.high_activity'),
        ];

        $activityChildren = [];
        foreach ($activityLabels as $key => $label) {
            $val = $key ? "'$key'" : 'null';
            $activityChildren[] = DropdownChild::name($label)
                ->wireAction("\$set('activityFilter', $val)");
        }

        // ── Coverage Filter ──────────────────────────────────
        $coverageLabels = [
            null     => __('ui.all'),
            'none'   => __('ui.no_providers'),
            'low'    => __('ui.low_coverage'),
            'medium' => __('ui.medium_coverage'),
            'good'   => __('ui.good_coverage'),
        ];

        $coverageChildren = [];
        foreach ($coverageLabels as $key => $label) {
            $val = $key ? "'$key'" : 'null';
            $coverageChildren[] = DropdownChild::name($label)
                ->wireAction("\$set('coverageFilter', $val)");
        }

        // ── Revenue Tier Filter ──────────────────────────────
        $revenueLabels = [
            null     => __('ui.all'),
            'none'   => __('ui.no_revenue'),
            'low'    => __('ui.low_revenue'),
            'medium' => __('ui.medium_revenue'),
            'high'   => __('ui.high_revenue'),
        ];

        $revenueChildren = [];
        foreach ($revenueLabels as $key => $label) {
            $val = $key ? "'$key'" : 'null';
            $revenueChildren[] = DropdownChild::name($label)
                ->wireAction("\$set('revenueTierFilter', $val)");
        }

        // ── Current labels ───────────────────────────────────
        $currentSort = $sortLabels[$this->sortByFilters] ?? __('ui.all');
        $currentActivity = $activityLabels[$this->activityFilter] ?? __('ui.city_activity');
        $currentCoverage = $coverageLabels[$this->coverageFilter] ?? __('ui.provider_coverage');
        $currentRevenue = $revenueLabels[$this->revenueTierFilter] ?? __('ui.revenue_tier');

        return new Collection([
            Dropdown::name(__('ui.sort') . ': ' . $currentSort, __('ui.sort') . ': ' . $currentSort)
                ->id('sortByFilters')
                ->children($sortChildren),

            Dropdown::name($currentActivity, $currentActivity)
                ->id('activityFilter')
                ->children($activityChildren),

            Dropdown::name($currentCoverage, $currentCoverage)
                ->id('coverageFilter')
                ->children($coverageChildren),

            Dropdown::name($currentRevenue, $currentRevenue)
                ->id('revenueTierFilter')
                ->children($revenueChildren),
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
