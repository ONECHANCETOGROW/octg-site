<?php

declare(strict_types=1);

namespace App\Modules\MarketingIntel\Parsing;

/**
 * Parses text pasted back from an AI Assistant (e.g. Google Ads Advisor).
 *
 * This parser is only as reliable as the prompt that produced the response —
 * per RNS spec §2's core recommendation, every PromptTemplate embeds an
 * explicit response-format contract ("reply as a markdown table with these
 * exact columns"). This class's job is to actually honor that contract when
 * present, and degrade gracefully through two fallback tiers when it isn't:
 *
 *   Tier 1 — markdown table (highest confidence: the AI followed the
 *            format contract).
 *   Tier 2 — key: value lines (medium confidence: freeform prose that
 *            happens to be structured enough to salvage).
 *   Tier 3 — nothing structured detected at all (empty payload + a
 *            structural warning; ValidationEngine's StructuralFormatRule
 *            turns this into a critical issue rather than silently
 *            accepting empty data).
 */
final class AiResponseParser implements TextParserInterface
{
    public function code(): string
    {
        return 'ai_table_parser';
    }

    public function parseText(string $rawText, array $expectedColumns): ParsedPayload
    {
        $text = str_replace(["\r\n", "\r"], "\n", trim($rawText));

        if ($text === '') {
            return ParsedPayload::empty('empty_response');
        }

        $tableResult = $this->tryParseMarkdownTable($text, $expectedColumns);
        if ($tableResult !== null) {
            return $tableResult;
        }

        $scalarResult = $this->tryParseKeyValueLines($text, $expectedColumns);
        if ($scalarResult !== null) {
            return $scalarResult;
        }

        return ParsedPayload::empty('no_structure_detected');
    }

    /**
     * @param array<int,string> $expectedColumns
     */
    private function tryParseMarkdownTable(string $text, array $expectedColumns): ?ParsedPayload
    {
        $lines = explode("\n", $text);
        $tableStart = null;

        for ($i = 0; $i < count($lines) - 1; $i++) {
            $line = trim($lines[$i]);
            $nextLine = trim($lines[$i + 1]);

            if (!str_contains($line, '|')) {
                continue;
            }

            // A markdown table header is followed by a separator line made
            // only of |, -, : and whitespace (e.g. "|---|:---:|---|").
            if (preg_match('/^\|?[\s:|-]+\|?$/', $nextLine) === 1 && str_contains($nextLine, '-')) {
                $tableStart = $i;
                break;
            }
        }

        if ($tableStart === null) {
            return null;
        }

        $headerCells = $this->splitTableRow($lines[$tableStart]);
        if ($headerCells === []) {
            return null;
        }

        $rows = [];
        for ($i = $tableStart + 2; $i < count($lines); $i++) {
            $line = trim($lines[$i]);
            if ($line === '' || !str_contains($line, '|')) {
                break;
            }

            $cells = $this->splitTableRow($line);
            if ($cells === []) {
                continue;
            }

            $row = [];
            foreach ($headerCells as $index => $header) {
                $row[$header] = $cells[$index] ?? '';
            }

            $rows[] = $row;
        }

        if ($rows === []) {
            return null;
        }

        $fieldConfidence = [];
        $normalizedExpected = array_map(
            static fn (string $c): string => strtolower(trim($c)),
            $expectedColumns
        );

        foreach ($rows as $rowIndex => $row) {
            foreach ($row as $column => $value) {
                $key = "row{$rowIndex}.{$column}";
                $matchesExpected = in_array(strtolower(trim($column)), $normalizedExpected, true);
                $hasValue = trim($value) !== '';

                $fieldConfidence[$key] = match (true) {
                    !$hasValue => 20,
                    $matchesExpected => 92,
                    default => 68,
                };
            }
        }

        return new ParsedPayload($rows, [], $headerCells, $fieldConfidence, []);
    }

    /**
     * @return array<int,string>
     */
    private function splitTableRow(string $line): array
    {
        $line = trim($line);
        $line = trim($line, '|');
        $cells = array_map(
            static fn (string $c): string => trim($c),
            explode('|', $line)
        );

        return array_values(array_filter($cells, static fn (string $c): bool => $c !== ''));
    }

    /**
     * @param array<int,string> $expectedColumns
     */
    private function tryParseKeyValueLines(string $text, array $expectedColumns): ?ParsedPayload
    {
        $lines = explode("\n", $text);
        $scalars = [];

        foreach ($lines as $line) {
            $line = trim($line);
            // Strip common markdown bullet/bold decoration before matching.
            $line = preg_replace('/^[-*]\s*/', '', $line) ?? $line;
            $line = preg_replace('/\*\*/', '', $line) ?? $line;

            if (preg_match('/^([A-Za-z0-9 %\/\-_]{2,60}):\s*(.+)$/', $line, $matches) === 1) {
                $key = trim($matches[1]);
                $value = trim($matches[2]);
                if ($key !== '' && $value !== '') {
                    $scalars[$key] = $value;
                }
            }
        }

        if ($scalars === []) {
            return null;
        }

        $normalizedExpected = array_map(
            static fn (string $c): string => strtolower(trim($c)),
            $expectedColumns
        );

        $fieldConfidence = [];
        foreach ($scalars as $key => $value) {
            $matchesExpected = in_array(strtolower(trim($key)), $normalizedExpected, true);
            // Key:value fallback is inherently less reliable than a proper
            // table — the AI didn't follow the response-format contract, so
            // even a "matching" key is capped lower than table-tier confidence.
            $fieldConfidence["scalar.{$key}"] = $matchesExpected ? 70 : 55;
        }

        return new ParsedPayload([], $scalars, array_keys($scalars), $fieldConfidence, ['fallback_key_value_used']);
    }
}
