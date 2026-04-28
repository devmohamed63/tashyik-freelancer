<?php

namespace App\Livewire\Dashboard\Banners;

use App\Models\AdBroadcast;
use App\Models\Banner;
use App\Utils\Services\Firebase\CloudMessaging;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Livewire\Attributes\On;
use Livewire\Attributes\Validate;
use Livewire\Component;
use Livewire\WithFileUploads;

class CreateAd extends Component
{
    use WithFileUploads;

    /**
     * When false, the form is shown on a full page (no modal chrome, no hideModal dispatch).
     */
    public bool $embedInModal = true;

    /**
     * @var list<string>
     */
    public array $audiences = ['customers'];

    public string $title = '';

    public string $description = '';

    #[Validate]
    public $image;

    public ?string $error;

    /**
     * When re-sending from history, reuse this storage path (public disk) for the FCM image URL without a new upload.
     */
    public ?string $resendStoragePath = null;

    protected string $imageUrl = '';

    protected function rules()
    {
        return [
            'audiences' => ['required', 'array', 'min:1'],
            'audiences.*' => ['required', 'string', 'in:customers,service_providers,guests'],
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:255'],
            'image' => ['nullable', 'image', 'mimes:'.config('app.allowed_image_mimes'), 'max:'.config('app.upload_max_size')],
        ];
    }

    protected function validationAttributes(): array
    {
        return [
            'audiences' => __('ui.target_audiences'),
            'audiences.*' => __('ui.target_audiences'),
            'title' => __('validation.attributes.title'),
            'description' => __('validation.attributes.description'),
            'image' => __('validation.attributes.image'),
        ];
    }

    public function mount(): void
    {
        $this->authorize('viewAny', Banner::class);

        $resendId = request()->integer('resend');
        if ($resendId > 0) {
            $this->fillCreateAdFromBroadcast($resendId);
        }
    }

    /**
     * Label for the guests audience option. Falls back if lang files are stale and __('ui.guests') returns the key.
     */
    public function guestsAudienceLabel(): string
    {
        $translated = __('ui.guests');

        if ($translated !== 'ui.guests') {
            return $translated;
        }

        return app()->getLocale() === 'ar' ? 'الضيوف' : 'Guests';
    }

    #[On('fillCreateAdFromBroadcast')]
    public function fillCreateAdFromBroadcast(int $id): void
    {
        $this->authorize('create', Banner::class);

        $row = AdBroadcast::query()->findOrFail($id);

        $this->audiences = $row->audienceKeys();
        if ($this->audiences === []) {
            $this->audiences = ['customers'];
        }
        $this->title = $row->title;
        $this->description = (string) ($row->description ?? '');
        $this->image = null;
        $this->resendStoragePath = $row->image_path;
        $this->error = null;
        $this->imageUrl = '';
        $this->resetValidation();
    }

    public function publish()
    {
        $this->authorize('create', Banner::class);

        $this->validate();

        $storedImagePath = null;
        $this->imageUrl = '';

        if ($this->image) {
            $storedImagePath = $this->image->store('ads', 'public');
            $this->imageUrl = rtrim((string) env('CDN_URL'), '/')."/storage/$storedImagePath";
            $this->resendStoragePath = null;
        } elseif (filled($this->resendStoragePath)) {
            $storedImagePath = $this->resendStoragePath;
            $this->imageUrl = rtrim((string) env('CDN_URL'), '/').'/storage/'.ltrim($this->resendStoragePath, '/');
        }

        $audiences = array_values(array_unique($this->audiences));
        sort($audiences);

        $topicByAudience = [
            'customers' => 'customer',
            'service_providers' => 'service_provider',
            'guests' => 'guest',
        ];

        $sendId = (string) Str::uuid();

        try {
            foreach ($audiences as $audienceKey) {
                $fcm = new CloudMessaging;

                $fcm->setNotification(
                    $this->title,
                    $this->description,
                    $this->imageUrl
                );

                $fcm->setData([
                    'send_id' => $sendId,
                    'sent_at' => (string) now()->getTimestamp(),
                    'source' => 'dashboard_ad',
                    'audience' => $audienceKey,
                ]);

                $fcm->massSend($topicByAudience[$audienceKey]);
            }

            AdBroadcast::query()->create([
                'audience' => implode(',', $audiences),
                'title' => $this->title,
                'description' => $this->description !== '' ? $this->description : null,
                'image_path' => $storedImagePath,
                'user_id' => auth()->id(),
            ]);
        } catch (\Throwable $th) {
            Log::error('Failed to publish Ad from dashboard:', [$th]);

            $this->error = __('ui.unexpected_error');

            return;
        }

        if ($this->embedInModal) {
            $this->dispatch('hideModal', ['id' => 'createAdModal']);
        }

        $this->dispatch('refreshTable');
        $this->resetFormState();
    }

    protected function resetFormState(): void
    {
        $this->audiences = ['customers'];
        $this->title = '';
        $this->description = '';
        $this->image = null;
        $this->error = null;
        $this->imageUrl = '';
        $this->resendStoragePath = null;
        $this->resetValidation();
    }

    public function render()
    {
        return view('livewire.dashboard.banners.create-ad');
    }
}
