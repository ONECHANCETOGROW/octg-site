<?php
/* ==========================================================================
   CAREERS.PHP — honest holding page. No fabricated job listings.
   ========================================================================== */
$pageTitle       = 'Careers at One Chance To Grow';
$pageDescription = "We're not actively hiring right now, but we're always glad to hear from people who do great marketing, development, or automation work.";
$pageSlug        = 'careers';
$activeNav       = '';
$bodyClass       = 'page-careers';

include __DIR__ . '/includes/header.php';
?>

<nav class="breadcrumb" aria-label="Breadcrumb">
  <a href="/index.php">Home</a>
  <span class="sep">/</span>
  <span aria-current="page">Careers</span>
</nav>

<section class="careers-hero wrap">
  <span class="eyebrow center">Careers</span>
  <h1 class="reveal-text">Building a Team the Same Way We Build Growth Systems</h1>
  <p class="lead">We're not actively hiring for a specific role right now. When we are, this page will say so specifically, not with a list of generic openings.</p>
</section>

<section class="section">
  <div class="wrap">
    <div class="section-head reveal">
      <span class="eyebrow">What We Look For</span>
      <h2>The traits that matter more than a resume line.</h2>
    </div>
    <div class="why-grid reveal">
      <div class="why-item">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"><path d="M12 3l2.5 5.5L20 9l-4 4 1 6-5-3-5 3 1-6-4-4 5.5-.5z"/></svg>
        <h3>Ownership over instructions</h3>
        <p>We look for people who improve the plan, not just execute it exactly as written.</p>
      </div>
      <div class="why-item">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="9"/><path d="M8 12l3 3 5-6"/></svg>
        <h3>Straight talk</h3>
        <p>Internally and with clients, we'd rather hear "this isn't working" early than "everything's fine" late.</p>
      </div>
      <div class="why-item">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"><path d="M13 2L4 14h6l-1 8 9-12h-6l1-8z"/></svg>
        <h3>Comfortable with modern tools</h3>
        <p>AI and automation are part of how we work day to day, not a topic we talk about but avoid using.</p>
      </div>
    </div>
  </div>
</section>

<section class="section final-cta" style="background:var(--paper-deep);">
  <div class="wrap">
    <span class="eyebrow center">Get On Our Radar</span>
    <h2>Think you'd be a fit down the line?</h2>
    <p class="lead">Send a short note and your background to hello@onechancetogrow.com. We keep good people in mind for when a real opening comes up.</p>
    <div class="final-cta__ctas">
      <a href="mailto:hello@onechancetogrow.com" class="btn btn-primary">Email Us</a>
      <a href="/about.php" class="btn btn-ghost">Learn About Us</a>
    </div>
  </div>
</section>

<script type="application/ld+json">
{
  "@context": "https://schema.org",
  "@type": "BreadcrumbList",
  "itemListElement": [
    { "@type": "ListItem", "position": 1, "name": "Home", "item": "https://onechancetogrow.com/" },
    { "@type": "ListItem", "position": 2, "name": "Careers", "item": "https://onechancetogrow.com/careers.php" }
  ]
}
</script>

<?php include __DIR__ . '/includes/footer.php'; ?>
