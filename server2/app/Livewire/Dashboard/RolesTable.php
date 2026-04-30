<?php

namespace App\Livewire\Dashboard;

use App\Utils\Livewire\DataTable;
use App\Utils\Livewire\Table\Button;
use App\Utils\Livewire\Table\Column;
use App\Utils\Livewire\Table\Modal;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Livewire\Attributes\On;
use Livewire\WithPagination;
use Spatie\Permission\Models\Role;

class RolesTable extends DataTable
{
    use WithPagination;

    protected $modelClass = Role::class;

    public array|null $searchableColumns = [
        'name',
    ];

    /**
     * System roles ids to ignore from delete or edit
     */
    protected array $systemRolesIds = [
        1
    ];

    /**
     * System roles names to ignore from delete or edit
     */
    protected array $systemRolesNames = [
        'Super admin'
    ];

    public function mount()
    {
        $this->authorize('manage roles and permissions');
    }

    /**
     * Table Query Builder
     */
    protected function builder(): Builder
    {
        return Role::query()->select([
            'id',
            'name',
        ])->withCount([
            'users',
            'permissions'
        ]);
    }

    public function create()
    {
        $this->dispatch('showModal', ['id' => 'createResultModal']);
    }

    public function show($id)
    {
        $this->dispatch('show-result', $id);

        $this->dispatch('showModal', ['id' => 'showResultModal']);
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

            Column::name('users_count', __('ui.users_count'))
                ->sortable(),

            Column::name('permissions_count', __('ui.permissions_count'))
                ->sortable(),

            Column::name('show', __('ui.show'))
                ->action()
                ->view('components.dashboard.tables.buttons.show')
                ->wireAction('show'),

            Column::name('edit', __('ui.edit'))
                ->action()
                ->view('components.dashboard.tables.buttons.edit')
                ->authorize(fn($role) => !in_array($role->name, $this->systemRolesNames))
                ->wireAction('edit'),

            Column::name('delete', __('ui.delete'))
                ->action()
                ->wireAction('delete')
                ->view('components.dashboard.tables.buttons.delete')
                ->authorize(fn($role) => !in_array($role->name, $this->systemRolesNames)),

        ]);
    }

    protected function buttons(): Collection|null
    {
        return new Collection([

            Button::name(__('ui.create_role'))
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
                ->view('dashboard.roles.create'),

            Modal::id('showResultModal')
                ->view('dashboard.roles.show'),

            Modal::id('editResultModal')
                ->view('dashboard.roles.edit'),

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
