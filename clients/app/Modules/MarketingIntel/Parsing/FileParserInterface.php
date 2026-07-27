<?php

declare(strict_types=1);

namespace App\Modules\MarketingIntel\Parsing;

/**
 * Implemented by parsers that need the actual uploaded file (Excel, PDF) —
 * see TextParserInterface for the pasted-text (AI Assistant, CSV) variant.
 */
interface FileParserInterface
{
    /**
     * @param array<int,string> $expectedColumns
     */
    public function parseFile(string $filePath, array $expectedColumns): ParsedPayload;

    public function code(): string;
}
