<?php

declare(strict_types=1);

namespace App\Modules\MarketingIntel\Parsing;

/**
 * Parses CSV text (either pasted directly, or the raw contents of an
 * uploaded .csv file, read by the caller and passed in as text) using PHP's
 * built-in str_getcsv — no external dependency, matches this app's
 * dependency-free architecture.
 */
final class CsvParser implements TextParserInterface
{
    public function code(): string
    {
        return 'csv_parser';
    }

    public function parseText(string $rawText, array $expectedColumns): ParsedPayload
    {
        $text = str_replace(["\r\n", "\r"], "\n", trim($rawText));
        if ($text === '') {
            return ParsedPayload::empty('empty_response');
        }

        $lines = array_values(array_filter(
            explode("\n", $text),
            static fn (string $line): bool => trim($line) !== ''
        ));

        if (count($lines) < 2) {
            return ParsedPayload::empty('csv_missing_header_or_rows');
        }

        $delimiter = $this->detectDelimiter($lines[0]);
        $header = str_getcsv($lines[0], $delimiter);
        $header = array_map(static fn ($h): string => trim((string) $h), $header);

        if ($header === [] || $header === ['']) {
            return ParsedPayload::empty('csv_unreadable_header');
        }

        $normalizedExpected = array_map(
            static fn (string $c): string => strtolower(trim($c)),
            $expectedColumns
        );

        $rows = [];
        $fieldConfidence = [];

        for ($i = 1; $i < count($lines); $i++) {
            $cells = str_getcsv($lines[$i], $delimiter);
            $row = [];

            foreach ($header as $columnIndex => $columnName) {
                $value = trim((string) ($cells[$columnIndex] ?? ''));
                $row[$columnName] = $value;

                $rowNumber = $i - 1;
                $matchesExpected = in_array(strtolower($columnName), $normalizedExpected, true);
                $fieldConfidence["row{$rowNumber}.{$columnName}"] = match (true) {
                    $value === '' => 25,
                    $matchesExpected || $normalizedExpected === [] => 95,
                    default => 80,
                };
            }

            $rows[] = $row;
        }

        return new ParsedPayload($rows, [], $header, $fieldConfidence, []);
    }

    private function detectDelimiter(string $headerLine): string
    {
        $commaCount = substr_count($headerLine, ',');
        $tabCount = substr_count($headerLine, "\t");
        $semicolonCount = substr_count($headerLine, ';');

        if ($tabCount > $commaCount && $tabCount > $semicolonCount) {
            return "\t";
        }

        if ($semicolonCount > $commaCount) {
            return ';';
        }

        return ',';
    }
}
