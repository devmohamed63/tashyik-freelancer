<?php

namespace App\Livewire\Dashboard;

use App\Models\Question;
use App\Utils\Livewire\DataTable;
use App\Utils\Livewire\Table\Button;
use App\Utils\Livewire\Table\Column;
use App\Utils\Livewire\Table\Modal;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Livewire\Attributes\On;
use Livewire\WithPagination;

class QuestionsTable extends DataTable
{
    use WithPagination;

    protected $modelClass = Question::class;

    public array|null $searchableColumns = [
        'name',
    ];

    public bool $draggableItems = true;

    public string $confirmDeleteModalId = 'questionDeleteConfirmationModal';

    public function mount()
    {
        $this->authorize('manage settings');
    }

    /**
     * Table Query Builder
     */
    protected function builder(): Builder
    {
        return Question::query()->select([
            'id',
            'title',
            'answer',
            'item_order',
        ]);
    }

    public function create()
    {
        $this->dispatch('showModal', ['id' => 'createQuestionModal']);
    }

    protected function columns(): Collection|null
    {
        return new Collection([

            Column::name('check')
                ->checkbox(),

            Column::name('question')
                ->callback(function ($question) {
                    return <<<HTML
                        <div class="flex flex-col gap-2 items-start">
                            <p class="text-gray-600 text-sm dark:text-gray-400 font-semibold">$question->title</p>
                            <p class="text-gray-500 text-sm dark:text-gray-400">$question->answer</p>
                        </div>
                    HTML;
                }),

            Column::name('delete', __('ui.delete'))
                ->action()
                ->wireAction('delete')
                ->view('components.dashboard.tables.buttons.delete'),

        ]);
    }

    protected function buttons(): Collection|null
    {
        return new Collection([

            Button::name(__('ui.add_question'))
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

            Modal::id('createQuestionModal')
                ->view('dashboard.questions.create'),

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
