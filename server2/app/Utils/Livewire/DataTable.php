<?php

namespace App\Utils\Livewire;

use App\Models\User;
use Livewire\Component;
use Livewire\Attributes\Url;
use App\Utils\Livewire\Table\Dropdown;
use App\Utils\Livewire\Table\DropdownChild;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Spatie\Permission\Models\Role;

abstract class DataTable extends Component
{
    protected $modelClass;

    public string $orderColumn = 'id';

    public string $orderType = 'desc';

    public int $resultsLimit = 10;

    public string $confirmDeleteModalId = 'deleteConfirmationModal';

    public string|null $searchQuery;

    public array|null $idsToDelete;

    public bool $selectAllCheckbox;

    public bool $modelHasMedia = false;

    public bool $draggableItems = false;

    public bool $trashMode = false;

    public bool $tableHasTrash = false;

    public array $selected;

    public array|null $searchableColumns;

    public bool $deleteConfirmed = false;

    public bool $exportableTable = false;

    // Filter properties start

    public bool $tableHasStatus = false;

    public bool $tableHasTypes = false;

    public array $availableStatusTypes = [];

    public array $availableTypes = [];

    #[Url]
    public string|null $typeFilter = null;

    #[Url]
    public string|null $statusFilter = null;

    // Filter properties end

    abstract protected function columns(): Collection|null;

    abstract protected function buttons(): Collection|null;

    abstract protected function dropdowns(): Collection|null;

    abstract protected function modals(): Collection|null;

    abstract protected function builder(): Builder;

    /**
     * Apply search query string to query builder
     */
    protected function applySearchQuery($query)
    {
        $q = trim($this->searchQuery);

        if ($q && $q !== '') {
            $query = $query->where(function (Builder $buiderQuery) use ($q) {
                foreach ($this->searchableColumns as $key => $column) {
                    $whereMethod = $key === 0 ? 'where' : 'orWhere';

                    $buiderQuery->{$whereMethod}($column, 'LIKE', "%{$q}%");
                }
            });
        } else {
            $this->searchQuery = null;
        }

        return $query;
    }

    public function toggleSelected(): void
    {
        $this->selected = $this->selectAllCheckbox
            ? $this->getResults()->pluck('id')->toArray()
            : [];
    }

    /**
     * Delete result
     */
    public function delete($ids): void
    {
        if (!is_array($ids)) {
            $ids = [$ids];
        }

        $customDeleteModels = [
            User::class,
            Role::class
        ];

        // Custom delete
        if (in_array($this->modelClass, $customDeleteModels)) {
            $ids = array_filter($ids, function ($idToDelete) {
                // Remove current user id from ids array
                if ($this->modelClass == User::class) {
                    return $idToDelete != Auth::user()->id;
                }

                // Remove system roles from ids array
                if ($this->modelClass == Role::class) {
                    return !in_array($idToDelete, $this->systemRolesIds);
                }
            });
        }

        if ($this->deleteConfirmed) {

            if ($this->modelHasMedia) {
                // Delete and retrieve each model in ids array

                // Force delete any trashed results from selected
                if ($this->tableHasTrash) {
                    $results = $this->builder()
                        ->whereIn('id', $ids)
                        ->onlyTrashed()
                        ->get(['id']);

                    foreach ($results as $result) {
                        $class = new $this->modelClass;

                        $class->onlyTrashed()
                            ->find($result->id)
                            ->forceDelete();
                    }
                }

                $results = $this->builder()
                    ->whereIn('id', $ids)
                    ->get(['id']);

                foreach ($results as $result) {
                    $class = new $this->modelClass;

                    $class->find($result->id)
                        ->delete();
                }
            } else {
                // Mass delete statement

                // Force delete any trashed results from selected
                if ($this->tableHasTrash) {
                    $this->builder()
                        ->whereIn('id', $ids)
                        ->onlyTrashed()
                        ->forceDelete();
                }

                $this->builder()
                    ->whereIn('id', $ids)
                    ->delete();
            }

            $this->deleteConfirmed = false;
            $this->resetSelector();

            $this->dispatch('hideModal', ['id' => $this->confirmDeleteModalId]);
        } else {
            $this->idsToDelete = $ids;

            $this->dispatch('showModal', ['id' => $this->confirmDeleteModalId]);
        }
    }

    /**
     * Restore results
     */
    public function restore($ids): void
    {
        if (!is_array($ids)) {
            $ids = [$ids];
        }

        $this->builder()
            ->onlyTrashed()
            ->whereIn('id', $ids)
            ->restore();

        $this->resetSelector();
    }

    /**
     * Confirm delete from modal
     */
    public function confirmDelete(): void
    {
        $this->deleteConfirmed = true;

        $this->delete($this->idsToDelete);
    }

    public function toggleTrash(): void
    {
        $this->trashMode = !$this->trashMode;

        $this->resetSelector();
    }

    public function resetSelector(): void
    {
        $this->selected = [];
        $this->selectAllCheckbox = false;
    }

    /**
     * Change results limit
     */
    public function changeLimit(int $limit): void
    {
        $this->resultsLimit = $limit;
    }

    public function sortBy($column): void
    {
        if ($this->orderColumn == $column) {
            $this->orderType = $this->orderType == 'desc' ? 'asc' : 'desc';
        } else {
            $this->orderColumn = $column;
        }
    }

    /**
     * Reorder items when the user changes the position of an item.
     *
     * @param array $data
     * @return void
     */
    public function reorder($data): void
    {
        $item = $data[0];

        $globalIndex = (($this->getPage() - 1) * $this->resultsLimit) + $item['order'];

        $itemId = $item['value'];
        $newOrder = $globalIndex + 1;

        $class = new $this->modelClass;

        $class->moveToOrder($this->builder(), $itemId, $newOrder);
    }

    /**
     * Get table dropdowns
     */
    protected function getDropdowns(): Collection
    {
        $customDropdowns = $this->dropdowns()->map(function ($item) {
            return $item->getData();
        });

        $defaultDropdowns = new Collection([

            Dropdown::name(__('ui.showing_count_results', ['count' => $this->resultsLimit]))
                ->id('resultsLimitDropdown')
                ->children([

                    DropdownChild::name(__('ui.showing_count_results', ['count' => 10]))
                        ->wireAction('changeLimit(10)'),

                    DropdownChild::name(__('ui.showing_count_results', ['count' => 25]))
                        ->wireAction('changeLimit(25)'),

                    DropdownChild::name(__('ui.showing_count_results', ['count' => 50]))
                        ->wireAction('changeLimit(50)')

                ])
                ->getData(),

        ]);

        // Type filter dropdown start
        if ($this->tableHasTypes) {
            $typeDropdownTitle = $this->typeFilter
                ? __("ui.{$this->typeFilter}")
                : __('validation.attributes.type');

            $typesArray = [
                DropdownChild::name(__('ui.all'))
                    ->wireAction('setType()'),
            ];

            foreach ($this->availableTypes as $type) {
                $child = DropdownChild::name(__('ui.' . $type))
                    ->wireAction("setType('{$type}')");

                array_push($typesArray, $child);
            }

            $typesDropdown = Dropdown::name($typeDropdownTitle)
                ->id('resultsTypeDropdown')
                ->children($typesArray)
                ->getData();

            $defaultDropdowns = $defaultDropdowns->merge([$typesDropdown]);
        }
        // Type filter dropdown end

        // Status filter dropdown start
        if ($this->tableHasStatus) {
            $statusDropdownTitle = $this->statusFilter
                ? __("ui.{$this->statusFilter}")
                : __('ui.status');

            $statusTypesArray = [
                DropdownChild::name(__('ui.all'))
                    ->wireAction('setStatus()'),
            ];

            foreach ($this->availableStatusTypes as $status) {
                $child = DropdownChild::name(__('ui.' . $status))
                    ->wireAction("setStatus('{$status}')");

                array_push($statusTypesArray, $child);
            }

            $statusTypesDropdown = Dropdown::name($statusDropdownTitle)
                ->id('resultsStatusDropdown')
                ->children($statusTypesArray)
                ->getData();

            $defaultDropdowns = $defaultDropdowns->merge([$statusTypesDropdown]);
        }
        // Status filter dropdown end

        $dropdowns = $defaultDropdowns->merge($customDropdowns);

        return $dropdowns;
    }

    /**
     * Get table modals
     */
    protected function getModals(): array
    {
        $modals = $this->modals()->map(function ($item) {
            return $item->getData();
        });

        $modals = array_filter($modals->toArray(), function ($modal) {
            return !$modal['hidden'];
        });

        return $modals;
    }

    /**
     * Get table buttons
     */
    protected function getButtons(): array
    {
        $buttons = $this->buttons()->map(function ($item) {
            return $item->getData();
        });

        $buttons = array_filter($buttons->toArray(), function ($button) {
            return !$button['hidden'];
        });

        return $buttons;
    }

    /**
     * Get table fields or columns
     */
    protected function getFields(): array
    {
        $fields = $this->columns()->map(function ($item) {
            return $item->getData();
        });

        $fields = array_filter($fields->toArray(), function ($field) {
            return !$field['hidden'];
        });

        return $fields;
    }

    protected function getFinalQueryBuilder()
    {
        $query = $this->builder();

        if ($this->trashMode) {
            $query = $query->onlyTrashed();
        }

        if (isset($this->searchQuery)) {
            $query = $this->applySearchQuery($query);
        }

        $orderColumn = $this->draggableItems ? 'item_order' : $this->orderColumn;

        $orderType = $orderColumn == 'item_order' ? 'asc' : $this->orderType;

        $query = $query->orderBy($orderColumn, $orderType);

        return $query;
    }

    /**
     * Run table query builder
     */
    protected function getResults()
    {
        $query = $this->getFinalQueryBuilder();

        $results = $query->paginate($this->resultsLimit);

        if ($results->currentPage() > $results->lastPage()) {
            $this->resetPage();

            $results = $query->paginate($this->resultsLimit);
        }

        return $results;
    }

    /**
     * Apply type filter on results
     */
    public function setType($newType = null): void
    {
        $this->typeFilter = $newType;
    }

    /**
     * Apply status filter on results
     */
    public function setStatus($newStatus = null): void
    {
        $this->statusFilter = $newStatus;
    }
}
