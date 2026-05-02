<?php

namespace App\Services\Pinecone;

use App\Models\Order;
use App\Models\Promotion;
use App\Models\Service;

class ServiceCatalogRecordBuilder
{
    public function build(Service $service): array
    {
        $textField = (string) config('services.pinecone.record_text_key', 'text');
        $service->loadMissing(['category.parent', 'highlights', 'promotion', 'media']);

        $nameAr = (string) $service->getTranslation('name', 'ar');
        $descAr = trim(strip_tags((string) $service->getTranslation('description', 'ar')));
        $category = $service->category;
        $categoryName = $category ? (string) $category->getTranslation('name', 'ar') : '';
        $parent = $category?->parent;
        $parentCategoryName = $parent ? (string) $parent->getTranslation('name', 'ar') : '';
        $parentCategoryId = $parent ? (int) $parent->id : null;

        $highlightTitles = $service->highlights
            ->map(fn ($h) => (string) $h->getTranslation('title', 'ar'))
            ->filter()
            ->values();

        $completedOrdersCount = $service->orders()
            ->where('status', Order::COMPLETED_STATUS)
            ->count();

        $promotion = $service->promotion;
        $promoLine = '';
        $promoName = '';
        if ($promotion) {
            $promoName = trim((string) ($promotion->name ?? ''));
            $promoLine = match ($promotion->type) {
                Promotion::PERCENTAGE_TYPE => 'عرض: خصم نسبة '.$promotion->value.'٪',
                Promotion::FIXED_TYPE => 'عرض: خصم مبلغ ثابت '.$promotion->value,
                default => 'عرض: متوفر',
            };
            if ($promoName !== '') {
                $promoLine = 'اسم العرض: '.$promoName.'. '.$promoLine;
            }
        }

        $badgeKey = $service->badge ? (string) $service->badge : '';
        $badgeLabel = $badgeKey !== '' ? trim((string) __("ui.badges.{$badgeKey}")) : '';
        if ($badgeLabel !== '' && ($badgeLabel === "ui.badges.{$badgeKey}" || str_starts_with($badgeLabel, 'ui.'))) {
            $badgeLabel = $badgeKey;
        }

        $categoryIdsForMeta = collect([
            $category?->id,
            $parentCategoryId,
        ])->filter()->map(fn ($id) => (string) $id)->unique()->values()->all();

        $textChunks = array_filter([
            "service_id: {$service->id}",
            "slug: {$service->slug}",
            "name: {$nameAr}",
            $descAr !== '' ? "description: {$descAr}" : '',
            $categoryName !== '' ? "category: {$categoryName}" : '',
            $parentCategoryName !== '' ? "parent_category: {$parentCategoryName}" : '',
            'highlights: '.$highlightTitles->implode(', '),
            'price: '.(string) $service->price,
            $badgeKey !== '' ? 'badge_key: '.$badgeKey : '',
            $badgeLabel !== '' ? 'badge: '.$badgeLabel : '',
            $service->warranty_days ? "warranty_days: {$service->warranty_days}" : '',
            $promoLine,
            "completed_orders: {$completedOrdersCount}",
            'rating_display: '.$service->getRating(),
        ], fn ($value) => trim((string) $value) !== '');

        return [
            '_id' => 'svc_'.$service->id,
            '_schema_version' => 'service_catalog_v1',
            $textField => implode("\n", $textChunks),
            'type' => 'service',
            'service_id' => (int) $service->id,
            'slug' => (string) $service->slug,
            'service_name' => $nameAr,
            'category_id' => $category ? (int) $category->id : null,
            'parent_category_id' => $parentCategoryId,
            'category_name' => $categoryName !== '' ? $categoryName : null,
            'parent_category_name' => $parentCategoryName !== '' ? $parentCategoryName : null,
            'category_ids' => $categoryIdsForMeta,
            'price' => (float) $service->price,
            'badge' => $service->badge ? (string) $service->badge : null,
            'warranty_days' => $service->warranty_days ? (int) $service->warranty_days : null,
            'completed_orders_count' => $completedOrdersCount,
            'highlights' => $highlightTitles->all(),
            'has_promotion' => $promotion !== null,
            'updated_at' => optional($service->updated_at)?->toIso8601String(),
        ];
    }
}
