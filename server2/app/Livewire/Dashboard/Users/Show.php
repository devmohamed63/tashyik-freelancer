<?php

namespace App\Livewire\Dashboard\Users;

use App\Models\User;
use App\Events\ServiceProviderApproved;
use App\Utils\ExcelSheet\ExcelSheet;
use App\Utils\ExcelSheet\Column as ExcelSheetColumn;
use Illuminate\Support\Collection;
use Livewire\Component;
use Livewire\Attributes\On;
use Livewire\Attributes\Url;

class Show extends Component
{
    public $user;

    public string|null $avatar;

    public array $userRoles;

    public bool $isServiceProvider;

    public bool $isInstitution;

    public string $status = '';

    public string $account_status = '';

    public string|null $residenceImage;

    public string|null $commercialRegistrationImage;

    public string|null $nationalAddressImage;

    public array $categories = [];

    #[Url]
    public $showResult = '';

    public function mount()
    {
        $this->authorize('viewAny', User::class);

        if ($this->showResult) {
            $this->dispatch('show-result', $this->showResult);

            $this->dispatch('showModal', ['id' => 'showResultModal']);

            $this->showResult = null;
        }
    }

    #[On('show-result')]
    public function getResult($id)
    {
        $this->user = User::findOrFail($id);
        $this->avatar = $this->user->getAvatarUrl('lg');
        $this->userRoles = $this->user->roles->pluck(['name'])->toArray();
        $this->isServiceProvider = $this->user->type != User::USER_ACCOUNT_TYPE;
        $this->status = $this->user->status;
        $this->account_status = $this->status;
        $this->categories = $this->user?->categories->pluck('name')->toArray();

        $this->user->loadAvg('reviews', 'rating');

        $this->isInstitution = in_array(
            $this->user->entity_type,
            [User::INSTITUTION_ENTITY_TYPE, User::COMPANY_ENTITY_TYPE]
        );

        if ($this->isInstitution) {
            $this->user->load(['members' => function ($q) {
                $q->select(['id', 'name', 'phone', 'status', 'institution_id', 'created_at'])
                    ->withCount([
                        'serviceProviderOrders as completed_orders' => fn($q) => $q->completed(),
                    ])
                    ->withSum([
                        'serviceProviderOrders as total_earnings' => fn($q) => $q->completed(),
                    ], 'subtotal');
            }]);
        } else {
            $this->user->load('members');
        }

        $this->residenceImage = $this->user->getMedia('residence_image')
            ->first()
            ?->getUrl('xl');

        $this->commercialRegistrationImage = $this->user->getMedia('commercial_registration_image')
            ->first()
            ?->getUrl('xl');

        $this->nationalAddressImage = $this->user->getMedia('national_address_image')
            ->first()
            ?->getUrl('xl');
    }

    public function approve()
    {
        $this->user->update([
            'status' => User::ACTIVE_STATUS
        ]);

        $this->dispatch('hideModal', ['id' => 'showResultModal']);

        $this->dispatch('refreshTable');
    }

    public function exportMembers()
    {
        $columns = new Collection([
            ExcelSheetColumn::name('name', __('validation.attributes.name')),
            ExcelSheetColumn::name('phone', __('validation.attributes.phone')),
        ]);

        $builder = User::where('institution_id', $this->user->id)
            ->select(['id', 'name', 'phone']);

        $excelSheet = new ExcelSheet($columns, $builder);

        return $excelSheet->export("members-{$this->user->id}");
    }

    public function render()
    {
        if ($this->account_status != $this->status) {
            $this->user?->update([
                'status' => $this->account_status
            ]);

            $this->status = $this->account_status;

            // Send approval notification
            if ($this->status == User::ACTIVE_STATUS) {
                ServiceProviderApproved::dispatch($this?->user);
            }

            // Logout from all devices
            if ($this->status == User::INACTIVE_STATUS) {
                $this->user->tokens()->delete();
            }

            $this->dispatch('refreshTable');
        }

        return view('livewire.dashboard.users.show');
    }
}
