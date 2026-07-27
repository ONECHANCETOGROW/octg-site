<?php

declare(strict_types=1);

namespace App\Services\AIParser;

/**
 * Repairs a markdown table that arrived as one squished line instead of
 * one row per line.
 *
 * Standard markdown tables put the header row, the alignment/separator
 * row, and every data row on their own line, separated by "\n" -- that's
 * what NLParser::extractTable() expects. In practice, copying a rendered
 * table out of a chat UI (Ask Advisor, ChatGPT, etc.) doesn't always
 * preserve those line breaks in the clipboard; the whole table can land
 * in the textarea as a single line like
 * "| a | b | | :--- | :--- | | 1 | 2 |". Fed straight to extractTable(),
 * that single line gets misread as one giant header row and nothing
 * else -- every campaign/keyword/search-term row silently vanishes.
 *
 * This class detects that shape (a run of "|" characters with too few
 * real newlines to be a normal table) and reconstructs proper
 * newline-separated rows by finding the header's column count from the
 * first "| |" row boundary, then chunking every remaining cell into rows
 * of that width. If the text already looks like a normal multi-line
 * table, or doesn't contain a table at all, it's returned unchanged.
 *
 * Originally written as a private method on
 * App\Modules\MarketingIntel\CollectionController (the older
 * per-Requirement collection flow); extracted here so the newer AI
 * Data Collection pipeline (NLParser, used by
 * MarketingWorkspaceController::ingestAI()) can share the exact same
 * fix instead of re-implementing it -- one sanitizer, reused by every
 * source, per the project's "never duplicate logic" rule.
 */
final class MarkdownTableSanitizer
{
    public static function sanitize(string $text): string
    {
        // 1. Auto-split combined Conversions metrics onto newlines if they
        // are on the same line -- an advisor sometimes runs several
        // labeled KPI-style fields together on one line separated only by
        // their own labels; give each its own line so downstream regex
        // extraction (which is line-based) can find them.
        $keys = [
            'Conversion Value Change vs Previous Period',
            'Conversion Change vs Previous Period',
            'Change vs Previous Period',
            'Total Conversion Value',
            'Conversion Value Change',
            'Conversion Change',
        ];

        usort($keys, fn ($a, $b) => strlen($b) <=> strlen($a));

        $pattern = '/(?<!\n)\b(' . implode('|', array_map(fn ($k) => preg_quote($k, '/'), $keys)) . '):\s*/i';

        $text = preg_replace_callback($pattern, function ($matches) {
            return "\n" . $matches[1] . ': ';
        }, $text);

        // 2. Standardize Conversion Change vs Previous Period key
        $text = preg_replace('/Conversion Change vs Previous Period:/i', 'Change vs Previous Period:', $text);

        if (!str_contains($text, '|')) {
            return $text;
        }

        $firstPipe = strpos($text, '|');
        if ($firstPipe === false) {
            return $text;
        }

        $lastPipe = strrpos($text, '|');
        $tableContent = substr($text, $firstPipe, $lastPipe - $firstPipe + 1);

        // If the table content already has more than 5 lines, it's not squished.
        if (substr_count($tableContent, "\n") > 5) {
            return $text;
        }

        // Find double-pipe boundaries (end of one row, start of the next).
        $firstDoublePipe = -1;
        if (preg_match('/\|\s*\|/', $tableContent, $matches, PREG_OFFSET_CAPTURE)) {
            $firstDoublePipe = $matches[0][1];
        }

        if ($firstDoublePipe === -1) {
            return preg_replace('/\|\s*\|/', "|\n|", $text);
        }

        $headerText = substr($tableContent, 0, $firstDoublePipe + 1);
        $headerCols = substr_count($headerText, '|') - 1;

        if ($headerCols <= 1) {
            return preg_replace('/\|\s*\|/', "|\n|", $text);
        }

        $parts = explode('|', $tableContent);
        if (trim($parts[0]) === '') {
            array_shift($parts);
        }
        if (trim(end($parts)) === '') {
            array_pop($parts);
        }

        $elements = array_map('trim', $parts);

        $cleanedElements = [];
        $colIndex = 0;
        foreach ($elements as $el) {
            if ($el === '' && $colIndex === 0 && count($cleanedElements) > 0) {
                continue;
            }
            $cleanedElements[] = $el;
            $colIndex = ($colIndex + 1) % $headerCols;
        }

        $chunks = array_chunk($cleanedElements, $headerCols);
        $rows = [];
        foreach ($chunks as $chunk) {
            if (count($chunk) < $headerCols) {
                continue;
            }
            $rows[] = '| ' . implode(' | ', $chunk) . ' |';
        }

        $formattedTable = implode("\n", $rows);
        $beforeTable = substr($text, 0, $firstPipe);
        $afterTable = substr($text, $lastPipe + 1);

        return $beforeTable . "\n" . $formattedTable . "\n" . $afterTable;
    }
}
