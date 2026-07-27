<?php

declare(strict_types=1);

namespace App\Modules\MarketingIntel\Validation\Rules;

use App\Modules\MarketingIntel\Parsing\ParsedPayload;
use App\Modules\MarketingIntel\Validation\ValidationContext;
use App\Modules\MarketingIntel\Validation\ValidationFinding;
use App\Modules\MarketingIntel\Validation\ValidationRuleInterface;

/**
 * Layer 6a (RNS spec §9 hallucination heuristics): scans the *original*
 * pasted text (not the parsed payload — the hedge is usually prose around
 * the data, which parsing may have discarded) for phrases an AI uses when it
 * doesn't actually have real account data. This is a strong, practical tell:
 * the AI is telling the user, in plain language, that what follows may not
 * be grounded in the real account.
 */
final class HedgingLanguageRule implements ValidationRuleInterface
{
    /** @var array<int,string> */
    private const HEDGE_PHRASES = [
        "i don't have access",
        "i do not have access",
        "as an ai",
        "i cannot access",
        "i can't access",
        "i don't have real-time",
        "i don't have the ability to",
        "hypothetical",
        "for illustration",
        "example data",
        "i'm unable to retrieve",
        "i am unable to retrieve",
        "approximately, since exact",
        "since exact figures aren't available",
        "i don't actually have",
    ];

    public function code(): string
    {
        return 'hedging_language';
    }

    public function evaluate(ParsedPayload $payload, ValidationContext $context): array
    {
        $lowerText = strtolower($context->rawText);
        $findings = [];

        foreach (self::HEDGE_PHRASES as $phrase) {
            if (str_contains($lowerText, $phrase)) {
                $findings[] = new ValidationFinding(
                    'critical',
                    'hallucination_suspected',
                    null,
                    "The response contains the phrase \"{$phrase}\", which usually means the AI assistant "
                        . "didn't actually have real account data for this — re-check that it had genuine "
                        . "account access before trusting these figures."
                );

                // One flag is enough signal; avoid piling on multiple
                // near-identical findings for the same underlying response.
                break;
            }
        }

        return $findings;
    }
}
