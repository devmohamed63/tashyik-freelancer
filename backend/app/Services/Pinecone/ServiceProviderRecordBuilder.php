<?php

namespace App\Services\Pinecone;

use App\Models\Order;
use App\Models\User;

class ServiceProviderRecordBuilder
{
    public function build(User $serviceProvider): array
    {
        $textField = (string) config('services.pinecone.record_text_key', 'text');
        $categoryNames = $serviceProvider->categories
            ->map(fn ($category) => (string) $category->getTranslation('name', 'ar'))
            ->filter()
            ->values();
        $reviews = $serviceProvider->reviews;
        $reviewsCount = $reviews->count();
        $avgRatingPercent = $reviewsCount > 0 ? round((float) $reviews->avg('rating'), 2) : 0.0;
        $avgRatingFive = round($avgRatingPercent / 20, 2);
        $recentReviews = $reviews
            ->sortByDesc('created_at')
            ->take(8)
            ->values();

        $completedOrders = $serviceProvider->serviceProviderOrders
            ->where('status', Order::COMPLETED_STATUS)
            ->values();
        $completedOrdersCount = $completedOrders->count();
        $workedCityIds = $completedOrders
            ->pluck('customer.city_id')
            ->filter()
            ->map(fn ($id) => (int) $id)
            ->values();
        $workedCityNames = $completedOrders
            ->map(fn ($order) => $order->customer?->city?->getTranslation('name', 'ar'))
            ->filter()
            ->values();
        $workedCategoryIds = $completedOrders
            ->pluck('category_id')
            ->filter()
            ->map(fn ($id) => (int) $id)
            ->values();
        $workedCategoryNames = $completedOrders
            ->map(fn ($order) => $order->category?->getTranslation('name', 'ar'))
            ->filter()
            ->values();
        $workedServiceIds = $completedOrders
            ->pluck('service_id')
            ->filter()
            ->map(fn ($id) => (int) $id)
            ->values();
        $workedServiceNames = $completedOrders
            ->map(fn ($order) => $order->service?->getTranslation('name', 'ar'))
            ->filter()
            ->values();
        $topWorkedCityId = $workedCityIds->countBy()->sortDesc()->keys()->first();
        $topWorkedCityName = $workedCityNames->countBy()->sortDesc()->keys()->first();
        $topWorkedCategoryId = $workedCategoryIds->countBy()->sortDesc()->keys()->first();
        $topWorkedCategoryName = $workedCategoryNames->countBy()->sortDesc()->keys()->first();
        $topWorkedServiceId = $workedServiceIds->countBy()->sortDesc()->keys()->first();
        $topWorkedServiceName = $workedServiceNames->countBy()->sortDesc()->keys()->first();
        $latestReviewAt = $reviews->max('created_at');
        $latestCompletedOrderAt = $completedOrders->max('updated_at');

        $textChunks = array_filter([
            "provider_id: {$serviceProvider->id}",
            "name: {$serviceProvider->name}",
            "phone: {$serviceProvider->phone}",
            "email: {$serviceProvider->email}",
            "type: {$serviceProvider->type}",
            "entity_type: {$serviceProvider->entity_type}",
            "status: {$serviceProvider->status}",
            'city: '.($serviceProvider->city?->getTranslation('name', 'ar') ?? ''),
            'categories: '.$categoryNames->implode(', '),
            "reviews_count: {$reviewsCount}",
            "average_rating_out_of_5: {$avgRatingFive}",
            "completed_orders_count: {$completedOrdersCount}",
            'top_worked_city: '.((string) $topWorkedCityName),
            'top_worked_category: '.((string) $topWorkedCategoryName),
            'top_worked_service: '.((string) $topWorkedServiceName),
            'recent_reviews: '.$recentReviews->map(function ($review): string {
                $rating = number_format(((float) $review->rating) / 20, 1);
                $body = trim((string) ($review->body ?? ''));
                $reviewer = (string) ($review->user?->name ?? '');

                return trim("rating={$rating}; reviewer={$reviewer}; review={$body}");
            })->implode(' | '),
        ], fn ($value) => trim((string) $value) !== '');

        return [
            '_id' => (string) $serviceProvider->id,
            $textField => implode("\n", $textChunks),
            'type' => 'service_provider',
            'provider_id' => (int) $serviceProvider->id,
            'provider_name' => (string) $serviceProvider->name,
            'city_id' => $serviceProvider->city_id ? (int) $serviceProvider->city_id : null,
            'city_name' => $serviceProvider->city?->getTranslation('name', 'ar'),
            'status' => (string) $serviceProvider->status,
            'entity_type' => $serviceProvider->entity_type ? (string) $serviceProvider->entity_type : null,
            'category_ids' => $serviceProvider->categories->pluck('id')->map(fn ($id) => (string) $id)->values()->all(),
            'category_names' => $categoryNames->all(),
            'reviews_count' => $reviewsCount,
            'average_rating_percent' => $avgRatingPercent,
            'average_rating' => $avgRatingFive,
            'latest_review_at' => $latestReviewAt ? $latestReviewAt->toIso8601String() : null,
            'recent_review_texts' => $recentReviews
                ->pluck('body')
                ->filter()
                ->map(fn ($text) => trim((string) $text))
                ->values()
                ->all(),
            'recent_review_ratings' => $recentReviews
                ->pluck('rating')
                ->map(fn ($rating) => round(((float) $rating) / 20, 2))
                ->values()
                ->all(),
            'completed_orders_count' => $completedOrdersCount,
            'latest_completed_order_at' => $latestCompletedOrderAt ? $latestCompletedOrderAt->toIso8601String() : null,
            'worked_city_ids' => $workedCityIds->unique()->map(fn ($id) => (string) $id)->values()->all(),
            'worked_city_names' => $workedCityNames->unique()->values()->all(),
            'worked_category_ids' => $workedCategoryIds->unique()->map(fn ($id) => (string) $id)->values()->all(),
            'worked_category_names' => $workedCategoryNames->unique()->values()->all(),
            'worked_service_ids' => $workedServiceIds->unique()->map(fn ($id) => (string) $id)->values()->all(),
            'worked_service_names' => $workedServiceNames->unique()->values()->all(),
            'top_worked_city_id' => $topWorkedCityId ? (int) $topWorkedCityId : null,
            'top_worked_city_name' => $topWorkedCityName ? (string) $topWorkedCityName : null,
            'top_worked_category_id' => $topWorkedCategoryId ? (int) $topWorkedCategoryId : null,
            'top_worked_category_name' => $topWorkedCategoryName ? (string) $topWorkedCategoryName : null,
            'top_worked_service_id' => $topWorkedServiceId ? (int) $topWorkedServiceId : null,
            'top_worked_service_name' => $topWorkedServiceName ? (string) $topWorkedServiceName : null,
            'has_location' => $serviceProvider->latitude !== null && $serviceProvider->longitude !== null,
            'latitude' => $serviceProvider->latitude !== null ? (float) $serviceProvider->latitude : null,
            'longitude' => $serviceProvider->longitude !== null ? (float) $serviceProvider->longitude : null,
            'updated_at' => optional($serviceProvider->updated_at)?->toIso8601String(),
        ];
    }
}
