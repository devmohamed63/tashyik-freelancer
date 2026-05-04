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
$sheet->setCellValue('A1', 'name');
$sheet->setCellValue('B1', 'phone');
$sheet->getStyle('A1:B1')->applyFromArray([
    'font' => ['bold' => true],
    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'C6EFCE']],
]);
$sheet->getStyle('B')->getNumberFormat()->setFormatCode(NumberFormat::FORMAT_TEXT);
$sheet->getColumnDimension('A')->setWidth(28);
$sheet->getColumnDimension('B')->setWidth(18);

// Keep in sync with UserController::import_sample_template() (phones 0500000001–0500000010).
$rows = [];
for ($i = 1; $i <= 10; $i++) {
    $rows[] = [
        "عميل تجريبي {$i}",
        sprintf('05000000%02d', $i),
    ];
}

foreach ($rows as $i => [$name, $phone]) {
    $r = $i + 2;
    $sheet->setCellValueExplicit('A'.$r, $name, DataType::TYPE_STRING);
    $sheet->setCellValueExplicit('B'.$r, $phone, DataType::TYPE_STRING);
}

$writer = new Xlsx($spreadsheet);

// Write to a temp path first. On Windows, saving directly over public/excel/*.xlsx often fails with
// "Resource temporarily unavailable" if Excel/Explorer preview/antivirus holds the target file open.
$tmpPath = sys_get_temp_dir().DIRECTORY_SEPARATOR.'sample-customers-10-'.uniqid('', true).'.xlsx';
$writer->save($tmpPath);

$finalPath = $path;

if (is_file($finalPath) && ! @unlink($finalPath)) {
    $fallbackPath = $dir.'/sample-customers-10-generated.xlsx';
    if (! copy($tmpPath, $fallbackPath)) {
        fwrite(STDERR, "ERROR: Could not remove locked file: {$finalPath}\n");
        fwrite(STDERR, "Close Excel or any program using that file, then run again.\n");
        @unlink($tmpPath);
        exit(1);
    }
    @unlink($tmpPath);
    echo "Wrote (original file was locked): {$fallbackPath}\n";
    echo "Tip: Close sample-customers-10.xlsx in Excel if it is open, delete the old file, then run this script again.\n";
    echo "Or use Dashboard → Import customers → Download sample file (test data).\n";
    exit(0);
}

if (! copy($tmpPath, $finalPath)) {
    fwrite(STDERR, "ERROR: Could not write: {$finalPath}\n");
    @unlink($tmpPath);
    exit(1);
}

@unlink($tmpPath);

echo "Wrote: {$finalPath}\n";
