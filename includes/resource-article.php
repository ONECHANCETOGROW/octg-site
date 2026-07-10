<?php
/* ==========================================================================
   RESOURCE-ARTICLE.PHP — shared renderer for /resources/[slug].php articles.
   Each page is a 3-line file setting $articleSlug, same pattern as
   service-page.php and legal-page.php.
   ========================================================================== */

if (!isset($articleSlug)) {
    http_response_code(500);
    die('resource-article.php requires $articleSlug to be set before it is included.');
}

$ARTICLES = require __DIR__ . '/../data/resources-catalog.php';
$SERVICES = require __DIR__ . '/../data/services-catalog.php';
$svcBySlug = [];
foreach ($SERVICES as $row) { $svcBySlug[$row['slug']] = $row; }

$article = null;
foreach ($ARTICLES as $row) {
    if ($row['slug'] === $articleSlug) { $article = $row; break; }
}

if (!$article) {
    http_response_code(404);
    $pageTitle = 'Article Not Found | One Chance To Grow';
    $pageSlug = $articleSlug;
    $activeNav = '';
    include __DIR__ . '/header.php';
    echo '<section class="section wrap"><h1>Article not found</h1><p class="lead">That article doesn\'t exist yet. <a href="/resources.php">See all resources</a>.</p></section>';
    include __DIR__ . '/footer.php';
    exit;
}

$pageTitle       = $article['title'] . ' | One Chance To Grow';
$pageDescription = $article['meta_description'];
$pageSlug        = $article['slug'];
$canonicalPath   = 'resources/' . $article['slug'] . '.php';
$activeNav       = '';
$bodyClass       = 'page-article';
$pageStyle       = null;
$extraStyles     = ['resource-article'];
$pageScript      = null;

include __DIR__ . '/header.php';
?>

<nav class="breadcrumb" aria-label="Breadcrumb">
  <a href="/index.php">Home</a>
  <span class="sep">/</span>
  <a href="/resources.php">Resources</a>
  <span class="sep">/</span>
  <span aria-current="page"><?php echo htmlspecialchars($article['title']); ?></span>
</nav>

<article class="article-hero wrap">
  <span class="eyebrow center"><?php echo htmlspecialchars($article['catLabel']); ?></span>
  <h1 class="reveal-text center"><?php echo htmlspecialchars($article['title']); ?></h1>
  <span class="article-hero__meta">By the One Chance To Grow Team &middot; <?php echo htmlspecialchars($article['read_time']); ?></span>
</article>

<section class="section" style="padding-top:20px;">
  <div class="wrap">
    <div class="media-frame article-cover reveal">
      <?php octg_media($article['cover_key'], $article['title'] . ' Cover'); ?>
    </div>

    <?php if (!empty($article['quick_answer'])): ?>
    <div class="quick-answer reveal" style="margin-inline:auto; margin-bottom:40px;">
      <span class="quick-answer__label">Quick Answer</span>
      <p><?php echo htmlspecialchars($article['quick_answer']); ?></p>
    </div>
    <?php endif; ?>

    <div class="article-body reveal">
      <?php foreach ($article['body'] as $block): ?>
        <?php if (isset($block['h2'])): ?>
          <h2><?php echo htmlspecialchars($block['h2']); ?></h2>
        <?php elseif (isset($block['p'])): ?>
          <p><?php echo htmlspecialchars($block['p']); ?></p>
        <?php elseif (isset($block['list'])): ?>
          <ul class="check-list">
            <?php foreach ($block['list'] as $item): ?>
            <li>
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12l5 5L20 6"/></svg>
              <span><?php echo htmlspecialchars($item); ?></span>
            </li>
            <?php endforeach; ?>
          </ul>
        <?php endif; ?>
      <?php endforeach; ?>
    </div>

    <?php if (!empty($article['faq'])): ?>
    <div class="article-faq reveal">
      <h2>Quick Questions</h2>
      <div class="faq">
        <?php foreach ($article['faq'] as $qa): ?>
        <details class="faq-item">
          <summary><?php echo htmlspecialchars($qa[0]); ?></summary>
          <p><?php echo htmlspecialchars($qa[1]); ?></p>
        </details>
        <?php endforeach; ?>
      </div>
    </div>
    <?php endif; ?>

    <div class="section-head reveal" style="margin-top:64px;">
      <span class="eyebrow">Related Services</span>
      <h2>Where this connects.</h2>
    </div>
    <div class="service-grid reveal">
      <?php foreach ($article['related_services'] as $relSlug): $rel = $svcBySlug[$relSlug] ?? null; if (!$rel) continue; ?>
      <a class="service-chip" href="/services/<?php echo htmlspecialchars($relSlug); ?>.php">
        <h3 class="service-chip__name"><?php echo htmlspecialchars($rel['name']); ?><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M5 12h14M13 6l6 6-6 6"/></svg></h3>
        <span class="service-chip__desc"><?php echo htmlspecialchars($rel['hero_lead']); ?></span>
      </a>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<section class="section final-cta" style="background:var(--paper-deep);">
  <div class="wrap">
    <span class="eyebrow center">Every Business Deserves One Chance To Grow</span>
    <h2>Want this handled instead of DIY'd?</h2>
    <p class="lead">Book a growth call and we'll show you exactly where to start.</p>
    <div class="final-cta__ctas">
      <a href="/book-demo.php" class="btn btn-primary">Book a Growth Call</a>
      <a href="/resources.php" class="btn btn-ghost">More Articles</a>
    </div>
  </div>
</section>

<?php
$breadcrumbSchema = [
    '@context' => 'https://schema.org',
    '@type' => 'BreadcrumbList',
    'itemListElement' => [
        ['@type' => 'ListItem', 'position' => 1, 'name' => 'Home', 'item' => 'https://onechancetogrow.com/'],
        ['@type' => 'ListItem', 'position' => 2, 'name' => 'Resources', 'item' => 'https://onechancetogrow.com/resources.php'],
        ['@type' => 'ListItem', 'position' => 3, 'name' => $article['title'], 'item' => 'https://onechancetogrow.com/resources/' . $article['slug'] . '.php'],
    ],
];
$articleSchema = [
    '@context' => 'https://schema.org',
    '@type' => 'Article',
    'headline' => $article['title'],
    'description' => $article['meta_description'],
    'datePublished' => '2026-07-09',
    'dateModified' => '2026-07-09',
    'author' => ['@type' => 'Organization', 'name' => 'One Chance To Grow LLC'],
    'publisher' => ['@type' => 'Organization', 'name' => 'One Chance To Grow LLC'],
    'url' => 'https://onechancetogrow.com/resources/' . $article['slug'] . '.php',
];
?>
<script type="application/ld+json"><?php echo json_encode($breadcrumbSchema, JSON_UNESCAPED_SLASHES); ?></script>
<script type="application/ld+json"><?php echo json_encode($articleSchema, JSON_UNESCAPED_SLASHES); ?></script>
<?php if (!empty($article['faq'])): ?>
<script type="application/ld+json">
<?php
$faqSchema = [
    '@context' => 'https://schema.org',
    '@type' => 'FAQPage',
    'mainEntity' => array_map(function ($qa) {
        return ['@type' => 'Question', 'name' => $qa[0], 'acceptedAnswer' => ['@type' => 'Answer', 'text' => $qa[1]]];
    }, $article['faq']),
];
echo json_encode($faqSchema, JSON_UNESCAPED_SLASHES);
?>
</script>
<?php endif; ?>

<?php include __DIR__ . '/footer.php'; ?>
