<?php

namespace App\Helpers;

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ExcelExport
{
    // GFS brand palette
    private const COLOR_HEADER_BG   = '0F1117';
    private const COLOR_HEADER_TEXT = 'FFFFFF';
    private const COLOR_COL_BG      = '1A2035';
    private const COLOR_COL_TEXT    = 'FFFFFF';
    private const COLOR_STRIPE      = 'F5F4F1';
    private const COLOR_BORDER      = 'E5E7EB';
    private const COLOR_SECTION_BG  = '22C55E';

    /**
     * Stream a single-sheet Excel download.
     *
     * @param  string   $filename   Output filename (with or without .xlsx)
     * @param  string   $title      Spreadsheet title shown in the first row
     * @param  array    $headers    Column header labels
     * @param  array    $rows       2D array of data rows
     */
    public static function download(
        string $filename,
        string $title,
        array  $headers,
        array  $rows
    ): StreamedResponse {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle(mb_substr($title, 0, 31));

        $totalCols = max(1, count($headers));
        $lastCol   = Coordinate::stringFromColumnIndex($totalCols);

        // ── Row 1: report title ──────────────────────────────────────────
        $sheet->setCellValue('A1', $title);
        $sheet->mergeCells("A1:{$lastCol}1");
        $sheet->getStyle('A1')->applyFromArray([
            'font'      => ['bold' => true, 'size' => 13, 'color' => ['rgb' => self::COLOR_HEADER_TEXT]],
            'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => self::COLOR_HEADER_BG]],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
        ]);
        $sheet->getRowDimension(1)->setRowHeight(22);

        // ── Row 2: column headers ────────────────────────────────────────
        $sheet->fromArray($headers, null, 'A2');
        $sheet->getStyle("A2:{$lastCol}2")->applyFromArray([
            'font'      => ['bold' => true, 'color' => ['rgb' => self::COLOR_COL_TEXT]],
            'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => self::COLOR_COL_BG]],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
        ]);
        $sheet->getRowDimension(2)->setRowHeight(16);

        // ── Data rows ────────────────────────────────────────────────────
        $rowNum = 3;
        foreach ($rows as $i => $row) {
            $sheet->fromArray(array_values($row), null, 'A' . $rowNum);
            if ($i % 2 === 1) {
                $sheet->getStyle("A{$rowNum}:{$lastCol}{$rowNum}")->applyFromArray([
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => self::COLOR_STRIPE]],
                ]);
            }
            $rowNum++;
        }

        // ── Border around the whole block ────────────────────────────────
        $lastRow = max(2, $rowNum - 1);
        $sheet->getStyle("A1:{$lastCol}{$lastRow}")->applyFromArray([
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => self::COLOR_BORDER]]],
        ]);

        // ── Auto column widths ───────────────────────────────────────────
        for ($c = 1; $c <= $totalCols; $c++) {
            $sheet->getColumnDimension(Coordinate::stringFromColumnIndex($c))->setAutoSize(true);
        }

        return self::stream($spreadsheet, $filename);
    }

    /**
     * Stream a multi-section Excel download.
     * Each section can optionally have a heading row before the column headers.
     *
     * $sections = [
     *   ['heading' => 'PAYMENT',  'headers' => ['Method','Qty','Amount'], 'rows' => [...]],
     *   ['heading' => 'VOID',     'headers' => ['Reason','Qty','Amount'], 'rows' => [...]],
     * ]
     */
    public static function downloadMultiSection(
        string $filename,
        string $title,
        array  $sections
    ): StreamedResponse {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle(mb_substr($title, 0, 31));

        // Find the widest section for title merge
        $maxCols = 1;
        foreach ($sections as $s) {
            $maxCols = max($maxCols, count($s['headers'] ?? []));
        }
        $lastCol = Coordinate::stringFromColumnIndex($maxCols);

        // Title
        $sheet->setCellValue('A1', $title);
        $sheet->mergeCells("A1:{$lastCol}1");
        $sheet->getStyle('A1')->applyFromArray([
            'font'      => ['bold' => true, 'size' => 13, 'color' => ['rgb' => self::COLOR_HEADER_TEXT]],
            'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => self::COLOR_HEADER_BG]],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
        ]);
        $sheet->getRowDimension(1)->setRowHeight(22);

        $rowNum = 2;
        foreach ($sections as $section) {
            $headers   = $section['headers']   ?? [];
            $rows      = $section['rows']      ?? [];
            $heading   = $section['heading']   ?? null;
            $colCount  = max(1, count($headers));
            $sLastCol  = Coordinate::stringFromColumnIndex($colCount);

            // Optional section heading
            if ($heading !== null) {
                $sheet->setCellValue("A{$rowNum}", $heading);
                $sheet->mergeCells("A{$rowNum}:{$sLastCol}{$rowNum}");
                $sheet->getStyle("A{$rowNum}")->applyFromArray([
                    'font'      => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                    'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => self::COLOR_SECTION_BG]],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_LEFT, 'vertical' => Alignment::VERTICAL_CENTER],
                ]);
                $sheet->getRowDimension($rowNum)->setRowHeight(16);
                $rowNum++;
            }

            // Column headers
            if (!empty($headers)) {
                $sheet->fromArray($headers, null, 'A' . $rowNum);
                $sheet->getStyle("A{$rowNum}:{$sLastCol}{$rowNum}")->applyFromArray([
                    'font'      => ['bold' => true, 'color' => ['rgb' => self::COLOR_COL_TEXT]],
                    'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => self::COLOR_COL_BG]],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
                ]);
                $rowNum++;
            }

            // Data
            foreach ($rows as $i => $row) {
                $sheet->fromArray(array_values($row), null, 'A' . $rowNum);
                if ($i % 2 === 1) {
                    $sheet->getStyle("A{$rowNum}:{$sLastCol}{$rowNum}")->applyFromArray([
                        'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => self::COLOR_STRIPE]],
                    ]);
                }
                $rowNum++;
            }

            $rowNum++; // blank gap between sections
        }

        // Auto-width
        for ($c = 1; $c <= $maxCols; $c++) {
            $sheet->getColumnDimension(Coordinate::stringFromColumnIndex($c))->setAutoSize(true);
        }

        return self::stream($spreadsheet, $filename);
    }

    // ── Private ──────────────────────────────────────────────────────────

    private static function stream(Spreadsheet $spreadsheet, string $filename): StreamedResponse
    {
        $filename = preg_replace('/\.csv$/i', '', $filename) . '.xlsx';
        $writer   = new Xlsx($spreadsheet);

        return response()->streamDownload(
            fn () => $writer->save('php://output'),
            $filename,
            [
                'Content-Type'        => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                'Cache-Control'       => 'max-age=0',
                'Content-Disposition' => 'attachment; filename="' . $filename . '"',
            ]
        );
    }
}
