<?php

namespace App\Livewire\Dashboard;

use App\Models\Page;
use App\Utils\Livewire\DataTable;
use App\Utils\Livewire\Table\Button;
use App\Utils\Livewire\Table\Column;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Gate;
use Livewire\WithPagination;

class PagesTable extends DataTable
{
    use WithPagination;

    protected $modelClass = Page::class;

    public bool $modelHasMedia = true;

    public bool $tableHasStatus = true;

    public array $availableStatusTypes = Page::AVAILABLE_STATUS_TYPES;

    public array|null $searchableColumns = [
        'name',
    ];

    public string|null $statusFilter = null;

    public function mount()
    {
        $this->authorize('viewAny', Page::class);
    }

    /**
     * Table Query Builder
     */
    protected function builder(): Builder
    {
        $query = Page::query()->notDefaultPages();

        if ($this->statusFilter) {
            if ($this->statusFilter == 'active') {
                // Add active scope to query
                $query->active();
            } else {
                // Add inactive scope to query
                $query->inactive();
            }
        }

        return $query;
    }

    protected function columns(): Collection|null
    {
        return new Collection([

            Column::name('check')
                ->checkbox()
                ->hidden(Gate::denies('deleteAny', Page::class)),

            Column::name('name')
                ->sortable(),

            Column::name('status', __('ui.status'))
                ->callback(function ($page) {

                    if ($page->isActive()) {
                        $badge = view('components.dashboard.badges.success', ['name' => __('ui.active')]);
                    } else {
                        $badge = view('components.dashboard.badges.light', ['name' => __('ui.inactive')]);
                    }

                    return $badge;
                }),

            // Column::name('show', __('ui.show'))
            //     ->action()
            //     ->url(fn($page) => route('front.pages.show', ['page' => $page->id]))
            //     ->target('_blank')
            //     ->view('components.dashboard.tables.buttons.show'),

            Column::name('edit', __('ui.edit'))
                ->action()
                ->url(fn($page) => route('dashboard.pages.edit', ['page' => $page->id]))
                ->view('components.dashboard.tables.buttons.edit')
                ->authorize(fn($page) => Gate::allows('update', $page))
                ->hidden(Gate::denies('updateAny', Page::class)),

            Column::name('delete', __('ui.delete'))
                ->action()
                ->wireAction('delete')
                ->view('components.dashboard.tables.buttons.delete')
                ->authorize(fn($page) => Gate::allows('delete', $page))
                ->hidden(Gate::denies('deleteAny', Page::class)),

        ]);
    }

    protected function buttons(): Collection|null
    {
        return new Collection([

            Button::name(__('ui.create_page'))
                ->url(route('dashboard.pages.create'))
                ->view('components.dashboard.tables.buttons.add')
                ->hidden(Gate::denies('create', Page::class)),

            Button::name('delete')
                ->type('delete')
                ->view('components.dashboard.tables.buttons.delete')
                ->hidden(Gate::denies('deleteAny', Page::class)),

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
