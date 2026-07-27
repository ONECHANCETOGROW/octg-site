<?php

declare(strict_types=1);

namespace App\Modules\MarketingIntel\Validation;

/**
 * One problem a ValidationRule found — mirrors RuleResult on the SEO side of
 * this app (SeoEngine\RuleResult), same "one class of problem produces one
 * typed, explainable finding" philosophy.
 */
final class ValidationFinding
{
    public function __construct(
        public readonly string $severity,
        public readonly string $issueType,
        public readonly ?string $fieldName,
        public readonly string $message
    ) {
    }
}
