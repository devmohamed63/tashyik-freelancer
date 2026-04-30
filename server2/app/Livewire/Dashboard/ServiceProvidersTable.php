<?php

namespace App\Livewire\Dashboard;

use App\Models\User;
use App\Utils\ExcelSheet\Column as ExcelSheetColumn;
use App\Utils\ExcelSheet\ExcelSheet;
use App\Utils\Livewire\DataTable;
use App\Utils\Livewire\Table\Button;
use App\Utils\Livewire\Table\Column;
use App\Utils\Livewire\Table\Modal;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\On;
use Livewire\WithPagination;

class ServiceProvidersTable extends DataTable
{
    use WithPagination;

    protected $modelClass = User::class;

    public bool $modelHasMedia = true;

    #[Url]
    public string|null $activityFilter = null;

    #[Url]
    public string|null $cityFilter = null;

    public bool $tableHasTrash = true;

    public bool $tableHasStatus = true;

    public bool $tableHasTypes = true;

    public bool $exportableTable = true;

    public array $availableStatusTypes = User::AVAILABLE_STATUS_TYPES;

    public array $availableTypes = [
        User::INDIVIDUAL_ENTITY_TYPE,
        User::INSTITUTION_ENTITY_TYPE,
        User::COMPANY_ENTITY_TYPE,
    ];

    public array|null $searchableColumns = [
        'name',
        'phone'
    ];

    public function mount()
    {
        $this->authorize('viewAny', User::class);
    }

    /**
     * Table Query Builder
     */
    protected function builder(): Builder
    {
        $query = User::query()->select([
            'id',
            'name',
            'phone',
            'entity_type',
            'status',
            'city_id',
            'balance',
        ])->isServiceProvider()
          ->with('city:id,name')
          ->withCount('serviceProviderOrders')
          ->withCount('members')
          ->withSum(['serviceProviderOrders as revenue' => fn($q) => $q->where('status', \App\Models\Order::COMPLETED_STATUS)], 'subtotal');

        if ($this->statusFilter) {
            switch ($this->statusFilter) {
                case User::PENDING_STATUS:
                    $query->pending();
                    break;

                case User::ACTIVE_STATUS:
                    $query->active();
                    break;

                case User::INACTIVE_STATUS:
                    $query->inactive();
                    break;
            }
        }

        if ($this->cityFilter) {
            $query->where('city_id', $this->cityFilter);
        }

        if ($this->typeFilter) {
            switch ($this->typeFilter) {
                case User::INDIVIDUAL_ENTITY_TYPE:
                    $query->isIndividual();
                    break;

                case User::INSTITUTION_ENTITY_TYPE:
                    $query->isInstitution();
                    break;

                case User::COMPANY_ENTITY_TYPE:
                    $query->isCompany();
                    break;
            }
        }

        if ($this->activityFilter === 'most_active') {
            $query->whereHas('serviceProviderOrders')
                  ->orderByDesc('service_provider_orders_count');
        } elseif ($this->activityFilter === 'no_orders') {
            $query->doesntHave('serviceProviderOrders');
        }

        return $query;
    }

    public function show($id)
    {
        $user = User::findOrFail($id);

        // Redirect institutions/companies to their dedicated full page
        if ($user->isInstitutionOrCompany()) {
            return redirect()->to(route('dashboard.institution.show', $user));
        }

        $this->dispatch('show-result', $id);

        $this->dispatch('showModal', ['id' => 'showResultModal']);
    }

    public function create()
    {
        $this->dispatch('showModal', ['id' => 'createResultModal']);
    }

    protected function columns(): Collection|null
    {
        return new Collection([

            Column::name('check')
                ->checkbox()
                ->hidden(Gate::denies('deleteAny', User::class)),

            Column::name('name')
                ->sortable(),

            Column::name('phone')
                ->sortable(),

            Column::name('entity_type', __('ui.account_type'))
                ->sortable()
                ->callback(function ($user) {
                    switch ($user->entity_type) {
                        case User::INDIVIDUAL_ENTITY_TYPE:
                            $badge = view('components.dashboard.badges.light', ['name' => __('ui.' . User::INDIVIDUAL_ENTITY_TYPE)]);
                            break;

                        case User::INSTITUTION_ENTITY_TYPE:
                            $badge = view('components.dashboard.badges.success', ['name' => __('ui.' . User::INSTITUTION_ENTITY_TYPE)]);
                            break;

                        case User::COMPANY_ENTITY_TYPE:
                            $badge = view('components.dashboard.badges.primary', ['name' => __('ui.' . User::COMPANY_ENTITY_TYPE)]);
                            break;
                    }

                    return $badge;
                }),

            Column::name('status', __('ui.status'))
                ->callback(function ($user) {
                    switch ($user->status) {
                        case User::PENDING_STATUS:
                            $badge = view('components.dashboard.badges.warning', ['name' => __('ui.' . $user->status)]);
                            break;

                        case User::ACTIVE_STATUS:
                            $badge = view('components.dashboard.badges.success', ['name' => __('ui.' . $user->status)]);
                            break;

                        case User::INACTIVE_STATUS:
                            $badge = view('components.dashboard.badges.light', ['name' => __('ui.' . $user->status)]);
                            break;
                    }

                    return $badge ?? '';
                }),

            Column::name('city', __('ui.city'))
                ->relation('city', 'name'),

            Column::name('service_provider_orders_count', __('ui.orders'))
                ->customValue(fn($user) => number_format($user->service_provider_orders_count))
                ->sortable(),

            Column::name('revenue', __('ui.revenue'))
                ->customValue(fn($user) => number_format($user->revenue ?? 0, config('app.decimal_places')) . ' ' . __('ui.currency'))
                ->sortable(),

            Column::name('balance', __('ui.balance'))
                ->customValue(fn($user) => number_format($user->balance ?? 0, config('app.decimal_places')) . ' ' . __('ui.currency'))
                ->sortable(),

            Column::name('members_count', __('ui.members_count'))
                ->customValue(fn($user) => $user->members_count > 0 ? number_format($user->members_count) : '-')
                ->sortable()
                ->hidden(!in_array($this->typeFilter, ['institution', 'company'])),

            Column::name('show', __('ui.show'))
                ->action()
                ->view('components.dashboard.tables.buttons.show')
                ->wireAction('show')
                ->hidden($this->trashMode),

            Column::name('edit', __('ui.edit'))
                ->action()
                ->url(fn($user) => route('dashboard.users.edit', ['user' => $user->id]))
                ->view('components.dashboard.tables.buttons.edit')
                ->authorize(fn($user) => Gate::allows('update', $user))
                ->hidden($this->trashMode || Gate::denies('updateAny', User::class)),

            Column::name('restore', __('ui.restore'))
                ->action()
                ->wireAction('restore')
                ->view('components.dashboard.tables.buttons.restore')
                ->authorize(fn($user) => Gate::allows('restore', $user))
                ->hidden(!$this->trashMode || Gate::denies('restoreAny', User::class)),

            Column::name('delete', __('ui.delete'))
                ->action()
                ->wireAction('delete')
                ->view('components.dashboard.tables.buttons.delete')
                ->authorize(fn($user) => Gate::allows('delete', $user) || Gate::allows('forceDelete', $user))
                ->hidden(Gate::denies('deleteAny', User::class)),

        ]);
    }

    protected function buttons(): Collection|null
    {
        return new Collection([

            Button::name(__('ui.add_service_provider'))
                ->wireAction('create')
                ->view('components.dashboard.tables.buttons.add')
                ->hidden(Gate::denies('create', User::class)),

            Button::name('restore')
                ->type('restore')
                ->view('components.dashboard.tables.buttons.restore')
                ->hidden(!$this->trashMode || Gate::denies('restoreAny', User::class)),

            Button::name('delete')
                ->type('delete')
                ->view('components.dashboard.tables.buttons.delete')
                ->hidden(Gate::denies('deleteAny', User::class)),

            Button::name(__('ui.trash'))
                ->type('trash')
                ->view('components.dashboard.tables.buttons.trash')
                ->hidden(Gate::denies('restoreAny', User::class)),

        ]);
    }

    protected function dropdowns(): Collection|null
    {
        $activityLabels = [
            null => 'النشاط',
            'most_active' => 'الأكثر طلباً',
            'no_orders' => 'بدون طلبات',
        ];

        $cities = \App\Models\City::orderBy('name')->get(['id', 'name']);
        
        $cityChildren = [
            \App\Utils\Livewire\Table\DropdownChild::name('الكل')->wireAction('$set("cityFilter", null)')
        ];
        
        foreach ($cities as $city) {
            $cityChildren[] = \App\Utils\Livewire\Table\DropdownChild::name($city->name)->wireAction('$set("cityFilter", ' . $city->id . ')');
        }

        $selectedCity = $this->cityFilter ? ($cities->firstWhere('id', (int) $this->cityFilter)->name ?? 'المدينة') : 'المدينة';

        return new Collection([
            \App\Utils\Livewire\Table\Dropdown::name($selectedCity)
                ->id('cityFilter')
                ->children($cityChildren),

            \App\Utils\Livewire\Table\Dropdown::name($activityLabels[$this->activityFilter] ?? 'النشاط')
                ->id('activityFilter')
                ->children([
                    \App\Utils\Livewire\Table\DropdownChild::name('الكل')
                        ->wireAction('$set("activityFilter", null)'),
                    \App\Utils\Livewire\Table\DropdownChild::name('الأكثر طلباً')
                        ->wireAction('$set("activityFilter", "most_active")'),
                    \App\Utils\Livewire\Table\DropdownChild::name('بدون طلبات')
                        ->wireAction('$set("activityFilter", "no_orders")'),
                ]),
        ]);
    }

    protected function modals(): Collection|null
    {
        return new Collection([

            Modal::id('createResultModal')
                ->view('dashboard.users.create'),

            Modal::id('showResultModal')
                ->view('dashboard.users.show'),

        ]);
    }

    protected function excelSheetColumns(): Collection|null
    {
        return new Collection([
            ExcelSheetColumn::name('name', __('ui.service_provider_name')),

            ExcelSheetColumn::name('phone'),

            ExcelSheetColumn::name('entity_type', __('ui.account_type'))
                ->callback(function ($user) {
                    switch ($user->entity_type) {
                        case User::INDIVIDUAL_ENTITY_TYPE:
                            $type = __('ui.' . User::INDIVIDUAL_ENTITY_TYPE);
                            break;

                        case User::INSTITUTION_ENTITY_TYPE:
                            $type = __('ui.' . User::INSTITUTION_ENTITY_TYPE);
                            break;

                        case User::COMPANY_ENTITY_TYPE:
                            $type = __('ui.' . User::COMPANY_ENTITY_TYPE);
                            break;
                    }

                    return $type ?? '';
                }),

            ExcelSheetColumn::name('created_at', __('ui.created_at'))
                ->dateFormat(),
        ]);
    }

    protected function excelSheetBuilder(): Builder
    {
        return $this->getFinalQueryBuilder();
    }

    public function exportAsExcel()
    {
        $excelSheet = new ExcelSheet(
            $this->excelSheetColumns(),
            $this->excelSheetBuilder(),
        );

        $excelSheet->export('service-providers');
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
