<?php

namespace App\Livewire\Dashboard\Reviews;

use App\Models\Review;
use App\Models\User;
use Livewire\Component;
use Livewire\Attributes\On;

class Show extends Component
{
    public $review;

    public $customer;
    public ?string $customerPhone = null;

    public $serviceProvider;
    public ?string $providerPhone = null;

    public ?string $body = null;
    public int $rating = 0;
    public int $starCount = 0;
    public ?string $createdAt = null;

    public ?string $customerWhatsApp = null;
    public ?string $providerWhatsApp = null;

    public function mount()
    {
        $this->authorize('manage reviews');
    }

    #[On('show-review')]
    public function getResult($id)
    {
        $this->review = Review::with(['user:id,name,phone', 'reviewable:id,name,phone'])->findOrFail($id);

        $this->customer = $this->review->user;
        $this->customerPhone = $this->customer?->phone;

        $this->serviceProvider = $this->review->reviewable;
        $this->providerPhone = $this->serviceProvider?->phone;

        $this->body = $this->review->body;
        $this->rating = $this->review->rating;
        $this->starCount = min(5, max(1, round($this->rating / 20)));
        $this->createdAt = $this->review->created_at?->isoFormat(config('app.time_format'));

        $this->customerWhatsApp = $this->customerPhone ? $this->formatWhatsAppUrl($this->customerPhone) : null;
        $this->providerWhatsApp = $this->providerPhone ? $this->formatWhatsAppUrl($this->providerPhone) : null;
    }

    /**
     * Format phone for WhatsApp URL
     */
    private function formatWhatsAppUrl(string $phone): string
    {
        $clean = preg_replace('/\D/', '', $phone);

        if (str_starts_with($clean, '0')) {
            $clean = '966' . substr($clean, 1);
        } elseif (!str_starts_with($clean, '966')) {
            $clean = '966' . $clean;
        }

        return "https://wa.me/{$clean}";
    }

    public function render()
    {
        return view('livewire.dashboard.reviews.show');
    }
}
