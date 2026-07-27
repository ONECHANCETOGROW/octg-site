<?php

declare(strict_types=1);

namespace App\Modules\MarketingIntel\Validation\Rules;

use App\Modules\MarketingIntel\Parsing\ParsedPayload;
use App\Modules\MarketingIntel\Validation\ValidationContext;
use App\Modules\MarketingIntel\Validation\ValidationFinding;
use App\Modules\MarketingIntel\Validation\ValidationRuleInterface;

/**
 * Layer 6b (RNS spec §9 hallucination heuristics): the strategist confirms
 * real campaign/account names once, up front, when creating the audit
 * (Audit::known_entity_names — see AuditController). Every AI-relayed
 * response is checked for whether it actually references at least one of
 * those real names, rather than generic placeholders ("Campaign A", "Sample
 * Campaign 1") — a strong, practical tell that the AI lost real account
 * context or is inventing plausible-looking data.
 *
 * Deliberately skips silently (no false positives) when no known names have
 * been configured yet for the audit, or when the response method isn't
 * AI-relayed — this check only makes sense for that one method.
 */
final class NameGroundingRule implements ValidationRuleInterface
{
    /** @var array<int,string> */
    private const PLACEHOLDER_PATTERNS = [
        '/campaign\s*[a-e]\b/i',
        '/sample\s*campaign/i',
        '/example\s*campaign/i',
        '/\[campaign name\]/i',
        '/your campaign name/i',
    ];

    public function code(): string
    {
        return 'name_grounding';
    }

    public function evaluate(ParsedPayload $payload, ValidationContext $context): array
    {
        if ($context->knownEntityNames === []) {
            return [];
        }

        $findings = [];
        $lowerText = strtolower($context->rawText);

        $anyKnownNameFound = false;
        foreach ($context->knownEntityNames as $name) {
            if ($name !== '' && str_contains($lowerText, strtolower($name))) {
                $anyKnownNameFound = true;
                break;
            }
        }

        if (!$anyKnownNameFound) {
            $findings[] = new ValidationFinding(
                'warning',
                'hallucination_suspected',
                null,
                'None of this audit\'s confirmed real campaign/account names appear in the response — '
                    . 'double-check the AI assistant actually had access to the real account rather than '
                    . 'generating a plausible-looking example.'
            );
        }

        foreach (self::PLACEHOLDER_PATTERNS as $pattern) {
            if (preg_match($pattern, $context->rawText) === 1) {
                $findings[] = new ValidationFinding(
                    'critical',
                    'hallucination_suspected',
                    null,
                    'The response uses a generic placeholder-style name (e.g. "Campaign A", "Sample '
                        . 'Campaign") instead of a real campaign name — this strongly suggests the data is '
                        . 'illustrative, not real account data.'
                );
                break;
            }
        }

        return $findings;
    }
}
