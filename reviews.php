<?php
/* ==========================================================================
   REVIEWS.PHP — testimonial showcase.
   Note: no Review/AggregateRating schema here on purpose — this content is
   illustrative placeholder copy (clearly marked "Client Name"), and schema
   markup asserts real, factual review data to search engines. Swap in real
   testimonials via data-cms-key first; add Review schema once they're real.
   ========================================================================== */
$pageTitle       = 'Reviews — What Business Owners Say | One Chance To Grow';
$pageDescription = 'See how One Chance To Grow has helped businesses across the US and Canada fix their lead process, reputation, and growth systems.';
$pageSlug        = 'reviews';
$activeNav       = 'reviews';
$bodyClass       = 'page-reviews';

$REVIEWS = [
  ['key'=>'review_1_photo','industry'=>'home-services','name'=>'Client Name','role'=>'Owner, Home Services Co.','quote'=>"One Chance To Grow rebuilt our lead process end to end. We stopped losing calls and started closing them."],
  ['key'=>'review_2_photo','industry'=>'retail','name'=>'Client Name','role'=>'Founder, Retail Brand','quote'=>"The first agency we've used that actually understood our CRM better than we did."],
  ['key'=>'review_3_photo','industry'=>'professional','name'=>'Client Name','role'=>'Managing Partner, Professional Services','quote'=>"Our AI receptionist alone paid for the whole engagement in the first month."],
  ['key'=>'review_4_photo','industry'=>'contractors','name'=>'Client Name','role'=>'Owner, Roofing Company','quote'=>"We went from hoping the phone would ring to a real system that books the job before a competitor calls back."],
  ['key'=>'review_5_photo','industry'=>'health','name'=>'Client Name','role'=>'Practice Manager, Dental Clinic','quote'=>"Review requests, reminders, and rebooking all run automatically now. Our front desk finally has time to focus on patients."],
  ['key'=>'review_6_photo','industry'=>'real-estate','name'=>'Client Name','role'=>'Broker, Real Estate Team','quote'=>"Every lead gets a response within minutes now, day or night. That alone changed how many deals we close."],
];

$FILTERS = [
  'all' => 'All Industries', 'home-services' => 'Home Services', 'professional' => 'Professional Services',
  'health' => 'Health & Wellness', 'retail' => 'Retail', 'real-estate' => 'Real Estate', 'contractors' => 'Contractors & Trades',
];

include __DIR__ . '/includes/header.php';
?>

<nav class="breadcrumb" aria-label="Breadcrumb">
  <a href="/index.php">Home</a>
  <span class="sep">/</span>
  <span aria-current="page">Reviews</span>
</nav>

<section class="reviews-hero wrap">
  <span class="eyebrow center">Reviews</span>
  <h1 class="reveal-text">Trusted By Businesses That Expect More</h1>
  <p class="lead">Real engagements, real outcomes — shown here as they're published. Filter by the industry closest to yours.</p>
</section>

<section class="section" style="padding-top:20px;">
  <div class="wrap">
    <div class="review-filters" role="tablist" aria-label="Filter reviews by industry">
      <?php foreach ($FILTERS as $key => $label): ?>
      <button type="button" class="review-filter <?php echo $key === 'all' ? 'is-active' : ''; ?>" data-filter="<?php echo htmlspecialchars($key); ?>"><?php echo htmlspecialchars($label); ?></button>
      <?php endforeach; ?>
    </div>

    <div class="quote-row reviews-grid reveal">
      <?php foreach ($REVIEWS as $r): ?>
      <article class="quote-card" data-industry="<?php echo htmlspecialchars($r['industry']); ?>">
        <svg class="mark" viewBox="0 0 32 24" fill="currentColor"><path d="M0 24V13.6C0 6 4.8 1 12.8 0l1.6 4.4C9.6 5.6 7.2 8 7.2 12h6.4V24H0zm18 0V13.6C18 6 22.8 1 30.8 0l1.6 4.4c-4.8 1.2-7.2 3.6-7.2 7.6h6.4V24H18z"/></svg>
        <p class="q"><?php echo htmlspecialchars($r['quote']); ?></p>
        <div class="quote-card__who">
          <div class="media-frame is-round">
            <?php octg_media($r['key'], 'Client Photo', '', true); ?>
          </div>
          <div><strong><?php echo htmlspecialchars($r['name']); ?></strong><span><?php echo htmlspecialchars($r['role']); ?></span></div>
        </div>
      </article>
      <?php endforeach; ?>
    </div>
    <p class="filter-empty-msg" id="reviewsEmpty" hidden>No reviews in that category yet — check back soon, or see all reviews above.</p>
  </div>
</section>

<section class="section final-cta">
  <div class="wrap">
    <span class="eyebrow center">Add Yours</span>
    <h2>Ready to be the next one on this page?</h2>
    <p class="lead">Book a growth call and let's find out what a system built around your business could do.</p>
    <div class="final-cta__ctas">
      <a href="/book-demo.php" class="btn btn-primary">Book a Growth Call</a>
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
    { "@type": "ListItem", "position": 2, "name": "Reviews", "item": "https://onechancetogrow.com/reviews.php" }
  ]
}
</script>

<?php include __DIR__ . '/includes/footer.php'; ?>
