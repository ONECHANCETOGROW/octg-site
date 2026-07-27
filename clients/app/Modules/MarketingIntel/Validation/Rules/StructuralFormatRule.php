<?php

declare(strict_types=1);

namespace App\Modules\MarketingIntel\Validation\Rules;

use App\Modules\MarketingIntel\Parsing\ParsedPayload;
use App\Modules\MarketingIntel\Validation\ValidationContext;
use App\Modules\MarketingIntel\Validation\ValidationFinding;
use App\Modules\MarketingIntel\Validation\ValidationRuleInterface;

/**
 * Layer 1 of the validation pipeline (RNS spec §9): did the response contain
 * any parseable structure at all? This runs first because every other rule
 * assumes there's something to check — an empty payload short-circuits
 * straight to a single, clear, actionable critical finding instead of a
 * confusing cascade of "missing metric" findings for every expected column.
 */
final class StructuralFormatRule implements ValidationRuleInterface
{
    public function code(): string
    {
        return 'structural_format';
    }

    public function evaluate(ParsedPayload $payload, ValidationContext $context): array
    {
        if (!$payload->isEmpty()) {
            return [];
        }

        $warning = $payload->structuralWarnings[0] ?? 'no_structure_detected';

        $message = match ($warning) {
            'empty_response' => 'Nothing was pasted — copy the AI\'s reply and paste it in before submitting.',
            'no_structure_detected' => 'We couldn\'t find a table or clear key/value data in this response. '
                . 'Try re-pasting the prompt into the AI assistant, or ask it to reformat the answer as a table.',
            'csv_missing_header_or_rows' => 'The uploaded CSV needs at least a header row and one data row.',
            'csv_unreadable_header' => 'The first row of the CSV could not be read as column headers.',
            'excel_missing_sheet1' => 'The uploaded Excel file has no readable first worksheet.',
            'excel_sheet_empty' => 'The first worksheet in the uploaded Excel file appears to be empty.',
            'pdf_no_extractable_text' => 'No text could be extracted from this PDF — it may be a scanned image '
                . 'rather than a text-based PDF, which this parser cannot read.',
            'pdf_text_extracted_but_unstructured' => 'Text was extracted from the PDF, but it didn\'t match a '
                . 'recognizable table or key/value structure.',
            default => 'This response could not be parsed into structured data.',
        };

        return [new ValidationFinding('critical', 'structural_format_failure', null, $message)];
    }
}
