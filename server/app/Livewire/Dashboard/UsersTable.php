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

class UsersTable extends DataTable
{
    use WithPagination;

    protected $modelClass = User::class;

    public bool $modelHasMedia = true;

    public bool $tableHasTrash = true;

    public bool $exportableTable = true;

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
        return User::query()->select([
            'id',
            'name',
            'phone',
            'city_id',
            'balance',
            'created_at',
        ])->isUser()
          ->with('city:id,name')
          ->withCount('orders');
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
                ->checkbox()
                ->hidden(Gate::denies('deleteAny', User::class)),

            Column::name('name')
                ->sortable(),

            Column::name('phone')
                ->sortable(),

            Column::name('city', __('ui.city'))
                ->relation('city', 'name'),

            Column::name('orders_count', __('ui.orders'))
                ->customValue(fn($user) => number_format($user->orders_count))
                ->sortable(),

            Column::name('balance', __('ui.balance'))
                ->customValue(fn($user) => number_format($user->balance ?? 0, config('app.decimal_places')) . ' ' . __('ui.currency'))
                ->sortable(),

            Column::name('created_at', __('ui.created_at'))
                ->sortable()
                ->dateFormat(),

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

            Button::name(__('ui.add_user'))
                ->url(route('dashboard.users.create'))
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
        return new Collection([]);
    }

    protected function modals(): Collection|null
    {
        return new Collection([

            Modal::id('showResultModal')
                ->view('dashboard.users.show'),

        ]);
    }

    protected function excelSheetColumns(): Collection|null
    {
        return new Collection([
            ExcelSheetColumn::name('name'),

            ExcelSheetColumn::name('phone'),

            ExcelSheetColumn::name('created_at', __('ui.created_at'))
                ->dateFormat(),
        ]);
    }

    protected function excelSheetBuilder(): Builder
    {
        return $this->getFinalQueryBuilder()->select(['id', 'name', 'phone', 'created_at']);
    }

    public function exportAsExcel()
    {
        $excelSheet = new ExcelSheet(
            $this->excelSheetColumns(),
            $this->excelSheetBuilder(),
        );

        $excelSheet->export('customers');
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
