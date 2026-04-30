<?php

namespace App\Utils\Traits\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

trait HasDraggableOrder
{
    /**
     * The "booted" method of the model.
     */
    protected static function bootHasDraggableOrder(): void
    {
        static::creating(function ($model) {
            $max = $model->maxDraggableIndex();

            $model->item_order = $max + 1;
        });
    }

    public function moveToOrder(Builder $builder, $itemId, $newOrder)
    {
        return DB::transaction(function () use ($builder, $itemId, $newOrder) {
            $items = $builder->orderBy('item_order')->get('id');

            $item = $items->firstWhere('id', $itemId);

            $items = $items->reject(fn($i) => $i->id === $itemId);

            $before = $items->slice(0, $newOrder - 1);
            $after  = $items->slice($newOrder - 1);

            $reordered = $before->push($item)->merge($after);

            foreach ($reordered as $index => $i) {
                $i->update(['item_order' => $index + 1]);
            }

            return $item->fresh();
        });
    }
}
