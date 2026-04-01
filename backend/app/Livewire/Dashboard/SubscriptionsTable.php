<?php

namespace App\Livewire\Dashboard;

use App\Models\Subscription;
use App\Models\User;
use App\Utils\ExcelSheet\Column as ExcelSheetColumn;
use App\Utils\ExcelSheet\ExcelSheet;
use App\Utils\Livewire\DataTable;
use App\Utils\Livewire\Table\Column;
use App\Utils\Livewire\Table\Modal;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\On;
use Livewire\WithPagination;

class SubscriptionsTable extends DataTable
{
    use WithPagination;

    protected $modelClass = User::class;

    public bool $tableHasStatus = true;

    public bool $exportableTable = true;

    public array $availableStatusTypes = Subscription::AVAILABLE_STATUS_TYPES;

    public array|null $searchableColumns = [
        'name',
        'phone'
    ];

    public function mount()
    {
        $this->authorize('manage subscriptions');
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
        ])->notUser()->with([
            'subscription:id,user_id,ends_at'
        ]);

        if ($this->statusFilter) {
            switch ($this->statusFilter) {
                case Subscription::ACTIVE_STATUS:
                    $query->whereRelation('subscription', 'ends_at', '>', now());
                    break;

                case Subscription::INACTIVE_STATUS:
                    $query->whereRelation('subscription', 'ends_at', '<', now());
                    break;
            }
        }

        return $query;
    }

    public function show($id)
    {
        $this->dispatch('show-result', $id);

        $this->dispatch('showModal', ['id' => 'showResultModal']);
    }

    public function renew($userId)
    {
        Subscription::where('user_id', $userId)->update([
            'ends_at' => now()->addMonth(),
        ]);
    }

    protected function columns(): Collection|null
    {
        return new Collection([

            Column::name('check')
                ->checkbox()
                ->hidden(Gate::denies('deleteAny', User::class)),

            Column::name('name', __('ui.service_provider_name'))
                ->sortable(),

            Column::name('phone')
                ->sortable(),

            Column::name('type', __('ui.account_type'))
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

            Column::name('status', __('ui.subscription_status'))
                ->callback(function ($user) {
                    $active = $user->subscription?->ends_at > now();

                    if ($active) {
                        $badge = view('components.dashboard.badges.success', ['name' => __('ui.active')]);
                    } else {
                        $badge = view('components.dashboard.badges.danger', ['name' => __('ui.inactive')]);
                    }

                    $renewButton = !$active
                        ? view('components.dashboard.tables.buttons.renew', ['user' => $user])
                        : '';

                    return <<<HTML
                        <div class="inline-flex items-center gap-2">
                            $badge
                            $renewButton
                        </div>
                    HTML;
                }),

            Column::name('show', __('ui.show'))
                ->action()
                ->view('components.dashboard.tables.buttons.show')
                ->wireAction('show')
                ->hidden(Gate::denies('viewAny', User::class)),

        ]);
    }

    protected function buttons(): Collection|null
    {
        return new Collection([]);
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
            ExcelSheetColumn::name('name', __('ui.service_provider_name')),

            ExcelSheetColumn::name('subscription', __('ui.subscription_status'))
                ->callback(function ($user) {
                    if ($user->subscription?->ends_at > now()) {
                        $status = __('ui.active');
                    } else {
                        $status = __('ui.inactive');
                    }

                    return $status ?? '';
                }),

            ExcelSheetColumn::name('phone'),
        ]);
    }

    protected function excelSheetBuilder(): Builder
    {
        return $this->getFinalQueryBuilder()->select(['id', 'name', 'phone']);
    }

    public function exportAsExcel()
    {
        $excelSheet = new ExcelSheet(
            $this->excelSheetColumns(),
            $this->excelSheetBuilder(),
        );

        $excelSheet->export('subscriptions');
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
