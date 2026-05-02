<?php

namespace Tests\Unit;

use App\Livewire\Dashboard\Users\ImportCustomers;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use ReflectionMethod;

class ImportCustomersPhoneNormalizationTest extends TestCase
{
    private function normalise(string $raw): ?string
    {
        $component = new ImportCustomers;
        $method = new ReflectionMethod(ImportCustomers::class, 'normalisePhone');
        $method->setAccessible(true);

        return $method->invoke($component, $raw);
    }

    public static function validPhonesProvider(): array
    {
        return [
            'national_10_digits' => ['0501234567', '0501234567'],
            'with_spaces' => ['050 123 4567', '0501234567'],
            'with_plus_966_12_digits' => ['+966501234567', '0501234567'],
            'with_00_prefix' => ['00966501234567', '0501234567'],
            'leading_966_no_plus' => ['966501234567', '0501234567'],
        ];
    }

    #[DataProvider('validPhonesProvider')]
    public function test_normalises_valid_saudi_style_phones(string $input, string $expected): void
    {
        $this->assertSame($expected, $this->normalise($input));
    }

    public function test_rejects_wrong_length(): void
    {
        $this->assertNull($this->normalise('501234567'));
        $this->assertNull($this->normalise('050123456'));
        $this->assertNull($this->normalise(''));
        $this->assertNull($this->normalise('abc'));
    }
}
