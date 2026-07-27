<?php

declare(strict_types=1);

namespace App\Modules\MarketingIntel\Parsing;

/**
 * Implemented by parsers that operate on pasted text (AI Assistant
 * responses). File-based formats (Excel, PDF) implement FileParserInterface
 * instead, since they need real file bytes rather than a text string.
 */
interface TextParserInterface
{
    /**
     * @param array<int,string> $expectedColumns hint from the PromptTemplate/
     *   UploadTemplate this response is meant to satisfy — used to boost
     *   confidence when detected columns match, not required for parsing to
     *   succeed.
     */
    public function parseText(string $rawText, array $expectedColumns): ParsedPayload;

    public function code(): string;
}
