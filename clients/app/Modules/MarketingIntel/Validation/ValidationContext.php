<?php

declare(strict_types=1);

namespace App\Modules\MarketingIntel\Validation;

/**
 * Everything a ValidationRule might need beyond the parsed payload itself:
 * the original raw text (for hedging-language / name-grounding checks that
 * need to see prose the parser may have discarded), the expected columns
 * from the fulfilling PromptTemplate/UploadTemplate, real entity names the
 * strategist confirmed for this audit (for the name-grounding
 * hallucination check, RNS spec §9), and any already-collected
 * KnowledgeFacts for this audit (for cross-prompt consistency checks).
 */
final class ValidationContext
{
    /**
     * @param array<int,string> $expectedColumns
     * @param array<int,string> $knownEntityNames
     * @param array<int,array<string,mixed>> $priorKnowledgeFacts
     */
    public function __construct(
        public readonly string $rawText,
        public readonly array $expectedColumns,
        public readonly array $knownEntityNames = [],
        public readonly array $priorKnowledgeFacts = []
    ) {
    }
}
