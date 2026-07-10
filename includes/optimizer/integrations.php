<?php
/* ==========================================================================
   INTEGRATIONS.PHP — architected hooks for external services listed under
   "Future Ready": PageSpeed Insights, Google Search Console, Google
   Analytics, Bing Webmaster Tools, Microsoft Clarity, and AI provider APIs
   (OpenAI/Claude/Gemini/Perplexity) for deeper analysis.

   NONE of these are connected yet — each requires an API key/account that
   only you can create and authorize. Every function below is a real,
   callable stub that returns a clear "not connected" result rather than
   fabricated data, and documents exactly what it will do once wired up.
   This file is the single place those integrations get added later —
   nothing elsewhere in the optimizer needs to change when that happens.
   ========================================================================== */

/* Real Core Web Vitals (LCP, CLS, INP) — requires a PageSpeed Insights API
   key and a live, publicly reachable URL. https://developers.google.com/speed/docs/insights/v5/get-started */
function octg_pagespeed_check(string $url): array {
    $apiKey = getenv('PAGESPEED_API_KEY') ?: null;
    if (!$apiKey) {
        return ['connected' => false, 'reason' => 'No PageSpeed Insights API key configured. Real LCP/CLS/INP numbers will appear here once one is added — this system will never invent them in the meantime.'];
    }
    // Implementation intentionally not written until a key exists to test against —
    // writing untestable API-integration code would risk shipping a broken
    // integration with no way to verify it actually works.
    return ['connected' => false, 'reason' => 'API key present but integration not yet implemented.'];
}

function octg_search_console_check(): array {
    return ['connected' => false, 'reason' => 'Google Search Console is not connected. Once authorized, this will pull real indexing status, search queries, and click-through data.'];
}

function octg_analytics_check(): array {
    return ['connected' => false, 'reason' => 'Google Analytics is not connected. This is separate from the internal lead-notification system already built.'];
}

function octg_ai_provider_check(string $provider): array {
    $known = ['openai', 'claude', 'gemini', 'perplexity'];
    if (!in_array($provider, $known, true)) {
        return ['connected' => false, 'reason' => "Unknown provider '{$provider}'."];
    }
    return ['connected' => false, 'reason' => ucfirst($provider) . " is not connected. Once an API key is added, this hook is where the AI Analysis Engine would call out for deeper, natural-language recommendations beyond the rule-based issue detection already running in scorer.php."];
}
