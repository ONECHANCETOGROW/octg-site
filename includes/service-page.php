<?php
/* ==========================================================================
   SERVICE-PAGE.PHP — shared renderer for every /services/[slug].php page.
   Each individual service page is a 3-line file: it sets $slug, then
   includes this template. Everything else — layout, schema, meta tags,
   related content — is generated from data/services-catalog.php.

   To add a new service later: add one entry to data/services-catalog.php,
   add its slug to includes/navigation.php's mega-menu array, then drop in
   a 3-line page file. No other file needs to change.
   ========================================================================== */

if (!isset($slug)) {
    http_response_code(500);
    die('service-page.php requires $slug to be set before it is included.');
}

$CATALOG   = require __DIR__ . '/../data/services-catalog.php';
$CATEGORIES = require __DIR__ . '/../data/categories.php';
$PRODUCTS  = require __DIR__ . '/../data/products-catalog.php';

$svc = null;
foreach ($CATALOG as $row) {
    if ($row['slug'] === $slug) { $svc = $row; break; }
}

if (!$svc) {
    http_response_code(404);
    $pageTitle = 'Service Not Found | One Chance To Grow';
    $pageDescription = 'This service page could not be found.';
    $pageSlug = $slug;
    $activeNav = 'services';
    include __DIR__ . '/header.php';
    echo '<section class="section wrap"><h1>Service not found</h1><p class="lead">That service page doesn\'t exist yet. <a href="/services.php">See all services</a>.</p></section>';
    include __DIR__ . '/footer.php';
    exit;
}

$productsBySlug = [];
foreach ($PRODUCTS as $p) { $productsBySlug[$p['slug']] = $p; }
$svcBySlug = [];
foreach ($CATALOG as $row) { $svcBySlug[$row['slug']] = $row; }

$categoryLabel = $CATEGORIES[$svc['category']] ?? 'Services';
$product = $productsBySlug[$svc['related_product']] ?? null;

/* ---- Header variables ---- */
$pageTitle       = $svc['h1'] . ' | One Chance To Grow';
$pageDescription = $svc['hero_lead'];
$pageSlug        = $svc['slug'];
$canonicalPath   = 'services/' . $svc['slug'] . '.php';
$activeNav       = 'services';
$bodyClass       = 'page-service';
$pageStyle       = null;
$extraStyles     = ['service-page'];
$pageScript      = null;

include __DIR__ . '/header.php';
?>

<!-- BREADCRUMB -->
<nav class="breadcrumb" aria-label="Breadcrumb">
  <a href="/index.php">Home</a>
  <span class="sep">/</span>
  <a href="/services.php">Services</a>
  <span class="sep">/</span>
  <span aria-current="page"><?php echo htmlspecialchars($svc['name']); ?></span>
</nav>

<!-- SERVICE HERO -->
<section class="service-hero wrap">
  <a href="/services.php#<?php echo htmlspecialchars($svc['category']); ?>" class="eyebrow"><?php echo htmlspecialchars($categoryLabel); ?></a>
  <h1 class="reveal-text"><?php echo htmlspecialchars($svc['h1']); ?></h1>
  <p class="lead"><?php echo htmlspecialchars($svc['hero_lead']); ?></p>
  <div class="service-hero__ctas">
    <a href="/book-demo.php" class="btn btn-primary">Book a Growth Call <svg class="btn-arrow" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M5 12h14M13 6l6 6-6 6"/></svg></a>
    <a href="tel:+18022768331" class="btn btn-ghost">Call (802) 276-8331</a>
  </div>
</section>

<!-- QUICK ANSWER (AEO: a concise, self-contained definition for answer engines and skimming readers) -->
<section class="section" style="padding-top:0;padding-bottom:0;">
  <div class="wrap">
    <div class="quick-answer reveal">
      <span class="quick-answer__label">Quick Answer</span>
      <p><?php echo htmlspecialchars($svc['quick_answer']); ?></p>
    </div>
  </div>
</section>

<!-- PROBLEM -->
<section class="section" style="padding-top:40px;padding-bottom:70px;">
  <div class="wrap">
    <div class="problem-block reveal">
      <h2><?php echo htmlspecialchars($svc['problem_heading']); ?></h2>
      <p class="lead"><?php echo htmlspecialchars($svc['problem_body']); ?></p>
    </div>
  </div>
</section>

<!-- DELIVERABLES -->
<section class="section" style="background:var(--paper-deep);">
  <div class="wrap">
    <div class="section-head reveal">
      <span class="eyebrow">What's Included</span>
      <h2>Exactly what this covers.</h2>
    </div>
    <ul class="check-list reveal">
      <?php foreach ($svc['deliverables'] as $item): ?>
      <li>
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12l5 5L20 6"/></svg>
        <span><?php echo htmlspecialchars($item); ?></span>
      </li>
      <?php endforeach; ?>
    </ul>
  </div>
</section>

<!-- PROCESS -->
<section class="section section--deep">
  <div class="wrap">
    <div class="section-head reveal">
      <span class="eyebrow">How It Works</span>
      <h2>Three steps, start to finish.</h2>
    </div>
    <div class="process process--three reveal">
      <?php foreach ($svc['process'] as $i => $step): ?>
      <div class="step">
        <div class="step__num"><?php echo str_pad((string)($i + 1), 2, '0', STR_PAD_LEFT); ?></div>
        <h3><?php echo htmlspecialchars($step[0]); ?></h3>
        <p><?php echo htmlspecialchars($step[1]); ?></p>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<!-- PROOF -->
<section class="section" style="padding-top:70px;padding-bottom:70px;">
  <div class="wrap">
    <?php if ($svc['proof']['type'] === 'stat'): ?>
      <div class="stat-card is-solo reveal">
        <span class="stat-card__num"><?php echo htmlspecialchars($svc['proof']['num']); ?></span>
        <span class="stat-card__text"><?php echo htmlspecialchars($svc['proof']['text']); ?></span>
        <?php if (!empty($svc['proof']['source'])): ?><span class="stat-card__source"><?php echo htmlspecialchars($svc['proof']['source']); ?></span><?php endif; ?>
      </div>
    <?php else: ?>
      <div class="insight-quote reveal">
        <p>"<?php echo htmlspecialchars($svc['proof']['text']); ?>"</p>
      </div>
    <?php endif; ?>
  </div>
</section>

<!-- FAQ -->
<section class="section" style="background:var(--paper-deep);">
  <div class="wrap">
    <div class="section-head reveal">
      <span class="eyebrow">Before You Ask</span>
      <h2>What business owners usually ask first.</h2>
    </div>
    <div class="faq reveal">
      <?php foreach ($svc['faq'] as $qa): ?>
      <details class="faq-item">
        <summary><?php echo htmlspecialchars($qa[0]); ?></summary>
        <p><?php echo htmlspecialchars($qa[1]); ?></p>
      </details>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<!-- RELATED SERVICES -->
<section class="section">
  <div class="wrap">
    <div class="section-head reveal">
      <span class="eyebrow">Related Services</span>
      <h2>Pairs well with this.</h2>
    </div>
    <div class="service-grid reveal">
      <?php foreach ($svc['related'] as $relSlug):
        $rel = $svcBySlug[$relSlug] ?? null;
        if (!$rel) continue;
      ?>
      <a class="service-chip" href="/services/<?php echo htmlspecialchars($relSlug); ?>.php">
        <h3 class="service-chip__name"><?php echo htmlspecialchars($rel['name']); ?><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M5 12h14M13 6l6 6-6 6"/></svg></h3>
        <span class="service-chip__desc"><?php echo htmlspecialchars($rel['hero_lead']); ?></span>
      </a>
      <?php endforeach; ?>
    </div>

    <?php if ($product): ?>
    <div class="product-callout reveal">
      <div>
        <span class="product-callout__label">Related OCTG Product</span>
        <div class="product-callout__name"><?php echo htmlspecialchars($product['name']); ?></div>
        <p class="product-callout__desc"><?php echo htmlspecialchars($product['desc']); ?></p>
      </div>
      <a href="/products.php#<?php echo htmlspecialchars($product['slug']); ?>" class="btn btn-ghost">Learn More</a>
    </div>
    <?php endif; ?>
  </div>
</section>

<!-- FINAL CTA -->
<section class="section final-cta">
  <div class="wrap">
    <span class="eyebrow center">Every Business Deserves One Chance To Grow</span>
    <h2><?php echo htmlspecialchars($svc['cta_heading']); ?></h2>
    <p class="lead"><?php echo htmlspecialchars($svc['cta_lead']); ?></p>
    <div class="final-cta__ctas">
      <a href="tel:+18022768331" class="btn btn-primary">Call (802) 276-8331</a>
      <a href="/book-demo.php" class="btn btn-ghost">Book a Growth Call <svg class="btn-arrow" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M5 12h14M13 6l6 6-6 6"/></svg></a>
    </div>
  </div>
</section>

<?php
/* ---- Structured data: BreadcrumbList, Service, FAQPage ---- */
$breadcrumbSchema = [
    '@context' => 'https://schema.org',
    '@type' => 'BreadcrumbList',
    'itemListElement' => [
        ['@type' => 'ListItem', 'position' => 1, 'name' => 'Home', 'item' => 'https://onechancetogrow.com/'],
        ['@type' => 'ListItem', 'position' => 2, 'name' => 'Services', 'item' => 'https://onechancetogrow.com/services.php'],
        ['@type' => 'ListItem', 'position' => 3, 'name' => $svc['name'], 'item' => 'https://onechancetogrow.com/services/' . $svc['slug'] . '.php'],
    ],
];

$serviceSchema = [
    '@context' => 'https://schema.org',
    '@type' => 'Service',
    'name' => $svc['name'],
    'serviceType' => $svc['name'],
    'description' => $svc['hero_lead'],
    'provider' => [
        '@type' => 'ProfessionalService',
        'name' => 'One Chance To Grow LLC',
        'telephone' => '+1-802-276-8331',
        'url' => 'https://onechancetogrow.com/',
    ],
    'areaServed' => ['US', 'CA'],
    'url' => 'https://onechancetogrow.com/services/' . $svc['slug'] . '.php',
];

$faqSchema = [
    '@context' => 'https://schema.org',
    '@type' => 'FAQPage',
    'mainEntity' => array_map(function ($qa) {
        return [
            '@type' => 'Question',
            'name' => $qa[0],
            'acceptedAnswer' => ['@type' => 'Answer', 'text' => $qa[1]],
        ];
    }, $svc['faq']),
];
?>
<script type="application/ld+json"><?php echo json_encode($breadcrumbSchema, JSON_UNESCAPED_SLASHES); ?></script>
<script type="application/ld+json"><?php echo json_encode($serviceSchema, JSON_UNESCAPED_SLASHES); ?></script>
<script type="application/ld+json"><?php echo json_encode($faqSchema, JSON_UNESCAPED_SLASHES); ?></script>

<?php include __DIR__ . '/footer.php'; ?>
