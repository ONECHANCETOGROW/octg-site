<?php
/* ==========================================================================
   ANALYZER.PHP — extracts measurable facts from a page's actual HTML.
   Every fact here is a direct regex/DOM extraction from real content —
   nothing here is estimated or invented.
   ========================================================================== */

function octg_analyze_html(string $html): array {
    $facts = [];

    preg_match('/<title>(.*?)<\/title>/is', $html, $m);
    $facts['title'] = $m[1] ?? null;

    preg_match('/<meta\s+name="description"\s+content="([^"]*)"/i', $html, $m);
    $facts['description'] = $m[1] ?? null;

    $facts['canonical'] = (bool) preg_match('/<link\s+rel="canonical"/i', $html);
    $facts['has_og'] = (bool) preg_match('/property="og:title"/i', $html);
    $facts['has_twitter'] = (bool) preg_match('/name="twitter:card"/i', $html);

    $facts['h1_count'] = preg_match_all('/<h1[\s>]/i', $html);
    preg_match_all('/<h([1-4])[\s>]/i', $html, $m);
    $facts['heading_sequence'] = array_map('intval', $m[1] ?? []);

    $facts['internal_link_count'] = preg_match_all('/href="(\/[a-zA-Z0-9_\-\/]*\.php)/i', $html, $linkMatches);
    $facts['internal_link_targets'] = array_values(array_unique($linkMatches[1] ?? []));
    $facts['external_link_count'] = preg_match_all('/href="https?:\/\/(?!onechancetogrow\.com)/i', $html);

    preg_match_all('/<img\b[^>]*>/i', $html, $m);
    $imgs = $m[0] ?? [];
    $facts['image_count'] = count($imgs);
    $facts['images_missing_alt'] = 0;
    foreach ($imgs as $img) {
        if (!preg_match('/alt="[^"]+"/i', $img)) $facts['images_missing_alt']++;
    }

    preg_match_all('/"@type":\s*"([A-Za-z]+)"/i', $html, $m);
    $facts['schema_types'] = $m[1] ?? [];

    $facts['has_breadcrumb_visual'] = (bool) preg_match('/class="breadcrumb/i', $html);
    $facts['has_faq'] = (bool) preg_match('/class="faq[\s"]/i', $html) || (bool) preg_match('/class="faq-item/i', $html);
    $facts['has_quick_answer'] = (bool) preg_match('/class="quick-answer/i', $html);

    $text = preg_replace('/<script.*?<\/script>/is', ' ', $html);
    $text = preg_replace('/<style.*?<\/style>/is', ' ', $text);
    $text = strip_tags($text);
    $text = preg_replace('/\s+/', ' ', $text);
    $facts['word_count'] = str_word_count(trim($text));

    $facts['heading_hierarchy_ok'] = true;
    $prev = 0;
    foreach ($facts['heading_sequence'] as $level) {
        if ($prev !== 0 && $level > $prev + 1) { $facts['heading_hierarchy_ok'] = false; break; }
        $prev = $level;
    }

    return $facts;
}
