<?php

namespace App\Livewire\Dashboard;

use App\Models\Article;
use App\Utils\Livewire\DataTable;
use App\Utils\Livewire\Table\Button;
use App\Utils\Livewire\Table\Column;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Gate;
use Livewire\WithPagination;

class ArticlesTable extends DataTable
{
    use WithPagination;

    protected $modelClass = Article::class;

    public bool $modelHasMedia = true;

    public bool $tableHasStatus = true;

    public array $availableStatusTypes = Article::AVAILABLE_STATUS_TYPES;

    public array|null $searchableColumns = [
        'title',
    ];

    public string|null $statusFilter = null;

    public function mount()
    {
        $this->authorize('viewAny', Article::class);
    }

    /**
     * Table Query Builder
     */
    protected function builder(): Builder
    {
        $query = Article::query();

        if ($this->statusFilter) {
            if ($this->statusFilter == 'active') {
                $query->active();
            } else {
                $query->inactive();
            }
        }

        return $query->latest();
    }

    protected function columns(): Collection|null
    {
        return new Collection([

            Column::name('check')
                ->checkbox()
                ->hidden(Gate::denies('deleteAny', Article::class)),

            Column::name('featured_image', __('ui.upload_image'))
                ->callback(function ($article) {
                    $imageUrl = $article->getImageUrl('card');

                    if ($imageUrl) {
                        return '<img src="' . $imageUrl . '" alt="' . e($article->title) . '" class="w-16 h-10 rounded-md object-cover" />';
                    }

                    return '<div class="w-16 h-10 rounded-md bg-gray-100 dark:bg-gray-800 flex items-center justify-center"><svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg></div>';
                }),

            Column::name('title')
                ->sortable(),

            Column::name('is_featured', __('ui.featured'))
                ->callback(function ($article) {
                    if ($article->is_featured) {
                        $badge = view('components.dashboard.badges.success', ['name' => __('ui.yes')]);
                    } else {
                        $badge = view('components.dashboard.badges.light', ['name' => __('ui.no')]);
                    }

                    return $badge;
                }),

            Column::name('status', __('ui.status'))
                ->callback(function ($article) {

                    if ($article->isActive()) {
                        $badge = view('components.dashboard.badges.success', ['name' => __('ui.active')]);
                    } else {
                        $badge = view('components.dashboard.badges.light', ['name' => __('ui.inactive')]);
                    }

                    return $badge;
                }),

            Column::name('published_at', __('ui.published_at'))
                ->callback(function ($article) {
                    return $article->published_at?->format('Y-m-d') ?? '-';
                }),

            Column::name('edit', __('ui.edit'))
                ->action()
                ->url(fn($article) => route('dashboard.articles.edit', ['article' => $article->id]))
                ->view('components.dashboard.tables.buttons.edit')
                ->authorize(fn($article) => Gate::allows('update', $article))
                ->hidden(Gate::denies('updateAny', Article::class)),

            Column::name('delete', __('ui.delete'))
                ->action()
                ->wireAction('delete')
                ->view('components.dashboard.tables.buttons.delete')
                ->authorize(fn($article) => Gate::allows('delete', $article))
                ->hidden(Gate::denies('deleteAny', Article::class)),

        ]);
    }

    protected function buttons(): Collection|null
    {
        return new Collection([

            Button::name(__('ui.create_article'))
                ->url(route('dashboard.articles.create'))
                ->view('components.dashboard.tables.buttons.add')
                ->hidden(Gate::denies('create', Article::class)),

            Button::name('delete')
                ->type('delete')
                ->view('components.dashboard.tables.buttons.delete')
                ->hidden(Gate::denies('deleteAny', Article::class)),

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
