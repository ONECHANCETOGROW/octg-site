<?php

declare(strict_types=1);

namespace App\Modules\MarketingIntel\Parsing;

/**
 * The universal output shape every parser (AI text, CSV, Excel, PDF)
 * produces, regardless of input format — this is what makes the Validation
 * Engine and Merge Engine format-agnostic (RNS spec §9/§10/§11).
 *
 * - `rows`: tabular data, one associative array per row (e.g. one per
 *   campaign). Empty for a purely scalar response.
 * - `scalars`: key/value facts that aren't part of a table (e.g. "Date
 *   Range", "Account Name", "Total Spend" when reported as a single figure
 *   rather than a table column).
 * - `columnsDetected`: the raw column headers the parser actually found,
 *   used to cross-check against a PromptTemplate/UploadTemplate's
 *   `expected_columns` during validation.
 * - `fieldConfidence`: flat map, keys formatted as "row{n}.{column}" for
 *   tabular fields and "scalar.{key}" for scalar fields, values 0-100.
 * - `structuralWarnings`: problems the parser itself noticed while parsing
 *   (e.g. "no table found, fell back to key:value scan") — consumed by
 *   ValidationEngine's StructuralFormatRule rather than duplicating that
 *   logic in every parser.
 */
final class ParsedPayload
{
    /**
     * @param array<int,array<string,string>> $rows
     * @param array<string,string> $scalars
     * @param array<int,string> $columnsDetected
     * @param array<string,int> $fieldConfidence
     * @param array<int,string> $structuralWarnings
     */
    public function __construct(
        public readonly array $rows,
        public readonly array $scalars,
        public readonly array $columnsDetected,
        public readonly array $fieldConfidence,
        public readonly array $structuralWarnings
    ) {
    }

    public static function empty(string $warning): self
    {
        return new self([], [], [], [], [$warning]);
    }

    /**
     * @return array<string,mixed>
     */
    public function toStructuredArray(): array
    {
        return [
            'rows' => $this->rows,
            'scalars' => $this->scalars,
            'columns_detected' => $this->columnsDetected,
        ];
    }

    public function isEmpty(): bool
    {
        return $this->rows === [] && $this->scalars === [];
    }
}
