<?php

namespace App\Livewire\Dashboard;

use App\Models\Review;
use App\Utils\Livewire\DataTable;
use App\Utils\Livewire\Table\Column;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Livewire\Attributes\On;
use Livewire\Attributes\Url;
use Livewire\WithPagination;

class ReviewsTable extends DataTable
{
    use WithPagination;

    protected $modelClass = Review::class;

    public array|null $searchableColumns = [
        'body',
    ];

    #[Url]
    public string|null $ratingFilter = null;

    public function mount()
    {
        $this->authorize('manage reviews');
    }

    /**
     * Table Query Builder
     */
    protected function builder(): Builder
    {
        $query = Review::query()
            ->select(['id', 'user_id', 'reviewable_id', 'reviewable_type', 'body', 'rating', 'created_at'])
            ->with(['user:id,name,phone', 'reviewable:id,name,phone']);

        if ($this->ratingFilter) {
            $filter = (int) $this->ratingFilter;

            if ($filter === 5) {
                $query->where('rating', '>=', 90);
            } elseif ($filter === 1) {
                $query->where('rating', '<', 30);
            } else {
                $query->whereBetween('rating', [($filter * 20) - 10, ($filter * 20) + 9]);
            }
        }

        return $query;
    }

    protected function columns(): Collection|null
    {
        return new Collection([

            Column::name('id', '#')
                ->sortable(),

            Column::name('customer', __('ui.customer'))
                ->callback(function ($review) {
                    $name = $review->user?->name ?? '-';
                    $phone = $review->user?->phone ?? '-';
                    return "<div><span class='block font-semibold'>{$name}</span><span class='block text-xs text-gray-500 mt-1' dir='ltr'>{$phone}</span></div>";
                }),

            Column::name('service_provider', __('ui.service_provider'))
                ->callback(function ($review) {
                    $name = $review->reviewable?->name ?? '-';
                    $phone = $review->reviewable?->phone ?? '-';
                    return "<div><span class='block font-semibold'>{$name}</span><span class='block text-xs text-gray-500 mt-1' dir='ltr'>{$phone}</span></div>";
                }),

            Column::name('rating', 'التقييم')
                ->callback(function ($review) {
                    $starCount = min(5, max(1, round($review->rating / 20)));
                    $stars = str_repeat('⭐', (int)$starCount);
                    return "<span class='text-sm'>$stars</span>";
                })
                ->sortable(),

            Column::name('body', 'التعليق')
                ->customValue(fn($review) => $review->body
                    ? \Illuminate\Support\Str::limit($review->body, 80)
                    : '-'),

            Column::name('created_at', __('ui.created_at'))
                ->sortable()
                ->dateFormat(),

            Column::name('delete', __('ui.delete'))
                ->action()
                ->wireAction('delete')
                ->view('components.dashboard.tables.buttons.delete'),

        ]);
    }

    protected function buttons(): Collection|null
    {
        return new Collection([
            \App\Utils\Livewire\Table\Button::name('delete')
                ->type('delete')
                ->view('components.dashboard.tables.buttons.delete'),
        ]);
    }

    protected function modals(): Collection|null
    {
        return new Collection();
    }

    protected function dropdowns(): Collection|null
    {
        $ratingLabels = [
            null => 'التقييم',
            '5' => '⭐⭐⭐⭐⭐ (5)',
            '4' => '⭐⭐⭐⭐ (4)',
            '3' => '⭐⭐⭐ (3)',
            '2' => '⭐⭐ (2)',
            '1' => '⭐ (1)',
        ];

        $ratingChildren = [
            \App\Utils\Livewire\Table\DropdownChild::name('الكل')
                ->wireAction('$set("ratingFilter", null)'),
        ];

        foreach (['5', '4', '3', '2', '1'] as $rating) {
            $ratingChildren[] = \App\Utils\Livewire\Table\DropdownChild::name($ratingLabels[$rating])
                ->wireAction('$set("ratingFilter", "' . $rating . '")');
        }

        return new Collection([
            \App\Utils\Livewire\Table\Dropdown::name($ratingLabels[$this->ratingFilter] ?? 'التقييم')
                ->id('ratingFilter')
                ->children($ratingChildren),
        ]);
    }

    #[On('refreshTable')]
    public function render()
    {
        return view('livewire.dashboard.general-table', [
            'results'   => $this->getResults(),
            'fields'    => $this->getFields(),
            'buttons'   => $this->getButtons(),
            'dropdowns' => $this->getDropdowns(),
            'modals'    => $this->getModals(),
        ]);
    }
}
