<?php

namespace Tests\Feature;

use App\Livewire\Dashboard\Users\ImportCustomers;
use App\Models\User;
use Illuminate\Http\Testing\File as TestingFile;
use Livewire\Livewire;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Tests\Support\ExposesImportCustomersReadRows;
use Tests\TestCase;

class ImportCustomersImportTest extends TestCase
{
    /** @var list<string> */
    private array $tempFiles = [];

    protected function tearDown(): void
    {
        foreach ($this->tempFiles as $path) {
            if (is_string($path) && is_file($path)) {
                @unlink($path);
            }
        }
        $this->tempFiles = [];

        parent::tearDown();
    }

    /**
     * @param  list<array{0: string, 1: string}>  $rows  Each row: [column A name, column B phone]
     */
    protected function writeCustomerImportXlsx(array $rows): string
    {
        $spreadsheet = new Spreadsheet;
        $sheet = $spreadsheet->getActiveSheet();
        foreach ($rows as $i => $row) {
            $r = $i + 1;
            $sheet->setCellValue([1, $r], $row[0]);
            $sheet->setCellValue([2, $r], $row[1]);
        }
        $path = sys_get_temp_dir().'/import-test-'.uniqid('', true).'.xlsx';
        (new Xlsx($spreadsheet))->save($path);
        $this->tempFiles[] = $path;

        return $path;
    }

    public function test_read_rows_skips_header_without_digits(): void
    {
        $path = $this->writeCustomerImportXlsx([
            ['الاسم', 'رقم الموبايل'],
            ['أحمد', '0501112233'],
        ]);

        $reader = new ExposesImportCustomersReadRows;
        [$valid, $invalid] = $reader->readRowsFromPath($path);

        $this->assertCount(1, $valid);
        $this->assertSame('أحمد', $valid[0]['name']);
        $this->assertSame('0501112233', $valid[0]['phone']);
        $this->assertSame(2, $valid[0]['row']);
        $this->assertSame([], $invalid);
    }

    public function test_read_rows_accepts_empty_name(): void
    {
        $path = $this->writeCustomerImportXlsx([
            ['', '0502223344'],
        ]);

        $reader = new ExposesImportCustomersReadRows;
        [$valid, $invalid] = $reader->readRowsFromPath($path);

        $this->assertCount(1, $valid);
        $this->assertNull($valid[0]['name']);
        $this->assertSame('0502223344', $valid[0]['phone']);
        $this->assertSame([], $invalid);
    }

    public function test_read_rows_rejects_name_longer_than_100_chars(): void
    {
        $long = str_repeat('ن', 101);
        $path = $this->writeCustomerImportXlsx([
            [$long, '0503334455'],
        ]);

        $reader = new ExposesImportCustomersReadRows;
        [$valid, $invalid] = $reader->readRowsFromPath($path);

        $this->assertSame([], $valid);
        $this->assertCount(1, $invalid);
        $this->assertSame(1, $invalid[0]['row']);
        $this->assertSame(__('ui.name_too_long'), $invalid[0]['reason']);
    }

    public function test_read_rows_rejects_invalid_phone_even_when_name_ok(): void
    {
        $path = $this->writeCustomerImportXlsx([
            ['علي', '123'],
        ]);

        $reader = new ExposesImportCustomersReadRows;
        [$valid, $invalid] = $reader->readRowsFromPath($path);

        $this->assertSame([], $valid);
        $this->assertCount(1, $invalid);
        $this->assertSame(__('ui.phone_must_be_10_digits'), $invalid[0]['reason']);
    }

    public function test_read_rows_collapses_internal_whitespace_in_name(): void
    {
        $path = $this->writeCustomerImportXlsx([
            ["أحمد   محمد\tعلي", '0504445566'],
        ]);

        $reader = new ExposesImportCustomersReadRows;
        [$valid] = $reader->readRowsFromPath($path);

        $this->assertSame('أحمد محمد علي', $valid[0]['name']);
    }

    public function test_read_rows_skips_completely_empty_rows(): void
    {
        $path = $this->writeCustomerImportXlsx([
            ['', ''],
            ['سالم', '0505556677'],
        ]);

        $reader = new ExposesImportCustomersReadRows;
        [$valid, $invalid] = $reader->readRowsFromPath($path);

        $this->assertCount(1, $valid);
        $this->assertSame('سالم', $valid[0]['name']);
        $this->assertSame([], $invalid);
    }

    public function test_import_surfaces_empty_file_when_sheet_has_no_data_rows(): void
    {
        $admin = User::factory()->create();
        $admin->givePermissionTo('create users');

        $path = $this->writeCustomerImportXlsx([
            ['الاسم', 'الموبايل'],
            ['', ''],
        ]);

        $file = TestingFile::createWithContent('empty.xlsx', (string) file_get_contents($path));

        Livewire::actingAs($admin)
            ->test(ImportCustomers::class)
            ->upload('file', [$file])
            ->call('import')
            ->assertSet('result', null)
            ->assertSet('error', __('ui.empty_file'));
    }

    public function test_import_stores_custom_name_when_provided(): void
    {
        $admin = User::factory()->create();
        $admin->givePermissionTo('create users');

        $phone = '0598765432';
        $this->assertDatabaseMissing('users', ['phone' => $phone]);

        $path = $this->writeCustomerImportXlsx([
            ['الاسم', 'الموبايل'],
            ['خالد إبراهيم', $phone],
        ]);

        $file = TestingFile::createWithContent('customers.xlsx', (string) file_get_contents($path));

        Livewire::actingAs($admin)
            ->test(ImportCustomers::class)
            ->upload('file', [$file])
            ->call('import')
            ->assertSet('error', null)
            ->assertSet('result.imported', 1);

        $this->assertDatabaseHas('users', [
            'phone' => $phone,
            'name' => 'خالد إبراهيم',
            'type' => User::USER_ACCOUNT_TYPE,
        ]);
    }

    public function test_import_uses_placeholder_name_when_cell_empty(): void
    {
        $admin = User::factory()->create();
        $admin->givePermissionTo('create users');

        $phone = '0587654321';
        $path = $this->writeCustomerImportXlsx([
            ['', $phone],
        ]);

        $file = TestingFile::createWithContent('customers.xlsx', (string) file_get_contents($path));

        Livewire::actingAs($admin)
            ->test(ImportCustomers::class)
            ->upload('file', [$file])
            ->call('import')
            ->assertSet('result.imported', 1);

        $this->assertDatabaseHas('users', [
            'phone' => $phone,
            'name' => __('ui.imported_customer'),
        ]);
    }

    public function test_import_second_duplicate_row_can_fill_name_if_first_had_none(): void
    {
        $admin = User::factory()->create();
        $admin->givePermissionTo('create users');

        $phone = '0576543210';
        $path = $this->writeCustomerImportXlsx([
            ['', $phone],
            ['مراد', $phone],
        ]);

        $file = TestingFile::createWithContent('customers.xlsx', (string) file_get_contents($path));

        Livewire::actingAs($admin)
            ->test(ImportCustomers::class)
            ->upload('file', [$file])
            ->call('import')
            ->assertSet('result.imported', 1)
            ->assertSet('result.duplicates', 1);

        $this->assertDatabaseHas('users', [
            'phone' => $phone,
            'name' => 'مراد',
        ]);

        $this->assertSame(1, User::where('phone', $phone)->count());
    }

    public function test_import_first_name_wins_when_later_row_has_empty_name(): void
    {
        $admin = User::factory()->create();
        $admin->givePermissionTo('create users');

        $phone = '0565432109';
        $path = $this->writeCustomerImportXlsx([
            ['نادر', $phone],
            ['', $phone],
        ]);

        $file = TestingFile::createWithContent('customers.xlsx', (string) file_get_contents($path));

        Livewire::actingAs($admin)
            ->test(ImportCustomers::class)
            ->upload('file', [$file])
            ->call('import')
            ->assertSet('result.imported', 1)
            ->assertSet('result.duplicates', 1);

        $this->assertDatabaseHas('users', [
            'phone' => $phone,
            'name' => 'نادر',
        ]);
    }
}
