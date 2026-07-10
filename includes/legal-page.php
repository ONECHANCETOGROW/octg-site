<?php
/* ==========================================================================
   LEGAL-PAGE.PHP — shared renderer for privacy.php, terms.php, cookies.php,
   and accessibility.php. Each is a 3-line file setting $legalKey.
   ========================================================================== */

if (!isset($legalKey)) {
    http_response_code(500);
    die('legal-page.php requires $legalKey to be set before it is included.');
}

$LEGAL = require __DIR__ . '/../data/legal-content.php';
$doc = $LEGAL[$legalKey] ?? null;

if (!$doc) {
    http_response_code(404);
    $pageTitle = 'Page Not Found | One Chance To Grow';
    $pageSlug = $legalKey;
    include __DIR__ . '/header.php';
    echo '<section class="section wrap"><h1>Page not found</h1></section>';
    include __DIR__ . '/footer.php';
    exit;
}

$pageTitle       = $doc['title'] . ' | One Chance To Grow';
$pageDescription = $doc['intro'];
$pageSlug        = $legalKey;
$activeNav       = '';
$bodyClass       = 'page-legal';
$pageStyle       = null;
$extraStyles     = ['legal-page'];
$pageScript      = null;

include __DIR__ . '/header.php';
?>

<nav class="breadcrumb" aria-label="Breadcrumb">
  <a href="/index.php">Home</a>
  <span class="sep">/</span>
  <span aria-current="page"><?php echo htmlspecialchars($doc['title']); ?></span>
</nav>

<section class="legal-hero wrap">
  <h1><?php echo htmlspecialchars($doc['title']); ?></h1>
  <span class="legal-updated">Last updated: <?php echo htmlspecialchars($doc['updated']); ?></span>
  <p class="lead"><?php echo htmlspecialchars($doc['intro']); ?></p>
</section>

<section class="section legal-body" style="padding-top:0;">
  <div class="wrap">
    <?php foreach ($doc['sections'] as $s): ?>
    <div class="legal-section reveal">
      <h2><?php echo htmlspecialchars($s['h']); ?></h2>
      <p><?php echo htmlspecialchars($s['body']); ?></p>
    </div>
    <?php endforeach; ?>
    <p class="legal-disclaimer">This page is provided as general information for site visitors and isn't a substitute for legal advice specific to your situation.</p>
  </div>
</section>

<section class="section final-cta">
  <div class="wrap">
    <span class="eyebrow center">Questions?</span>
    <h2>We're happy to walk through any of this with you.</h2>
    <div class="final-cta__ctas">
      <a href="/contact.php" class="btn btn-primary">Contact Us</a>
      <a href="tel:+18022768331" class="btn btn-ghost">Call (802) 276-8331</a>
    </div>
  </div>
</section>

<script type="application/ld+json">
{
  "@context": "https://schema.org",
  "@type": "BreadcrumbList",
  "itemListElement": [
    { "@type": "ListItem", "position": 1, "name": "Home", "item": "https://onechancetogrow.com/" },
    { "@type": "ListItem", "position": 2, "name": "<?php echo htmlspecialchars($doc['title']); ?>", "item": "https://onechancetogrow.com/<?php echo htmlspecialchars($legalKey); ?>.php" }
  ]
}
</script>

<?php include __DIR__ . '/footer.php'; ?>
