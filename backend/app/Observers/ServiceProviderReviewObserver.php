<?php

namespace App\Observers;

use App\Jobs\SyncServiceProviderToPineconeJob;
use App\Models\Review;
use App\Models\User;

class ServiceProviderReviewObserver
{
    public function created(Review $review): void
    {
        $this->syncProviderReview($review);
    }

    public function updated(Review $review): void
    {
        $this->syncProviderReview($review);
    }

    public function deleted(Review $review): void
    {
        $this->syncProviderReview($review);
    }

    public function restored(Review $review): void
    {
        $this->syncProviderReview($review);
    }

    private function syncProviderReview(Review $review): void
    {
        if ($review->reviewable_type !== User::class) {
            return;
        }

        $serviceProviderId = (int) $review->reviewable_id;
        if ($serviceProviderId <= 0) {
            return;
        }

        SyncServiceProviderToPineconeJob::dispatch($serviceProviderId);
    }
}
