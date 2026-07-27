<?php
/* ==========================================================================
   SAFE-FIXES.PHP — the ONLY category of auto-fix this system actually
   executes without a draft/approval step: regenerating derived metadata
   files (sitemap.xml, sitemap-images.xml, llms.txt, robots.txt) from the
   real source-of-truth data that already exists. This is safe specifically
   because these files are pure, deterministic derivations of existing
   catalog data — regenerating them can't introduce new facts, wrong facts,
   or touch anything a visitor reads as page content, marketing copy, or a
   CTA. Everything else this system finds (missing alt text, meta
   descriptions, etc.) goes through the draft_improvements review queue
   instead — see admin/optimizer-drafts.php.
   ========================================================================== */

function octg_regenerate_sitemap(): string {
    $services = require __DIR__ . '/../../data/services-catalog.php';
    $articles = require __DIR__ . '/../../data/resources-catalog.php';
    $cases = require __DIR__ . '/../../data/case-studies-catalog.php';
    $base = 'https://onechancetogrow.com';

    $urls = [
        [$base . '/', '1.0'], [$base . '/services.php', '0.9'], [$base . '/products.php', '0.8'],
        [$base . '/industries.php', '0.7'], [$base . '/reviews.php', '0.7'], [$base . '/resources.php', '0.6'],
        [$base . '/projects.php', '0.7'], [$base . '/about.php', '0.7'], [$base . '/process.php', '0.7'],
        [$base . '/faq.php', '0.6'], [$base . '/glossary.php', '0.6'], [$base . '/contact.php', '0.8'],
        [$base . '/book-demo.php', '0.9'], [$base . '/audit.php', '0.8'], [$base . '/careers.php', '0.4'],
        [$base . '/privacy.php', '0.3'], [$base . '/terms.php', '0.3'], [$base . '/cookies.php', '0.3'],
        [$base . '/accessibility.php', '0.3'],
    ];
    foreach ($services as $s) $urls[] = [$base . '/services/' . $s['slug'] . '.php', '0.8'];
    foreach ($articles as $a) $urls[] = [$base . '/resources/' . $a['slug'] . '.php', '0.7'];
    foreach ($cases as $c) $urls[] = [$base . '/projects/' . $c['slug'] . '.php', '0.7'];

    $xml = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n<urlset xmlns=\"http://www.sitemaps.org/schemas/sitemap/0.9\">\n";
    foreach ($urls as [$loc, $priority]) {
        $xml .= "  <url>\n    <loc>{$loc}</loc>\n    <priority>{$priority}</priority>\n  </url>\n";
    }
    $xml .= "</urlset>\n";

    file_put_contents(__DIR__ . '/../../sitemap.xml', $xml);
    return count($urls) . ' URLs written to sitemap.xml';
}

function octg_regenerate_image_sitemap(): string {
    $cmsContent = file_get_contents(__DIR__ . '/../../data/cms-images.php');
    preg_match_all("/'([a-zA-Z0-9_\-]+)'\s*=>\s*(null|'[^']*')/", $cmsContent, $m, PREG_SET_ORDER);
    $realImages = [];
    foreach ($m as $pair) {
        if ($pair[2] !== 'null') $realImages[$pair[1]] = trim($pair[2], "'");
    }

    $xml = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n";
    $xml .= "<urlset xmlns=\"http://www.sitemaps.org/schemas/sitemap/0.9\"\n";
    $xml .= "        xmlns:image=\"http://www.google.com/schemas/sitemap-image/1.1\">\n";
    if ($realImages) {
        $xml .= "  <url>\n    <loc>https://onechancetogrow.com/</loc>\n";
        foreach ($realImages as $url) {
            $xml .= "    <image:image>\n      <image:loc>{$url}</image:loc>\n    </image:image>\n";
        }
        $xml .= "  </url>\n";
    }
    $xml .= "</urlset>\n";

    file_put_contents(__DIR__ . '/../../sitemap-images.xml', $xml);
    return count($realImages) . ' real image(s) written to sitemap-images.xml (' . (count($m) - count($realImages)) . ' slots still placeholder)';
}

function octg_regenerate_llms_txt(): string {
    $services = require __DIR__ . '/../../data/services-catalog.php';
    $articles = require __DIR__ . '/../../data/resources-catalog.php';
    $cases = require __DIR__ . '/../../data/case-studies-catalog.php';

    $lines = [];
    $lines[] = '# One Chance To Grow';
    $lines[] = '';
    $lines[] = '> Marketing, web, CRM, and automation systems built as one connected system for small and mid-size businesses across the United States and Canada. Registered in Wyoming, USA.';
    $lines[] = '';
    $lines[] = 'Contact: (802) 276-8331 · support@onechancetogrow.com';
    $lines[] = '';
    $lines[] = '## Services';
    $lines[] = '- [Full services overview](https://onechancetogrow.com/services.php)';
    foreach ($services as $s) $lines[] = "- [{$s['name']}](https://onechancetogrow.com/services/{$s['slug']}.php)";
    $lines[] = '';
    $lines[] = '## Case Studies';
    foreach ($cases as $c) $lines[] = "- [{$c['title']}](https://onechancetogrow.com/projects/{$c['slug']}.php)";
    $lines[] = '';
    $lines[] = '## Resources & Guides';
    foreach ($articles as $a) $lines[] = "- [{$a['title']}](https://onechancetogrow.com/resources/{$a['slug']}.php)";
    $lines[] = '';
    $lines[] = '## Company';
    $lines[] = '- [About & leadership team](https://onechancetogrow.com/about.php)';
    $lines[] = '- [Contact](https://onechancetogrow.com/contact.php)';
    $lines[] = '- [Book a growth call](https://onechancetogrow.com/book-demo.php)';

    file_put_contents(__DIR__ . '/../../llms.txt', implode("\n", $lines) . "\n");
    return 'llms.txt regenerated with ' . (count($services) + count($articles) + count($cases)) . ' content links';
}

/* robots.txt rarely needs regeneration, but included for completeness/
   consistency — this always writes the same known-good rules: public pages
   open to search + AI crawlers, admin panel blocked. Keep this in sync with
   the root robots.txt file if that file is ever edited by hand. */
function octg_regenerate_robots_txt(): string {
    $bots = ['*', 'Googlebot', 'Google-Extended', 'Bingbot', 'GPTBot', 'ClaudeBot',
             'anthropic-ai', 'PerplexityBot', 'Applebot', 'Applebot-Extended',
             'CCBot', 'Amazonbot', 'facebookexternalhit', 'Twitterbot', 'LinkedInBot'];
    $lines = ["# One Chance To Grow — robots.txt",
              "# Public pages are open to search and AI crawlers. The admin panel is blocked.", ""];
    foreach ($bots as $bot) {
        $lines[] = "User-agent: {$bot}";
        $lines[] = "Allow: /";
        $lines[] = "Disallow: /admin8828051506/";
        $lines[] = "";
    }
    $lines[] = "Sitemap: https://onechancetogrow.com/sitemap.xml";
    $lines[] = "Sitemap: https://onechancetogrow.com/sitemap-images.xml";
    $content = implode("\n", $lines) . "\n";
    file_put_contents(__DIR__ . '/../../robots.txt', $content);
    return 'robots.txt regenerated';
}
