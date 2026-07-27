<?php

declare(strict_types=1);

/**
 * View-layer formatting helpers shared across module templates. Plain
 * functions (not a class) since they're pure presentation formatting with
 * no state and no dependencies — included via require_once from any view
 * that needs them.
 */

if (!function_exists('octg_score_class')) {
    function octg_score_class(int $score): string
    {
        if ($score >= 80) {
            return 'good';
        }

        if ($score >= 50) {
            return 'ok';
        }

        return 'bad';
    }
}

if (!function_exists('octg_severity_label')) {
    function octg_severity_label(string $severity): string
    {
        return ucfirst($severity);
    }
}

if (!function_exists('octg_decode_breakdown')) {
    /**
     * Decodes a website_scores.technical_breakdown / sitewide_breakdown JSON
     * column back into TechnicalSeoAnalyzer::breakdown() / SitewideFactorsAnalyzer
     * ::breakdown() shape. Returns [] for null/empty/malformed input — covers
     * historical rows computed before the 022 migration added these columns,
     * so older crawls degrade to "no breakdown available" instead of erroring.
     *
     * @return array<int,array{label:string,deduction:int,detail:string,verified:bool}>
     */
    function octg_decode_breakdown(?string $json): array
    {
        if ($json === null || $json === '') {
            return [];
        }

        $decoded = json_decode($json, true);

        return is_array($decoded) ? $decoded : [];
    }
}
