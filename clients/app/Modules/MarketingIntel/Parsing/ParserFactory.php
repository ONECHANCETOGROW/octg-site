<?php

declare(strict_types=1);

namespace App\Modules\MarketingIntel\Parsing;

/**
 * Selects the right parser for a CollectionAttempt's method — the one place
 * that knows "ai_assistant -> AiResponseParser", so controllers don't have
 * to. Adding a new method (e.g. a future API adapter) means adding one case
 * here and one new parser class, not touching any calling code.
 */
final class ParserFactory
{
    public function textParserFor(string $method): ?TextParserInterface
    {
        return match ($method) {
            'ai_assistant', 'manual' => new AiResponseParser(),
            'upload_csv' => new CsvParser(),
            default => null,
        };
    }

    public function fileParserFor(string $method): ?FileParserInterface
    {
        return match ($method) {
            'upload_excel' => new ExcelParser(),
            'upload_pdf' => new PdfParser(),
            default => null,
        };
    }
}
