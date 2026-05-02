<?php

/**
 * One-off: generates public/excel/sample-customers-10.xlsx for manual import testing.
 * Run: php scripts/generate-sample-import-xlsx.php
 */

declare(strict_types=1);

require dirname(__DIR__).'/vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

$dir = dirname(__DIR__).'/public/excel';
if (! is_dir($dir)) {
    mkdir($dir, 0755, true);
}

$path = $dir.'/sample-customers-10.xlsx';

$spreadsheet = new Spreadsheet;
$sheet = $spreadsheet->getActiveSheet();
$sheet->setRightToLeft(true);
$sheet->setCellValue('A1', 'phone');
$sheet->getStyle('A1')->applyFromArray([
    'font' => ['bold' => true],
    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'C6EFCE']],
]);
$sheet->getStyle('A')->getNumberFormat()->setFormatCode(NumberFormat::FORMAT_TEXT);
$sheet->getColumnDimension('A')->setWidth(18);

$nums = [
    '0500000001', '0500000002', '0500000003', '0500000004', '0500000005',
    '0500000006', '0500000007', '0500000008', '0500000009', '0500000010',
];

foreach ($nums as $i => $n) {
    $sheet->setCellValueExplicit('A'.($i + 2), $n, DataType::TYPE_STRING);
}

(new Xlsx($spreadsheet))->save($path);

echo "Wrote: {$path}\n";
