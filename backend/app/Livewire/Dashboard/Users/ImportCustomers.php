<?php

namespace App\Livewire\Dashboard\Users;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Livewire\Component;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Livewire\WithFileUploads;
use PhpOffice\PhpSpreadsheet\IOFactory;

class ImportCustomers extends Component
{
    use WithFileUploads;

    /** Hard cap to keep memory + UX sane; over this we'd queue the work. */
    public const MAX_ROWS = 5000;

    /** Insert chunk size: balances round-trips against query length. */
    public const CHUNK_SIZE = 500;

    public ?TemporaryUploadedFile $file = null;

    /**
     * Final result returned by import(); null until the run completes.
     *
     * @var array{imported:int,duplicates:int,trashed:int,invalid:array<int,array{row:int,value:string,reason:string}>,total:int}|null
     */
    public ?array $result = null;

    public ?string $error = null;

    protected function rules(): array
    {
        return [
            'file' => ['required', 'file', 'mimes:xlsx,xls,csv', 'max:10240'],
        ];
    }

    public function mount(): void
    {
        $this->authorize('create', User::class);
    }

    public function updatedFile(): void
    {
        $this->result = null;
        $this->error = null;
        $this->resetValidation('file');
    }

    public function import(): void
    {
        $this->authorize('create', User::class);

        $this->validate();

        $this->result = null;
        $this->error = null;

        try {
            [$validRows, $invalidRows] = $this->readPhonesFromFile($this->file->getRealPath());
        } catch (\RuntimeException $e) {
            // Validation-style errors (e.g. row cap exceeded) surface their own message.
            $this->error = $e->getMessage();

            return;
        } catch (\Throwable $e) {
            Log::error('Customers import: failed to read file', ['exception' => $e]);
            $this->error = __('ui.unexpected_error');

            return;
        }

        if ($validRows === [] && $invalidRows === []) {
            $this->error = __('ui.empty_file');

            return;
        }

        // Dedupe within the file itself before hitting the DB so the diff math
        // (sent vs affected) cleanly maps to "DB duplicates".
        $uniquePhones = [];
        $totalRequested = count($validRows);
        foreach ($validRows as $row) {
            $uniquePhones[$row['phone']] = $row['row'];
        }

        $imported = 0;
        $duplicates = $totalRequested - count($uniquePhones);
        $allDuplicatePhones = [];

        $now = now();
        $passwordPlaceholder = Hash::make(Str::random(40));
        $namePlaceholder = __('ui.imported_customer');

        foreach (array_chunk(array_keys($uniquePhones), self::CHUNK_SIZE) as $chunk) {
            $payload = array_map(fn ($phone) => [
                'name' => $namePlaceholder,
                'phone' => $phone,
                'password' => $passwordPlaceholder,
                'type' => User::USER_ACCOUNT_TYPE,
                'status' => User::ACTIVE_STATUS,
                'imported_at' => $now,
                'created_at' => $now,
                'updated_at' => $now,
            ], $chunk);

            $affected = DB::transaction(fn () => User::insertOrIgnore($payload));

            $chunkDuplicates = count($payload) - $affected;
            $imported += $affected;
            $duplicates += $chunkDuplicates;

            if ($chunkDuplicates > 0) {
                // Identify which phones were skipped so we can categorise them as trashed vs active.
                $existing = User::withTrashed()
                    ->whereIn('phone', $chunk)
                    ->pluck('phone')
                    ->all();

                $allDuplicatePhones = array_merge($allDuplicatePhones, $existing);
            }
        }

        $trashedDuplicates = 0;
        if ($allDuplicatePhones !== []) {
            $trashedDuplicates = User::onlyTrashed()
                ->whereIn('phone', array_unique($allDuplicatePhones))
                ->count();
        }

        $this->result = [
            'imported' => $imported,
            'duplicates' => $duplicates,
            'trashed' => $trashedDuplicates,
            'invalid' => $invalidRows,
            'total' => $totalRequested + count($invalidRows),
        ];

        // Free up the livewire-tmp file ASAP; don't wait for the scheduled cleanup.
        try {
            $this->file?->delete();
        } catch (\Throwable $e) {
            // Non-fatal; tmp cleanup runs on schedule too.
        }

        $this->file = null;
        $this->dispatch('refreshTable');
    }

    public function resetForm(): void
    {
        $this->file = null;
        $this->result = null;
        $this->error = null;
        $this->resetValidation();
    }

    /**
     * Read the uploaded spreadsheet, returning [validRows, invalidRows].
     * - validRows: list of ['row' => int, 'phone' => string] with normalised 10-digit phones.
     * - invalidRows: list of ['row' => int, 'value' => string, 'reason' => string].
     *
     * @return array{0: list<array{row:int,phone:string}>, 1: list<array{row:int,value:string,reason:string}>}
     */
    protected function readPhonesFromFile(string $path): array
    {
        $spreadsheet = IOFactory::load($path);
        $sheet = $spreadsheet->getActiveSheet();
        $highestRow = $sheet->getHighestDataRow();

        if ($highestRow > self::MAX_ROWS + 1) {
            // +1 allowance for an optional header row.
            throw new \RuntimeException(__('ui.max_rows_exceeded', ['max' => self::MAX_ROWS]));
        }

        $valid = [];
        $invalid = [];
        $headerSkipped = false;

        for ($rowNumber = 1; $rowNumber <= $highestRow; $rowNumber++) {
            $rawValue = $sheet->getCell([1, $rowNumber])->getValue();

            // Trim and stringify; treats Excel auto-numeric cells too.
            $original = trim((string) $rawValue);

            if ($original === '') {
                continue;
            }

            // Header detection: only kicks in for the very first non-empty row,
            // and only if it doesn't look like a phone (no digits at all, or starts with letters).
            if (!$headerSkipped && !preg_match('/\d/', $original)) {
                $headerSkipped = true;

                continue;
            }
            $headerSkipped = true;

            $normalised = $this->normalisePhone($original);

            if ($normalised === null) {
                $invalid[] = [
                    'row' => $rowNumber,
                    'value' => $original,
                    'reason' => __('ui.phone_must_be_10_digits'),
                ];

                continue;
            }

            $valid[] = ['row' => $rowNumber, 'phone' => $normalised];
        }

        return [$valid, $invalid];
    }

    /**
     * Normalise a raw cell value to a 10-digit phone, or null if invalid.
     * - Strips non-digits.
     * - Strips leading 966 country code.
     * - Strips a single leading 0.
     */
    protected function normalisePhone(string $raw): ?string
    {
        $digits = preg_replace('/\D+/', '', $raw) ?? '';

        if ($digits === '') {
            return null;
        }

        // Strip a single international prefix (966…) once; tolerate a leading 00.
        if (str_starts_with($digits, '00')) {
            $digits = substr($digits, 2);
        }

        if (str_starts_with($digits, '966') && strlen($digits) >= 12) {
            $digits = substr($digits, 3);
            // E.164 for Saudi mobiles is 966 + 9 digits (5XXXXXXXX). We store national 05XXXXXXXX.
            if (strlen($digits) === 9 && str_starts_with($digits, '5')) {
                $digits = '0'.$digits;
            }
        } elseif (strlen($digits) === 11 && str_starts_with($digits, '0')) {
            $digits = substr($digits, 1);
        }

        return preg_match('/^\d{10}$/', $digits) === 1 ? $digits : null;
    }

    public function render()
    {
        return view('livewire.dashboard.users.import-customers');
    }
}
