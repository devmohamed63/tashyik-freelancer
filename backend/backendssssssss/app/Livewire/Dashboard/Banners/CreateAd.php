<?php

namespace App\Livewire\Dashboard\Banners;

use App\Models\Banner;
use Illuminate\Support\Facades\Log;
use Livewire\Attributes\Validate;
use Livewire\Component;
use Livewire\WithFileUploads;
use App\Utils\Services\Firebase\CloudMessaging;

class CreateAd extends Component
{
    use WithFileUploads;

    public string $audience;

    public string $title;

    public string $description = '';

    #[Validate]
    public $image;

    public ?string $error;

    protected string $imageUrl = '';

    protected function rules()
    {
        return [
            'audience' => ['required', 'string', 'in:customers,service_providers'],
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:255'],
            'image' => ['nullable', 'image', 'mimes:' . config('app.allowed_image_mimes'), 'max:' . config('app.upload_max_size')],
        ];
    }

    public function mount()
    {
        $this->authorize('viewAny', Banner::class);
    }

    public function publish()
    {
        $this->authorize('create', Banner::class);

        $this->validate();

        if ($this->image) {
            $uploadedImage = $this->image->store('ads', 'public');

            $this->imageUrl = env('CDN_URL') . "/storage/$uploadedImage";
        }

        try {
            $fcm = new CloudMessaging();

            $fcm->setNotification(
                $this->title,
                $this->description,
                $this->imageUrl
            );

            switch ($this->audience) {
                case 'customers':
                    $topic = 'customer';
                    break;

                case 'service_providers':
                    $topic = 'service_provider';
                    break;
            }

            $fcm->massSend($topic);
        } catch (\Throwable $th) {
            Log::error('Failed to publish Ad from dashboard:', [$th]);

            $this->error = __('ui.unexpected_error');

            return;
        }

        $this->dispatch('hideModal', ['id' => 'createAdModal']);
        $this->dispatch('refreshTable');
        $this->reset();
    }

    public function render()
    {
        return view('livewire.dashboard.banners.create-ad');
    }
}
