<?php

namespace Tests\Support;

use App\Livewire\Dashboard\Users\ImportCustomers;

/**
 * Test double: exposes protected readRowsFromFile without reflection boilerplate in every test.
 */
final class ExposesImportCustomersReadRows extends ImportCustomers
{
    /**
     * @return array{0: list<array{row:int,name:?string,phone:string}>, 1: list<array{row:int,value:string,reason:string}>}
     */
    public function readRowsFromPath(string $path): array
    {
        return $this->readRowsFromFile($path);
    }
}
