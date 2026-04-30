<?php

namespace App\Utils\Traits;

trait HasTax
{
    public function getTaxes($amount)
    {
        $taxRate = config('app.tax_rate');

        $tax = $taxRate > 0
            ? ($amount * $taxRate / 100)
            : 0;

        return $tax;
    }
}
