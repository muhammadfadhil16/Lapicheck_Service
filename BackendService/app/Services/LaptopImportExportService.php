<?php

namespace App\Services;

use App\Models\Laptop;
use App\Models\LaptopBrand;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Writer\Csv;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\StreamedResponse;

class LaptopImportExportService
{
    /**
     * Import laptops from uploaded XLSX, XLS, or CSV file.
     *
     * @return array{total_rows: int, imported_count: int, updated_count: int, errors: array<array{row: int, message: string}>}
     */
    public function import(UploadedFile $file): array
    {
        $spreadsheet = IOFactory::load($file->getRealPath());
        $sheet = $spreadsheet->getActiveSheet();
        $rows = $sheet->toArray(null, true, false, true);

        if (empty($rows)) {
            return [
                'total_rows'     => 0,
                'imported_count' => 0,
                'updated_count'  => 0,
                'errors'         => [['row' => 1, 'message' => 'File kosong atau format tidak sesuai.']],
            ];
        }

        // Find header row and map column positions
        $headerRow = array_shift($rows);
        $colMap = $this->resolveHeaderColumns($headerRow);

        if (!isset($colMap['brand'], $colMap['model'])) {
            return [
                'total_rows'     => 0,
                'imported_count' => 0,
                'updated_count'  => 0,
                'errors'         => [['row' => 1, 'message' => 'Kolom wajib (Brand dan Model Laptop) tidak ditemukan pada baris header.']],
            ];
        }

        $imported = 0;
        $updated = 0;
        $errors = [];
        $currentRowIndex = 1;

        $currentMonth = (int) now()->format('n');
        $currentYear = (int) now()->format('Y');

        foreach ($rows as $row) {
            $currentRowIndex++;

            $brandName = trim((string) ($row[$colMap['brand']] ?? ''));
            $modelName = trim((string) ($row[$colMap['model']] ?? ''));
            $processorRaw = isset($colMap['processor']) ? ($row[$colMap['processor']] ?? '') : '';
            $processor = trim((string) $processorRaw);
            $benchmarkRaw = isset($colMap['benchmark']) ? ($row[$colMap['benchmark']] ?? null) : null;
            $priceRaw = isset($colMap['price']) ? ($row[$colMap['price']] ?? null) : null;
            $monthRaw = isset($colMap['month']) ? ($row[$colMap['month']] ?? null) : null;
            $yearRaw = isset($colMap['year']) ? ($row[$colMap['year']] ?? null) : null;

            // Skip empty rows
            if ($brandName === '' && $modelName === '' && $processor === '') {
                continue;
            }

            // Validations
            if ($brandName === '') {
                $errors[] = ['row' => $currentRowIndex, 'message' => 'Nama Brand wajib diisi.'];
                continue;
            }

            if ($modelName === '') {
                $errors[] = ['row' => $currentRowIndex, 'message' => 'Model Laptop wajib diisi.'];
                continue;
            }

            // Default processor if empty
            if ($processor === '') {
                $processor = '-';
            }

            // Parse benchmark score
            $benchmark = 0;
            if ($benchmarkRaw !== null && $benchmarkRaw !== '') {
                $cleanBenchmark = preg_replace('/[^0-9]/', '', (string) $benchmarkRaw);
                $benchmark = $cleanBenchmark !== '' ? (int) $cleanBenchmark : 0;
            }

            $category = $benchmark <= 7999 ? 'Rendah' : ($benchmark <= 18000 ? 'Sedang' : 'Tinggi');

            // Parse price
            $marketPrice = 0;
            if ($priceRaw !== null && $priceRaw !== '') {
                $cleanPrice = preg_replace('/[^0-9]/', '', (string) $priceRaw);
                $marketPrice = (int) $cleanPrice;
            }

            // Parse month & year
            $priceMonth = null;
            if ($monthRaw !== null && $monthRaw !== '' && is_numeric($monthRaw)) {
                $m = (int) $monthRaw;
                if ($m >= 1 && $m <= 12) {
                    $priceMonth = $m;
                }
            }

            $priceYear = null;
            if ($yearRaw !== null && $yearRaw !== '' && is_numeric($yearRaw)) {
                $y = (int) $yearRaw;
                if ($y >= 2000 && $y <= 2100) {
                    $priceYear = $y;
                }
            }

            if ($marketPrice > 0) {
                $priceMonth = $priceMonth ?? $currentMonth;
                $priceYear = $priceYear ?? $currentYear;
            }

            // Find or create Brand
            $brand = LaptopBrand::withTrashed()
                ->whereRaw('LOWER(name) = ?', [mb_strtolower($brandName)])
                ->first();

            if ($brand) {
                if ($brand->trashed()) {
                    $brand->restore();
                }
            } else {
                $brand = LaptopBrand::create(['name' => $brandName]);
            }

            // Find existing laptop
            $laptop = Laptop::withTrashed()
                ->where('brand_id', $brand->id)
                ->whereRaw('LOWER(model_name) = ?', [mb_strtolower($modelName)])
                ->first();

            $dataToSave = [
                'brand_id'        => $brand->id,
                'model_name'      => $modelName,
                'processor_name'  => $processor,
                'benchmark_score' => $benchmark,
                'category'        => $category,
                'market_price'    => $marketPrice,
                'price_month'     => $priceMonth,
                'price_year'      => $priceYear,
                'price_updated_at'=> $marketPrice > 0 ? now() : null,
            ];

            if ($laptop) {
                if ($laptop->trashed()) {
                    $laptop->restore();
                }
                $laptop->update($dataToSave);
                $updated++;
            } else {
                Laptop::create($dataToSave);
                $imported++;
            }
        }

        return [
            'total_rows'     => $currentRowIndex - 1,
            'imported_count' => $imported,
            'updated_count'  => $updated,
            'errors'         => $errors,
        ];
    }

    /**
     * Map header column letters to normalized field names.
     *
     * @param array<string, mixed> $headerRow
     * @return array<string, string>
     */
    protected function resolveHeaderColumns(array $headerRow): array
    {
        $map = [];

        foreach ($headerRow as $colLetter => $colValue) {
            $normalized = strtolower(trim((string) $colValue));
            $normalized = preg_replace('/[^a-z0-9]/', '', $normalized);

            if (str_contains($normalized, 'brand') || str_contains($normalized, 'merk')) {
                $map['brand'] = $colLetter;
            } elseif (str_contains($normalized, 'model') || str_contains($normalized, 'namalaptop')) {
                $map['model'] = $colLetter;
            } elseif (str_contains($normalized, 'processor') || str_contains($normalized, 'cpu') || str_contains($normalized, 'prosesor')) {
                $map['processor'] = $colLetter;
            } elseif (str_contains($normalized, 'benchmark') || str_contains($normalized, 'skor')) {
                $map['benchmark'] = $colLetter;
            } elseif (str_contains($normalized, 'harga') || str_contains($normalized, 'price') || str_contains($normalized, 'pasaran')) {
                $map['price'] = $colLetter;
            } elseif (str_contains($normalized, 'bulan') || str_contains($normalized, 'month')) {
                $map['month'] = $colLetter;
            } elseif (str_contains($normalized, 'tahun') || str_contains($normalized, 'year')) {
                $map['year'] = $colLetter;
            }
        }

        return $map;
    }

    /**
     * Generate template spreadsheet.
     */
    public function generateTemplate(string $format = 'xlsx'): StreamedResponse
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Template Import Laptop');

        $headers = [
            'A1' => 'Brand',
            'B1' => 'Model Laptop',
            'C1' => 'Processor',
            'D1' => 'Skor Benchmark',
            'E1' => 'Harga Pasaran (Rp)',
            'F1' => 'Bulan (1-12)',
            'G1' => 'Tahun',
        ];

        foreach ($headers as $cell => $text) {
            $sheet->setCellValue($cell, $text);
        }

        // Header style
        $headerStyle = [
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '1D4ED8']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
        ];
        $sheet->getStyle('A1:G1')->applyFromArray($headerStyle);
        $sheet->getRowDimension(1)->setRowHeight(28);

        // Example rows
        $sampleData = [
            ['ASUS', 'VivoBook 14 A416MA', 'Intel Celeron N4020', 1560, 3500000, (int) now()->format('n'), (int) now()->format('Y')],
            ['Lenovo', 'IdeaPad Slim 3 14IAU7', 'Intel Core i3-1215U', 11200, 6200000, (int) now()->format('n'), (int) now()->format('Y')],
            ['Acer', 'Nitro 5 AN515-58', 'Intel Core i5-12500H', 21400, 12800000, (int) now()->format('n'), (int) now()->format('Y')],
        ];

        $rowIdx = 2;
        foreach ($sampleData as $row) {
            $sheet->fromArray($row, null, 'A' . $rowIdx);
            $rowIdx++;
        }

        // Auto column width
        foreach (range('A', 'G') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        $filename = 'template_import_laptop_' . date('Ymd_His') . '.' . ($format === 'csv' ? 'csv' : 'xlsx');

        return response()->streamDownload(function () use ($spreadsheet, $format) {
            if ($format === 'csv') {
                $writer = new Csv($spreadsheet);
            } else {
                $writer = new Xlsx($spreadsheet);
            }
            $writer->save('php://output');
        }, $filename, [
            'Content-Type'        => $format === 'csv' ? 'text/csv' : 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Cache-Control'       => 'max-age=0, no-cache, must-revalidate',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ]);
    }

    /**
     * Generate export spreadsheet of laptops.
     */
    public function export(Collection $laptops, string $format = 'xlsx'): StreamedResponse
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Data Laptop');

        $headers = [
            'A1' => 'No',
            'B1' => 'Brand',
            'C1' => 'Model Laptop',
            'D1' => 'Processor',
            'E1' => 'Skor Benchmark',
            'F1' => 'Kategori',
            'G1' => 'Harga Pasaran (Rp)',
            'H1' => 'Bulan',
            'I1' => 'Tahun',
            'J1' => 'Terakhir Diperbarui',
        ];

        foreach ($headers as $cell => $text) {
            $sheet->setCellValue($cell, $text);
        }

        $headerStyle = [
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '0F766E']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
        ];
        $sheet->getStyle('A1:J1')->applyFromArray($headerStyle);
        $sheet->getRowDimension(1)->setRowHeight(28);

        $rowIdx = 2;
        $no = 1;
        foreach ($laptops as $laptop) {
            $sheet->setCellValue('A' . $rowIdx, $no++);
            $sheet->setCellValue('B' . $rowIdx, $laptop->brand->name ?? '-');
            $sheet->setCellValue('C' . $rowIdx, $laptop->model_name);
            $sheet->setCellValue('D' . $rowIdx, $laptop->processor_name);
            $sheet->setCellValue('E' . $rowIdx, $laptop->benchmark_score);
            $sheet->setCellValue('F' . $rowIdx, $laptop->category);
            $sheet->setCellValue('G' . $rowIdx, $laptop->market_price ?? 0);
            $sheet->setCellValue('H' . $rowIdx, $laptop->price_month ?? '-');
            $sheet->setCellValue('I' . $rowIdx, $laptop->price_year ?? '-');
            $sheet->setCellValue('J' . $rowIdx, $laptop->price_updated_at ? $laptop->price_updated_at->format('Y-m-d H:i') : ($laptop->updated_at ? $laptop->updated_at->format('Y-m-d H:i') : '-'));
            $rowIdx++;
        }

        foreach (range('A', 'J') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        $filename = 'export_data_laptop_' . date('Ymd_His') . '.' . ($format === 'csv' ? 'csv' : 'xlsx');

        return response()->streamDownload(function () use ($spreadsheet, $format) {
            if ($format === 'csv') {
                $writer = new Csv($spreadsheet);
            } else {
                $writer = new Xlsx($spreadsheet);
            }
            $writer->save('php://output');
        }, $filename, [
            'Content-Type'        => $format === 'csv' ? 'text/csv' : 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Cache-Control'       => 'max-age=0, no-cache, must-revalidate',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ]);
    }
}
