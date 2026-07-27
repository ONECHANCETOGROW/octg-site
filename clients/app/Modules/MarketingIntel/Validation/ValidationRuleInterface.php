<?php

declare(strict_types=1);

namespace App\Modules\MarketingIntel\Validation;

use App\Modules\MarketingIntel\Parsing\ParsedPayload;

/**
 * Every validation check implements this — mirrors SeoEngine\RuleInterface's
 * "one class per class-of-problem" design so adding a new validation check
 * is: create a class here, register it in ValidationEngine::rules(). Rules
 * never touch the database directly; they inspect a payload and return
 * findings, same separation the SEO rule engine already uses.
 */
interface ValidationRuleInterface
{
    public function code(): string;

    /**
     * @return array<int,ValidationFinding>
     */
    public function evaluate(ParsedPayload $payload, ValidationContext $context): array;
}
