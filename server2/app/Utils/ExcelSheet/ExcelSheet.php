<?php

namespace App\Utils\ExcelSheet;

use Illuminate\Support\Collection;
use Illuminate\Database\Eloquent\Builder;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ExcelSheet
{
    protected Collection $columns;

    protected Builder $builder;

    protected Collection $records;

    protected Spreadsheet $spreadsheet;

    protected Worksheet $excelSheet;

    public function __construct(Collection $columns, Builder $builder)
    {
        $this->newExcelSheet();

        // Create header
        $this->getColumnsData($columns);
        $this->addHeader();

        // Insert records
        $this->records = $builder->get();
        $this->insertRecords();
    }

    protected function newExcelSheet(): void
    {
        $this->spreadsheet = new Spreadsheet();
        $this->excelSheet = $this->spreadsheet->getActiveSheet();

        // Set font
        $this->spreadsheet->getDefaultStyle()
            ->getFont()
            ->setName('Segoe UI')
            ->setSize(12);

        // Set direction
        $this->excelSheet->setRightToLeft(true);
    }

    protected function addHeader()
    {
        $col = 'A';

        foreach ($this->columns as $column) {
            $this->excelSheet->setCellValue("{$col}1", $column['label']);
            $this->excelSheet->getColumnDimension($col)->setAutoSize(true);
            $col++;
        }
    }

    protected function applyStyles()
    {
        $lastCol = $this->excelSheet->getHighestColumn();

        $this->excelSheet->getRowDimension(1)->setRowHeight(36);

        $this->excelSheet->getStyle("A1:{$lastCol}1")
            ->applyFromArray([
                'font' => [
                    'bold' => true,
                    'size' => 11,
                ],
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                    'vertical'   => Alignment::VERTICAL_CENTER,
                ],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => 'C6EFCE'],
                ],
            ]);

        for ($i = 2; $i <= $this->excelSheet->getHighestRow(); $i++) {
            $this->excelSheet->getStyle("A{$i}:{$lastCol}{$i}")
                ->applyFromArray([
                    'alignment' => [
                        'horizontal' => Alignment::HORIZONTAL_CENTER,
                        'vertical'   => Alignment::VERTICAL_CENTER,
                    ],
                ]);

            $this->excelSheet->getRowDimension($i)->setRowHeight(25);
        }
    }

    protected function insertRecords(): void
    {
        $index = 2;

        foreach ($this->records as $record) {
            $col = 'A';

            foreach ($this->columns as $column) {

                if (isset($column['callback'])) {
                    // Callback case
                    $value = $column['callback']($record);
                } else if (isset($column['dateFormat'])) {
                    // Date format case
                    $value = $record->{$column['name']} ? $record->{$column['name']}->isoFormat(config('app.time_format')) : '-';
                } else if (isset($column['relation'])) {
                    // Relation case
                    $value = $record->{$column['relation'][0]}?->{$column['relation'][1]} ?? __('ui.not_exists');
                } else if (isset($column['customValue'])) {
                    // Custom value case
                    $value = $column['customValue']($record);
                } else {
                    $value = $record->{$column['name']};
                }

                $this->excelSheet->setCellValue("{$col}{$index}", $value);

                $col++;
            }

            $index++;
        }

        $this->applyStyles();
    }

    protected function getColumnsData(Collection $columns): void
    {
        $data = $columns->map(function ($item) {
            return $item->getData();
        });

        $this->columns = $data;
    }

    public function export(string $filename)
    {
        if (!is_dir(public_path('excel'))) {
            mkdir(public_path('excel'));
        }

        $filePath = "excel/$filename.xlsx";
        $writer = new Xlsx($this->spreadsheet);
        $writer->save($filePath);

        return redirect(asset($filePath) . '?t=' . now()->getTimestamp());
    }
}
