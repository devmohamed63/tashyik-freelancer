<?php

namespace App\Utils\Traits\Models;

use App\Http\Resources\PriceResource;

trait HasPrice
{
    public function printPrice(): string
    {
        /** @disregard P1012 */
        $price = $this->{static::PRICE_COLUMN};

        if ($price <= 0) return __('ui.none');

        return number_format($price, config('app.decimal_places')) . ' ' . __('ui.currency');
    }

    public function getPrice(): PriceResource
    {
        /** @disregard P1012 */
        return new PriceResource([$this->{static::PRICE_COLUMN}]);
    }
}
