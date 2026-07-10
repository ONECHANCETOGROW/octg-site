<?php
/* ==========================================================================
   RESOURCES.PHP — resource hub. Article detail pages aren't built yet, so
   cards are clearly marked "Coming Soon" rather than linking to dead pages.
   ========================================================================== */
$pageTitle       = 'Resources — Straight Answers for Growing Businesses | One Chance To Grow';
$pageDescription = 'Guides and breakdowns on SEO, reputation, automation, and growth strategy, written for business owners, not marketers.';
$pageSlug        = 'resources';
$activeNav       = '';
$bodyClass       = 'page-resources';

$ARTICLES = [
  ['cat'=>'seo','catLabel'=>'SEO & Visibility','title'=>'How Many Reviews Does Your Business Actually Need to Rank?','teaser'=>"The real answer isn't a number, it's a ratio most businesses get backwards.",'img'=>'article_1_image','slug'=>'reviews-needed-to-rank'],
  ['cat'=>'automation','catLabel'=>'Automation & AI','title'=>'The 5-Minute Rule for Responding to Leads','teaser'=>'Why the businesses that respond fastest win the job, even at a higher price.','img'=>'article_2_image','slug'=>'five-minute-rule-for-leads'],
  ['cat'=>'strategy','catLabel'=>'Business Strategy','title'=>'What to Actually Track in Your Marketing Reports','teaser'=>'Impressions and reach are activity metrics. Here is what actually predicts revenue.','img'=>'article_3_image','slug'=>'what-to-track-in-marketing-reports'],
  ['cat'=>'seo','catLabel'=>'SEO & Visibility','title'=>"Local SEO vs. Google Ads: Where to Start With a Small Budget",'teaser'=>'One compounds, one is immediate. Most businesses need both eventually, but not on day one.','img'=>'article_4_image','slug'=>'local-seo-vs-google-ads-small-budget'],
  ['cat'=>'automation','catLabel'=>'Automation & AI','title'=>'Why Most CRMs Fail in the First Six Months','teaser'=>"It's rarely the software. It's almost always the setup nobody finished.",'img'=>'article_5_image','slug'=>'why-crms-fail'],
  ['cat'=>'marketing','catLabel'=>'Marketing & Reputation','title'=>'Building a Reputation System Before You Need One','teaser'=>'The best time to set up review generation is before your first bad review, not after.','img'=>'article_6_image','slug'=>'reputation-system-before-you-need-one'],
];

$FILTERS = ['all'=>'All Topics','seo'=>'SEO & Visibility','marketing'=>'Marketing & Reputation','automation'=>'Automation & AI','strategy'=>'Business Strategy'];

include __DIR__ . '/includes/header.php';
?>

<nav class="breadcrumb" aria-label="Breadcrumb">
  <a href="/index.php">Home</a>
  <span class="sep">/</span>
  <span aria-current="page">Resources</span>
</nav>

<section class="resources-hero wrap">
  <span class="eyebrow center">Resources</span>
  <h1 class="reveal-text">Straight Answers for Growing Businesses</h1>
  <p class="lead">Guides and breakdowns on SEO, reputation, automation, and growth strategy, written for business owners, not marketers. New articles are added regularly.</p>
</section>

<section class="section" style="padding-top:20px;">
  <div class="wrap">
    <h2 class="sr-only">Browse Articles by Topic</h2>
    <div class="review-filters" role="tablist" aria-label="Filter articles by topic">
      <?php foreach ($FILTERS as $key => $label): ?>
      <button type="button" class="review-filter <?php echo $key === 'all' ? 'is-active' : ''; ?>" data-filter="<?php echo htmlspecialchars($key); ?>"><?php echo htmlspecialchars($label); ?></button>
      <?php endforeach; ?>
    </div>

    <div class="article-grid reveal">
      <?php foreach ($ARTICLES as $a): ?>
      <?php if ($a['slug']): ?>
      <a class="article-card" href="/resources/<?php echo htmlspecialchars($a['slug']); ?>.php" data-topic="<?php echo htmlspecialchars($a['cat']); ?>">
        <div class="media-frame article-card__frame">
          <?php octg_media($a['img'], $a['title'] . ' Cover'); ?>
        </div>
        <span class="article-card__cat"><?php echo htmlspecialchars($a['catLabel']); ?></span>
        <h3><?php echo htmlspecialchars($a['title']); ?></h3>
        <p><?php echo htmlspecialchars($a['teaser']); ?></p>
        <span class="article-card__read">Read the Guide
          <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M5 12h14M13 6l6 6-6 6"/></svg>
        </span>
      </a>
      <?php else: ?>
      <article class="article-card" data-topic="<?php echo htmlspecialchars($a['cat']); ?>">
        <div class="media-frame article-card__frame">
          <?php octg_media($a['img'], 'Article Cover'); ?>
          <span class="article-card__badge">Coming Soon</span>
        </div>
        <span class="article-card__cat"><?php echo htmlspecialchars($a['catLabel']); ?></span>
        <h3><?php echo htmlspecialchars($a['title']); ?></h3>
        <p><?php echo htmlspecialchars($a['teaser']); ?></p>
      </article>
      <?php endif; ?>
      <?php endforeach; ?>
    </div>
    <p class="filter-empty-msg" id="articlesEmpty" hidden>No articles in that topic yet, more are on the way.</p>
  </div>
</section>

<section class="section newsletter-section" style="background:var(--ink); color:var(--paper);">
  <div class="wrap newsletter-grid">
    <div>
      <span class="eyebrow">Stay Ahead</span>
      <h2>Get one growth idea a month. No spam, no fluff.</h2>
    </div>
    <form id="newsletterForm" action="/api/newsletter-handler.php" method="POST" class="newsletter-form" novalidate>
      <input type="text" name="company_website" class="sr-only" tabindex="-1" autocomplete="off">
      <div class="newsletter-field">
        <input type="email" name="email" placeholder="you@yourbusiness.com" required>
        <button type="submit" class="btn btn-on-dark">
          <span class="spinner"></span>
          <span class="btn-label">Subscribe</span>
        </button>
      </div>
      <p class="form-status" data-form-status role="status" aria-live="polite"></p>
    </form>
  </div>
</section>

<section class="section final-cta">
  <div class="wrap">
    <span class="eyebrow center">Have A Question We Haven't Covered?</span>
    <h2>Ask us directly.</h2>
    <p class="lead">Book a growth call and get a straight answer specific to your business.</p>
    <div class="final-cta__ctas">
      <a href="/book-demo.php" class="btn btn-primary">Book a Growth Call</a>
      <a href="/contact.php" class="btn btn-ghost">Contact Us</a>
    </div>
  </div>
</section>

<script type="application/ld+json">
{
  "@context": "https://schema.org",
  "@type": "BreadcrumbList",
  "itemListElement": [
    { "@type": "ListItem", "position": 1, "name": "Home", "item": "https://onechancetogrow.com/" },
    { "@type": "ListItem", "position": 2, "name": "Resources", "item": "https://onechancetogrow.com/resources.php" }
  ]
}
</script>

<?php include __DIR__ . '/includes/footer.php'; ?>
