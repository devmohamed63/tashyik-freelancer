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
        ])->withCount('serviceProviders')
        ->when($this->sortByFilters === 'most_providers', function ($query) {
            $query->orderBy('service_providers_count', 'desc');
        })
        ->when($this->sortByFilters === 'least_providers', function ($query) {
            $query->orderBy('service_providers_count', 'asc');
        });
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
        $this->dispatch('show-result', $id);

        $this->dispatch('showModal', ['id' => 'showResultModal']);
    }

    protected function columns(): Collection|null
    {
        return new Collection([

            Column::name('check')
                ->checkbox(),

            Column::name('name')
                ->sortable(),

            Column::name('service_providers_count', __('ui.service_providers'))
                ->customValue(fn($city) => number_format($city->service_providers_count))
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
        $sortChildren = [
            \App\Utils\Livewire\Table\DropdownChild::name('الافتراضي')
                 ->wireAction('$set("sortByFilters", "latest")'),
            \App\Utils\Livewire\Table\DropdownChild::name('الأكثر فنيين')
                 ->wireAction('$set("sortByFilters", "most_providers")'),
            \App\Utils\Livewire\Table\DropdownChild::name('الأقل فنيين')
                 ->wireAction('$set("sortByFilters", "least_providers")'),
        ];
        
        $currentSortName = 'الافتراضي';
        if ($this->sortByFilters === 'most_providers') $currentSortName = 'الأكثر فنيين';
        if ($this->sortByFilters === 'least_providers') $currentSortName = 'الأقل فنيين';

        return new Collection([
             \App\Utils\Livewire\Table\Dropdown::name('ترتيب: ' . $currentSortName)
                 ->id('sortByFilters')
                 ->children($sortChildren),
        ]);
    }

    protected function modals(): Collection|null
    {
        return new Collection([

            Modal::id('createResultModal')
                ->view('dashboard.cities.create'),

            Modal::id('editResultModal')
                ->view('dashboard.cities.edit'),

            Modal::id('showResultModal')
                ->view('dashboard.cities.show'),

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
