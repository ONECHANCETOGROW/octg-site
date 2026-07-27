<?php

declare(strict_types=1);

namespace App\Modules\MarketingIntel\Parsing;

use ZipArchive;

/**
 * Minimal, dependency-free .xlsx reader — no PhpSpreadsheet, no Composer
 * package, per this app's zero-Composer-dependency architecture. An .xlsx
 * file is a zip archive of XML parts; this reads exactly the two parts
 * needed to recover a flat data table:
 *
 *   - xl/sharedStrings.xml  (string pool most cell text is indexed into)
 *   - xl/worksheets/sheet1.xml  (the first worksheet's cell grid)
 *
 * Known, documented scope limits (see MARKETING_INTEL_ARCHITECTURE.md and
 * the Developer Handoff's "Known Limitations" section): only the first
 * worksheet is read; cell styles/formatting are ignored; formulas are read
 * from their cached value, not recalculated; encrypted/password-protected
 * workbooks are not supported. This covers the common case this feature
 * actually needs — a flat "export as Excel" report from an ad platform or
 * analytics tool — without pretending to be a general OOXML implementation.
 */
final class ExcelParser implements FileParserInterface
{
    public function code(): string
    {
        return 'excel_parser';
    }

    public function parseFile(string $filePath, array $expectedColumns): ParsedPayload
    {
        if (!is_file($filePath)) {
            return ParsedPayload::empty('excel_file_not_found');
        }

        $zip = new ZipArchive();
        if ($zip->open($filePath) !== true) {
            return ParsedPayload::empty('excel_not_a_valid_zip');
        }

        $sharedStrings = $this->readSharedStrings($zip);
        $sheetXml = $zip->getFromName('xl/worksheets/sheet1.xml');
        $zip->close();

        if ($sheetXml === false) {
            return ParsedPayload::empty('excel_missing_sheet1');
        }

        $grid = $this->readSheetGrid($sheetXml, $sharedStrings);
        if ($grid === []) {
            return ParsedPayload::empty('excel_sheet_empty');
        }

        $header = array_map(static fn ($v): string => trim((string) $v), $grid[0]);
        $normalizedExpected = array_map(
            static fn (string $c): string => strtolower(trim($c)),
            $expectedColumns
        );

        $rows = [];
        $fieldConfidence = [];

        for ($r = 1; $r < count($grid); $r++) {
            $row = [];
            foreach ($header as $columnIndex => $columnName) {
                if ($columnName === '') {
                    continue;
                }

                $value = trim((string) ($grid[$r][$columnIndex] ?? ''));
                $row[$columnName] = $value;

                $rowNumber = $r - 1;
                $matchesExpected = in_array(strtolower($columnName), $normalizedExpected, true);
                $fieldConfidence["row{$rowNumber}.{$columnName}"] = match (true) {
                    $value === '' => 25,
                    $matchesExpected || $normalizedExpected === [] => 93,
                    default => 78,
                };
            }

            if ($row !== []) {
                $rows[] = $row;
            }
        }

        return new ParsedPayload($rows, [], array_values(array_filter($header)), $fieldConfidence, []);
    }

    /**
     * @return array<int,string>
     */
    private function readSharedStrings(ZipArchive $zip): array
    {
        $xml = $zip->getFromName('xl/sharedStrings.xml');
        if ($xml === false) {
            return [];
        }

        $previous = libxml_use_internal_errors(true);
        $parsed = simplexml_load_string($xml);
        libxml_use_internal_errors($previous);

        if ($parsed === false) {
            return [];
        }

        $strings = [];
        foreach ($parsed->si as $si) {
            // Plain text: <si><t>text</t></si>
            // Rich text runs: <si><r><t>part</t></r><r><t>part2</t></r></si>
            if (isset($si->t)) {
                $strings[] = (string) $si->t;
                continue;
            }

            $combined = '';
            foreach ($si->r as $run) {
                $combined .= (string) $run->t;
            }

            $strings[] = $combined;
        }

        return $strings;
    }

    /**
     * @param array<int,string> $sharedStrings
     * @return array<int,array<int,string>> row index => column index => value
     */
    private function readSheetGrid(string $sheetXml, array $sharedStrings): array
    {
        $previous = libxml_use_internal_errors(true);
        $parsed = simplexml_load_string($sheetXml);
        libxml_use_internal_errors($previous);

        if ($parsed === false || !isset($parsed->sheetData)) {
            return [];
        }

        $grid = [];
        $rowIndex = 0;

        foreach ($parsed->sheetData->row as $row) {
            $columns = [];

            foreach ($row->c as $cell) {
                $ref = (string) $cell['r'];
                $columnIndex = $this->columnIndexFromRef($ref);
                $type = (string) $cell['t'];

                $rawValue = isset($cell->v) ? (string) $cell->v : '';

                if ($type === 's' && $rawValue !== '') {
                    // Shared-string index.
                    $columns[$columnIndex] = $sharedStrings[(int) $rawValue] ?? '';
                } elseif ($type === 'inlineStr' && isset($cell->is->t)) {
                    $columns[$columnIndex] = (string) $cell->is->t;
                } else {
                    // Numeric, boolean, or cached-formula value — kept as-is.
                    $columns[$columnIndex] = $rawValue;
                }
            }

            if ($columns !== []) {
                ksort($columns);
                // Re-key from 0 so downstream indexing lines up regardless
                // of which spreadsheet columns (A, B, C...) were used.
                $grid[$rowIndex] = array_values($columns);
                $rowIndex++;
            }
        }

        return $grid;
    }

    /**
     * Converts a cell reference like "C7" into a zero-based column index (2).
     */
    private function columnIndexFromRef(string $ref): int
    {
        $letters = preg_replace('/[0-9]/', '', $ref) ?? '';
        $index = 0;

        foreach (str_split($letters) as $char) {
            $index = $index * 26 + (ord(strtoupper($char)) - ord('A') + 1);
        }

        return max(0, $index - 1);
    }
}
