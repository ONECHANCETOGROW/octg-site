<?php
/* ==========================================================================
   PAGE-INVENTORY.PHP — the authoritative list of every real, live page on
   the site, built from the actual catalogs rather than a hand-maintained
   list — so it can never drift out of sync the way a static list would.
   ========================================================================== */

function octg_optimizer_page_inventory(): array {
    $pages = [
        ['url' => '/', 'label' => 'Homepage'],
        ['url' => '/services.php', 'label' => 'Services Hub'],
        ['url' => '/products.php', 'label' => 'Products'],
        ['url' => '/industries.php', 'label' => 'Industries'],
        ['url' => '/reviews.php', 'label' => 'Reviews'],
        ['url' => '/resources.php', 'label' => 'Resources Hub'],
        ['url' => '/projects.php', 'label' => 'Projects Hub'],
        ['url' => '/about.php', 'label' => 'About'],
        ['url' => '/process.php', 'label' => 'Our Process'],
        ['url' => '/faq.php', 'label' => 'FAQ Hub'],
        ['url' => '/glossary.php', 'label' => 'Glossary'],
        ['url' => '/contact.php', 'label' => 'Contact'],
        ['url' => '/book-demo.php', 'label' => 'Book a Demo'],
        ['url' => '/audit.php', 'label' => 'Free Audit'],
        ['url' => '/careers.php', 'label' => 'Careers'],
        ['url' => '/privacy.php', 'label' => 'Privacy Policy'],
        ['url' => '/terms.php', 'label' => 'Terms of Service'],
        ['url' => '/cookies.php', 'label' => 'Cookie Policy'],
        ['url' => '/accessibility.php', 'label' => 'Accessibility Statement'],
    ];

    $services = require __DIR__ . '/../../data/services-catalog.php';
    foreach ($services as $s) {
        $pages[] = ['url' => '/services/' . $s['slug'] . '.php', 'label' => $s['name']];
    }

    $articles = require __DIR__ . '/../../data/resources-catalog.php';
    foreach ($articles as $a) {
        $pages[] = ['url' => '/resources/' . $a['slug'] . '.php', 'label' => $a['title']];
    }

    $cases = require __DIR__ . '/../../data/case-studies-catalog.php';
    foreach ($cases as $c) {
        $pages[] = ['url' => '/projects/' . $c['slug'] . '.php', 'label' => $c['title']];
    }

    return $pages;
}
