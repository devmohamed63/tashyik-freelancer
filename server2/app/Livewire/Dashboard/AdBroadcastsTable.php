<?php

namespace App\Livewire\Dashboard;

use App\Models\AdBroadcast;
use App\Models\Banner;
use App\Utils\Livewire\DataTable;
use App\Utils\Livewire\Table\Button;
use App\Utils\Livewire\Table\Column;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\On;
use Livewire\WithPagination;

class AdBroadcastsTable extends DataTable
{
    use WithPagination;

    protected $modelClass = AdBroadcast::class;

    public string $orderColumn = 'created_at';

    public string $orderType = 'desc';

    public bool $tableHasTypes = true;

    public array $availableTypes = [
        'customers',
        'service_providers',
        'guests',
    ];

    public ?array $searchableColumns = [
        'title',
        'audience',
    ];

    public function mount(): void
    {
        $this->authorize('viewAny', Banner::class);
    }

    protected function builder(): Builder
    {
        $query = AdBroadcast::query()
            ->select([
                'id',
                'audience',
                'title',
                'user_id',
                'created_at',
            ])
            ->with(['user:id,name']);

        if ($this->typeFilter) {
            $key = $this->typeFilter;
            $query->where(function (Builder $q) use ($key) {
                $q->where('audience', $key)
                    ->orWhere('audience', 'like', $key.',%')
                    ->orWhere('audience', 'like', '%,'.$key.',%')
                    ->orWhere('audience', 'like', '%,'.$key);
            });
        }

        return $query;
    }

    protected function columns(): ?Collection
    {
        return new Collection([

            Column::name('title', __('validation.attributes.title'))
                ->sortable(),

            Column::name('audience', __('ui.audiences_column'))
                ->callback(function (AdBroadcast $row) {
                    $labels = collect($row->audienceKeys())
                        ->map(fn (string $k) => e(__('ui.'.$k)))
                        ->join(', ');

                    return '<p class="text-gray-500 text-theme-sm dark:text-gray-400 font-medium">'.$labels.'</p>';
                }),

            Column::name('user_id', __('ui.sent_by'))
                ->relation('user', 'name'),

            Column::name('created_at', __('ui.created_at'))
                ->dateFormat()
                ->sortable(),

            Column::name('resend', __('ui.resend_ad'))
                ->action()
                ->wireAction('redirectResend')
                ->view('components.dashboard.tables.buttons.resend-ad')
                ->hidden(Gate::denies('create', Banner::class)),
        ]);
    }

    protected function buttons(): ?Collection
    {
        return new Collection([

            Button::name(__('ui.create_push_ad'))
                ->url(route('dashboard.push-ads.create'))
                ->view('components.dashboard.tables.buttons.add')
                ->hidden(Gate::denies('create', Banner::class)),

        ]);
    }

    protected function dropdowns(): ?Collection
    {
        return new Collection([]);
    }

    protected function modals(): ?Collection
    {
        return new Collection([]);
    }

    public function redirectResend(int $id): void
    {
        Gate::authorize('create', Banner::class);

        $this->redirect(route('dashboard.push-ads.create', ['resend' => $id]));
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
