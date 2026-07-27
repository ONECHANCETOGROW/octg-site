<?php

declare(strict_types=1);

namespace App\Modules\MarketingIntel\Merge;

/**
 * The source trust hierarchy from RNS spec §11: Direct API > Native export
 * upload > AI-Relay with the response-format contract satisfied > AI-Relay
 * freeform/degraded > Manual entry. Returns an integer tier (higher = more
 * trusted) rather than a fixed enum so future sources (a new AI provider, a
 * new upload format) can be slotted in without renumbering everything else.
 */
final class SourceTrustRanking
{
    private const TIERS = [
        'api' => 100,
        'upload_csv' => 85,
        'upload_excel' => 85,
        'upload_pdf' => 72,
        'ai_assistant_structured' => 70,
        'ai_assistant_degraded' => 50,
        'manual' => 40,
    ];

    /**
     * @param bool $usedFallbackParsing true if the parser had to fall back
     *   from a proper table to key:value scanning (see AiResponseParser) —
     *   only meaningful for the ai_assistant method.
     */
    public function tierFor(string $method, bool $usedFallbackParsing = false): int
    {
        if ($method === 'ai_assistant') {
            return $usedFallbackParsing ? self::TIERS['ai_assistant_degraded'] : self::TIERS['ai_assistant_structured'];
        }

        return self::TIERS[$method] ?? self::TIERS['manual'];
    }

    public function label(int $tier): string
    {
        return match (true) {
            $tier >= 100 => 'Direct API',
            $tier >= 85 => 'Native export upload',
            $tier >= 70 => 'AI Assistant (structured response)',
            $tier >= 50 => 'AI Assistant (freeform response)',
            default => 'Manual entry',
        };
    }
}
