<?php
/* ==========================================================================
   CASE-STUDY.PHP — shared renderer for /projects/[slug].php case studies.
   Same 3-line-thin-file pattern as service-page.php / resource-article.php.
   ========================================================================== */

if (!isset($caseSlug)) {
    http_response_code(500);
    die('case-study.php requires $caseSlug to be set before it is included.');
}

$CASES = require __DIR__ . '/../data/case-studies-catalog.php';
usort($CASES, function($a, $b){ return $a['order'] <=> $b['order']; });
$SERVICES = require __DIR__ . '/../data/services-catalog.php';
$svcBySlug = [];
foreach ($SERVICES as $row) { $svcBySlug[$row['slug']] = $row; }

$case = null;
$caseIndex = null;
foreach ($CASES as $i => $row) {
    if ($row['slug'] === $caseSlug) { $case = $row; $caseIndex = $i; break; }
}

if (!$case) {
    http_response_code(404);
    $pageTitle = 'Case Study Not Found | One Chance To Grow';
    $pageSlug = $caseSlug;
    $activeNav = '';
    include __DIR__ . '/header.php';
    echo '<section class="section wrap"><h1>Case study not found</h1><p class="lead">That case study doesn\'t exist yet. <a href="/projects.php">See all projects</a>.</p></section>';
    include __DIR__ . '/footer.php';
    exit;
}

$nextCase = $CASES[($caseIndex + 1) % count($CASES)];

$pageTitle       = $case['title'] . ' — ' . $case['industry'] . ' Case Study | One Chance To Grow';
$pageDescription = $case['challenge'];
$pageSlug        = $case['slug'];
$canonicalPath   = 'projects/' . $case['slug'] . '.php';
$activeNav       = '';
$bodyClass       = 'page-case-study';
$pageStyle       = null;
$extraStyles     = ['case-study'];
$pageScript      = null;

include __DIR__ . '/header.php';
?>

<nav class="breadcrumb" aria-label="Breadcrumb">
  <a href="/index.php">Home</a>
  <span class="sep">/</span>
  <a href="/projects.php">Projects</a>
  <span class="sep">/</span>
  <span aria-current="page"><?php echo htmlspecialchars($case['title']); ?></span>
</nav>

<section class="case-hero wrap">
  <span class="eyebrow"><?php echo htmlspecialchars($case['industry']); ?> &middot; <?php echo htmlspecialchars($case['client_label']); ?></span>
  <h1 class="reveal-text"><?php echo htmlspecialchars($case['title']); ?></h1>
  <div class="media-frame case-hero__frame reveal">
    <?php octg_media($case['hero_key'], $case['title'] . ' Hero Image', '', false, true); ?>
  </div>
</section>

<section class="section" style="padding-top:0;">
  <div class="wrap case-grid">
    <div class="case-col reveal">
      <span class="case-col__label">The Challenge</span>
      <p><?php echo htmlspecialchars($case['challenge']); ?></p>
    </div>
    <div class="case-col reveal">
      <span class="case-col__label">The Solution</span>
      <p><?php echo htmlspecialchars($case['solution']); ?></p>
    </div>
  </div>
</section>

<section class="section" style="background:var(--paper-deep);">
  <div class="wrap">
    <div class="section-head reveal">
      <span class="eyebrow">The Results</span>
      <h2>What changed.</h2>
    </div>
    <div class="case-results reveal">
      <?php foreach ($case['results'] as $r): ?>
      <div class="case-result">
        <span class="case-result__metric"><?php echo htmlspecialchars($r['metric']); ?></span>
        <span class="case-result__label"><?php echo htmlspecialchars($r['label']); ?></span>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<section class="section">
  <div class="wrap">
    <div class="section-head reveal">
      <span class="eyebrow">How It Happened</span>
      <h2>The timeline.</h2>
    </div>
    <div class="timeline">
      <div class="timeline__fill"></div>
      <?php foreach ($case['timeline'] as $t): ?>
      <div class="timeline-item">
        <div class="timeline-item__dot"></div>
        <span class="timeline-item__year"><?php echo htmlspecialchars($t['phase']); ?></span>
        <h3><?php echo htmlspecialchars($t['title']); ?></h3>
        <p><?php echo htmlspecialchars($t['body']); ?></p>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<?php if (!empty($case['gallery_keys'])): ?>
<section class="section" style="background:var(--paper-deep);">
  <div class="wrap">
    <h2 class="sr-only">Project Gallery</h2>
    <div class="case-gallery reveal">
      <?php foreach ($case['gallery_keys'] as $gKey): ?>
      <div class="media-frame case-gallery__frame">
        <?php octg_media($gKey, $case['title'] . ' Gallery Image'); ?>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>
<?php endif; ?>

<section class="section">
  <div class="wrap">
    <blockquote class="case-quote reveal">
      <svg class="mark" viewBox="0 0 32 24" fill="currentColor"><path d="M0 24V13.6C0 6 4.8 1 12.8 0l1.6 4.4C9.6 5.6 7.2 8 7.2 12h6.4V24H0zm18 0V13.6C18 6 22.8 1 30.8 0l1.6 4.4c-4.8 1.2-7.2 3.6-7.2 7.6h6.4V24H18z"/></svg>
      <p><?php echo htmlspecialchars($case['quote']); ?></p>
      <cite><?php echo htmlspecialchars($case['quote_role']); ?></cite>
    </blockquote>
  </div>
</section>

<section class="section" style="background:var(--paper-deep);">
  <div class="wrap">
    <div class="section-head reveal">
      <span class="eyebrow">Services Used</span>
      <h2>What we built for this.</h2>
    </div>
    <div class="service-grid reveal">
      <?php foreach ($case['services_used'] as $relSlug): $rel = $svcBySlug[$relSlug] ?? null; if (!$rel) continue; ?>
      <a class="service-chip" href="/services/<?php echo htmlspecialchars($relSlug); ?>.php">
        <h3 class="service-chip__name"><?php echo htmlspecialchars($rel['name']); ?><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M5 12h14M13 6l6 6-6 6"/></svg></h3>
        <span class="service-chip__desc"><?php echo htmlspecialchars($rel['hero_lead']); ?></span>
      </a>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<section class="section case-next">
  <div class="wrap">
    <a href="/projects/<?php echo htmlspecialchars($nextCase['slug']); ?>.php" class="case-next__link">
      <span class="case-next__label">Next Project</span>
      <h2><?php echo htmlspecialchars($nextCase['title']); ?>
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M5 12h14M13 6l6 6-6 6"/></svg>
      </h2>
    </a>
  </div>
</section>

<section class="section final-cta" style="background:var(--paper-deep);">
  <div class="wrap">
    <span class="eyebrow center">Every Business Deserves One Chance To Grow</span>
    <h2>Want a similar system for your business?</h2>
    <div class="final-cta__ctas">
      <a href="/book-demo.php" class="btn btn-primary">Book a Growth Call</a>
      <a href="/projects.php" class="btn btn-ghost">See All Projects</a>
    </div>
  </div>
</section>

<script type="application/ld+json">
<?php
$breadcrumbSchema = [
    '@context' => 'https://schema.org',
    '@type' => 'BreadcrumbList',
    'itemListElement' => [
        ['@type' => 'ListItem', 'position' => 1, 'name' => 'Home', 'item' => 'https://onechancetogrow.com/'],
        ['@type' => 'ListItem', 'position' => 2, 'name' => 'Projects', 'item' => 'https://onechancetogrow.com/projects.php'],
        ['@type' => 'ListItem', 'position' => 3, 'name' => $case['title'], 'item' => 'https://onechancetogrow.com/projects/' . $case['slug'] . '.php'],
    ],
];
echo json_encode($breadcrumbSchema, JSON_UNESCAPED_SLASHES);
?>
</script>

<?php include __DIR__ . '/footer.php'; ?>
