<?php

namespace App\Livewire\Dashboard;

use App\Models\Contact;
use App\Utils\Livewire\DataTable;
use App\Utils\Livewire\Table\Button;
use App\Utils\Livewire\Table\Column;
use App\Utils\Livewire\Table\Modal;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Livewire\Attributes\On;
use Livewire\WithPagination;

class ContactsTable extends DataTable
{
    use WithPagination;

    protected $modelClass = Contact::class;

    public array|null $searchableColumns = [
        'subject',
        'name',
    ];

    public function mount()
    {
        $this->authorize('viewAny', Contact::class);
    }

    /**
     * Table Query Builder
     */
    protected function builder(): Builder
    {
        return Contact::query()->select([
            'id',
            'name',
            'subject',
            'created_at',
        ]);
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

            Column::name('subject', __('ui.subject'))
                ->sortable(),

            Column::name('name')
                ->sortable(),

            Column::name('created_at', __('ui.created_at'))
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
        return new Collection([]);
    }

    protected function modals(): Collection|null
    {
        return new Collection([

            Modal::id('showResultModal')
                ->view('dashboard.contacts.show'),

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
