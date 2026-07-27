<?php
/* ==========================================================================
   REVIEWS.PHP - Testimonial showcase.
   Pulls live reviews from cms_reviews.
   ========================================================================== */
$pageTitle       = 'Reviews - What Business Owners Say | One Chance To Grow';
$pageDescription = 'See how One Chance To Grow has helped businesses across the US and Canada fix their lead process, reputation, and growth systems.';
$pageSlug        = 'reviews';
$activeNav       = 'reviews';
$bodyClass       = 'page-reviews';

require_once __DIR__ . '/api/_lib.php';
$pdo = octg_db();
$reviews = $pdo ? $pdo->query('SELECT * FROM cms_reviews WHERE status="published" ORDER BY display_order ASC, created_at DESC')->fetchAll(PDO::FETCH_ASSOC) : [];

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
  <p class="lead">Real engagements, real outcomes - shown here as they're published.</p>
</section>

<section class="section" style="padding-top:20px;">
  <div class="wrap reviews-feed">
    
    <?php if (empty($reviews)): ?>
      <p style="text-align:center; color:var(--ink-soft);">More reviews coming soon.</p>
    <?php else: ?>
      <?php foreach ($reviews as $r): ?>
        <article class="review-card">
          <div class="review-card__stars">
            <?php 
              $rating = (int)$r['star_rating'];
              for ($i = 1; $i <= 5; $i++) {
                if ($i <= $rating) {
                  echo '<svg viewBox="0 0 24 24"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>';
                } else {
                  echo '<svg class="empty" viewBox="0 0 24 24"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>';
                }
              }
            ?>
          </div>
          
          <div class="review-card__text">
            <?php echo nl2br(htmlspecialchars($r['review_text'])); ?>
          </div>
          
          <div class="review-card__author">
            <?php if ($r['customer_avatar']): ?>
              <img src="<?php echo htmlspecialchars($r['customer_avatar']); ?>" alt="<?php echo htmlspecialchars($r['customer_name']); ?>" class="review-card__avatar">
            <?php endif; ?>
            <div class="review-card__meta">
              <span class="review-card__name"><?php echo htmlspecialchars($r['customer_name']); ?></span>
              <?php if ($r['job_title']): ?>
                <span class="review-card__role"><?php echo htmlspecialchars($r['job_title']); ?></span>
              <?php endif; ?>
            </div>
            <?php if ($r['company_logo']): ?>
              <img src="<?php echo htmlspecialchars($r['company_logo']); ?>" alt="<?php echo htmlspecialchars($r['company_name']); ?> Logo" class="review-card__logo">
            <?php elseif ($r['company_name']): ?>
               <span class="review-card__role" style="text-align:right;"><?php echo htmlspecialchars($r['company_name']); ?></span>
            <?php endif; ?>
          </div>
        </article>
      <?php endforeach; ?>
    <?php endif; ?>

  </div>
</section>

<section class="wrap">
  <div class="review-cta">
    <h2>Have you worked with One Chance To Grow?</h2>
    <p>We'd love to hear about your experience. Your feedback helps future business owners make informed decisions.</p>
    <a href="https://g.page/r/CVtKzxIAjGB_EBM/review" target="_blank" rel="noopener noreferrer" class="btn-google">
      <!-- Official Google G SVG -->
      <svg viewBox="0 0 48 48">
        <path fill="#EA4335" d="M24 9.5c3.54 0 6.71 1.22 9.21 3.6l6.85-6.85C35.9 2.38 30.47 0 24 0 14.62 0 6.51 5.38 2.56 13.22l7.98 6.19C12.43 13.72 17.74 9.5 24 9.5z"/>
        <path fill="#4285F4" d="M46.98 24.55c0-1.57-.15-3.09-.38-4.55H24v9.02h12.94c-.58 2.96-2.26 5.48-4.78 7.18l7.73 6c4.51-4.18 7.09-10.36 7.09-17.65z"/>
        <path fill="#FBBC05" d="M10.53 28.59c-.48-1.45-.76-2.99-.76-4.59s.27-3.14.76-4.59l-7.98-6.19C.92 16.46 0 20.12 0 24c0 3.88.92 7.54 2.56 10.78l7.97-6.19z"/>
        <path fill="#34A853" d="M24 48c6.48 0 11.93-2.13 15.89-5.81l-7.73-6c-2.15 1.45-4.92 2.3-8.16 2.3-6.26 0-11.57-4.22-13.47-9.91l-7.98 6.19C6.51 42.62 14.62 48 24 48z"/>
        <path fill="none" d="M0 0h48v48H0z"/>
      </svg>
      Leave a Google Review
      <svg class="arrow" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
        <line x1="5" y1="12" x2="19" y2="12"></line>
        <polyline points="12 5 19 12 12 19"></polyline>
      </svg>
    </a>
  </div>
</section>

<?php include __DIR__ . '/includes/footer.php'; ?>
