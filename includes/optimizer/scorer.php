<?php
/* ==========================================================================
   SCORER.PHP — turns extracted facts into scores + issues, using a fixed,
   documented rubric. Every point awarded or withheld ties to a specific,
   checkable fact — nothing here is estimated.

   IMPORTANT HONESTY BOUNDARY: "Performance Score" below measures STATIC
   proxies actually present in the fetched HTML (lazy-loading, script defer,
   font-display swap) — NOT real Core Web Vitals (LCP/CLS/INP), which can
   only be measured by an actual browser loading the page. Real CWV numbers
   require Google's PageSpeed Insights API (a live URL + an API key) — see
   includes/optimizer/pagespeed-integration.php for the architected-but-not-
   yet-connected hook. This system will never display an invented LCP/CLS/
   INP number.
   ========================================================================== */

function octg_score_page(array $facts, int $inboundLinks = 0): array {
    $scores = [];
    $issues = [];

    /* ---- SEO (100) ---- */
    $seo = 0;
    if ($facts['title']) {
        $seo += 10;
        $len = strlen($facts['title']);
        if ($len >= 30 && $len <= 65) $seo += 10;
        else $issues[] = ['category'=>'seo','severity'=>'warning','issue'=>'Title length not ideal','reason'=>"Title is {$len} characters; 30–65 is the range that typically displays fully in search results.",'fix'=>'Shorten or lengthen the title tag to fall within 30–65 characters.'];
    } else {
        $issues[] = ['category'=>'seo','severity'=>'critical','issue'=>'Missing title tag','reason'=>'No <title> tag was found in the page.','fix'=>'Set $pageTitle for this page.'];
    }
    if ($facts['description']) {
        $seo += 10;
        $len = strlen($facts['description']);
        if ($len >= 120 && $len <= 165) $seo += 10;
        else $issues[] = ['category'=>'seo','severity'=>'warning','issue'=>'Meta description length not ideal','reason'=>"Description is {$len} characters; 120–165 is the range that typically displays fully.",'fix'=>'Adjust the meta description length.'];
    } else {
        $issues[] = ['category'=>'seo','severity'=>'critical','issue'=>'Missing meta description','reason'=>'No meta description tag was found.','fix'=>'Set $pageDescription for this page.','safe'=>true];
    }
    if ($facts['h1_count'] === 1) $seo += 15;
    else $issues[] = ['category'=>'seo','severity'=>'critical','issue'=>"{$facts['h1_count']} H1 tags found",'reason'=>'A page should have exactly one H1.','fix'=>'Ensure exactly one <h1> exists on this page.'];
    if ($facts['heading_hierarchy_ok']) $seo += 15;
    else $issues[] = ['category'=>'seo','severity'=>'warning','issue'=>'Heading hierarchy skips a level','reason'=>'Heading levels jump (e.g. H2 to H4) without the level in between.','fix'=>'Insert the missing intermediate heading level or adjust heading levels used.'];
    if ($facts['canonical']) $seo += 10;
    else $issues[] = ['category'=>'seo','severity'=>'critical','issue'=>'Missing canonical tag','reason'=>'No rel="canonical" link tag found.','fix'=>'Add a canonical URL.','safe'=>true];
    if ($facts['internal_link_count'] >= 3) $seo += 10;
    else $issues[] = ['category'=>'seo','severity'=>'warning','issue'=>'Low internal link count','reason'=>"Only {$facts['internal_link_count']} internal links found; 3+ is a reasonable minimum for topical connectivity.",'fix'=>'Add links to related services, articles, or pages.'];
    if ($facts['image_count'] === 0 || $facts['images_missing_alt'] === 0) $seo += 10;
    else $issues[] = ['category'=>'seo','severity'=>'critical','issue'=>"{$facts['images_missing_alt']} image(s) missing alt text",'reason'=>'Every content image should have descriptive alt text.','fix'=>'Add alt text to the affected image(s).','safe'=>true];
    $scores['seo'] = $seo;

    /* ---- AEO (100) ---- */
    $aeo = 0;
    if ($facts['has_quick_answer']) $aeo += 35;
    else $issues[] = ['category'=>'aeo','severity'=>'warning','issue'=>'No Quick Answer section','reason'=>'A concise, self-contained answer block helps AI answer engines extract a direct response.','fix'=>'Add a Quick Answer callout summarizing the page in one sentence.'];
    if ($facts['has_faq']) $aeo += 35;
    else $issues[] = ['category'=>'aeo','severity'=>'warning','issue'=>'No FAQ section','reason'=>'FAQ-formatted content directly matches how answer engines look for Q&A pairs.','fix'=>'Add an FAQ section relevant to this page.'];
    if (in_array('FAQPage', $facts['schema_types'], true)) $aeo += 30;
    elseif ($facts['has_faq']) $issues[] = ['category'=>'aeo','severity'=>'warning','issue'=>'FAQ present but no FAQPage schema','reason'=>'Visible FAQ content exists but isn\'t marked up as FAQPage structured data.','fix'=>'Add FAQPage schema matching the visible FAQ.'];
    $scores['aeo'] = $aeo;

    /* ---- GEO / LLMO (100): machine-readability ---- */
    $geo = 0;
    if ($facts['schema_types']) $geo += 30;
    else $issues[] = ['category'=>'geo','severity'=>'warning','issue'=>'No structured data found','reason'=>'Schema.org markup helps AI systems and search engines understand page entities.','fix'=>'Add relevant schema (Service, Article, BreadcrumbList, etc.).'];
    if ($facts['word_count'] >= 150) $geo += 25;
    else $issues[] = ['category'=>'geo','severity'=>'warning','issue'=>'Thin content','reason'=>"Only {$facts['word_count']} words of visible text; more context helps AI systems understand the page.",'fix'=>'Expand the page content.'];
    if ($facts['internal_link_count'] >= 3) $geo += 25;
    if ($facts['heading_hierarchy_ok']) $geo += 20;
    $scores['geo'] = $geo;

    /* ---- E-E-A-T (100): trust signals ---- */
    $eeat = 0;
    if ($facts['has_breadcrumb_visual']) $eeat += 30;
    else $issues[] = ['category'=>'eeat','severity'=>'info','issue'=>'No visible breadcrumb','reason'=>'Breadcrumbs help establish site structure and transparency for visitors.','fix'=>'Add a breadcrumb to this page.'];
    $eeat += 30; // contact reachability is sitewide (header/footer nav) — verified once at the report level, not per page
    if ($facts['schema_types']) $eeat += 20;
    if ($facts['heading_hierarchy_ok']) $eeat += 20;
    $scores['eeat'] = $eeat;

    /* ---- Accessibility (100): static proxies + sitewide checks blended at report level ---- */
    $a11y = 0;
    if ($facts['image_count'] === 0 || $facts['images_missing_alt'] === 0) $a11y += 60;
    if ($facts['heading_hierarchy_ok']) $a11y += 40;
    $scores['accessibility'] = $a11y;

    /* ---- Performance (100): STATIC PROXIES ONLY — see file header note.
       Not computed here: lazy-loading/defer/font-display are consistent
       site-wide architecture rather than per-page variables, so
       run-audit.php checks them once and merges the result into every
       page's score after this function returns. ---- */

    /* ---- Technical SEO (100) ---- */
    $tseo = 0;
    if ($facts['canonical']) $tseo += 30;
    if ($facts['has_og']) $tseo += 25; else $issues[] = ['category'=>'technical_seo','severity'=>'warning','issue'=>'Missing Open Graph tags','reason'=>'OG tags control how the page appears when shared on social platforms.','fix'=>'Add og:title, og:description, og:image.','safe'=>true];
    if ($facts['has_twitter']) $tseo += 25; else $issues[] = ['category'=>'technical_seo','severity'=>'warning','issue'=>'Missing Twitter Card tags','reason'=>'Twitter Card tags control how the page appears when shared on X/Twitter.','fix'=>'Add twitter:card, twitter:title, twitter:description.','safe'=>true];
    if ($facts['schema_types']) $tseo += 20;
    $scores['technical_seo'] = $tseo;

    /* ---- Internal Linking (100) ---- */
    $il = min(50, $facts['internal_link_count'] * 10);
    if ($facts['has_breadcrumb_visual']) $il += 25;
    $il += min(25, $inboundLinks * 5); // 0 inbound links found yet if this is the first crawl pass
    if ($inboundLinks === 0) $issues[] = ['category'=>'internal_linking','severity'=>'info','issue'=>'No inbound internal links detected','reason'=>'No other crawled page links to this URL yet.','fix'=>'Add links to this page from related content.'];
    $scores['internal_linking'] = min(100, $il);

    // 'overall' is computed by the orchestrator (admin/cron/run-audit.php)
    // after it merges in the performance score, so every category is included.
    return ['scores' => $scores, 'issues' => $issues];
}
