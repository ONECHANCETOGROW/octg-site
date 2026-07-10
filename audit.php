<?php
/* ==========================================================================
   AUDIT.PHP — free audit request. Lower-commitment lead path than
   book-demo.php's full growth-call booking flow.
   ========================================================================== */
$pageTitle       = 'Free Marketing Audit Request | One Chance To Grow';
$pageDescription = "Get a free, no-obligation look at your website, local visibility, reputation, or ad performance, and exactly where it's leaving growth on the table.";
$pageSlug        = 'audit';
$activeNav       = '';
$bodyClass       = 'page-audit';

include __DIR__ . '/includes/header.php';
?>

<nav class="breadcrumb" aria-label="Breadcrumb">
  <a href="/index.php">Home</a>
  <span class="sep">/</span>
  <span aria-current="page">Free Audit</span>
</nav>

<section class="audit-hero wrap">
  <span class="eyebrow center">Free Audit Request</span>
  <h1 class="reveal-text">See Exactly Where Growth Is Being Left on the Table</h1>
  <p class="lead">No pressure, no obligation. Tell us what to look at, and we'll send back specific, honest findings, not a generic checklist.</p>
</section>

<section class="section audit-layout" style="padding-top:20px;">
  <div class="wrap audit-grid">
    <div class="audit-form-panel reveal">
      <form id="auditForm" action="/api/audit-handler.php" method="POST" novalidate>
        <input type="text" name="company_website" class="sr-only" tabindex="-1" autocomplete="off">
        <input type="hidden" name="source_page" value="/audit.php">

        <div class="field-row">
          <div class="field">
            <input type="text" name="business_name" id="af-business" placeholder=" " required>
            <label for="af-business">Business Name</label>
          </div>
          <div class="field">
            <input type="url" name="website_url" id="af-url" placeholder=" ">
            <label for="af-url">Website URL (if you have one)</label>
          </div>
        </div>

        <div class="field-row">
          <div class="field">
            <input type="email" name="email" id="af-email" placeholder=" " required>
            <label for="af-email">Email Address</label>
          </div>
          <div class="field">
            <input type="tel" name="phone" id="af-phone" placeholder=" ">
            <label for="af-phone">Phone (optional)</label>
          </div>
        </div>

        <fieldset class="audit-checks">
          <legend>What should we look at?</legend>
          <label class="audit-check"><input type="checkbox" name="audit_areas[]" value="Website"> Website</label>
          <label class="audit-check"><input type="checkbox" name="audit_areas[]" value="Local SEO & Visibility"> Local SEO &amp; Visibility</label>
          <label class="audit-check"><input type="checkbox" name="audit_areas[]" value="Google Business Profile"> Google Business Profile</label>
          <label class="audit-check"><input type="checkbox" name="audit_areas[]" value="Reputation & Reviews"> Reputation &amp; Reviews</label>
          <label class="audit-check"><input type="checkbox" name="audit_areas[]" value="Ad Performance"> Ad Performance</label>
          <label class="audit-check"><input type="checkbox" name="audit_areas[]" value="Overall Growth Strategy"> Not Sure — Look at Everything</label>
        </fieldset>

        <button type="submit" class="btn btn-primary">
          <span class="spinner"></span>
          <span class="btn-label">Request My Free Audit</span>
        </button>
        <span class="form-trust-note">No obligation. No sales pressure. Just honest findings.</span>
        <p class="form-status" data-form-status role="status" aria-live="polite"></p>
      </form>
    </div>

    <aside class="audit-side reveal">
      <div class="audit-side__block">
        <span class="eyebrow">What You'll Get</span>
        <ul class="contact-steps">
          <li><span>01</span>A specific look at the areas you flagged, not a generic template report.</li>
          <li><span>02</span>Honest findings, including what's already working and shouldn't change.</li>
          <li><span>03</span>No obligation. If it's not a fit, we'll tell you that too.</li>
        </ul>
      </div>
      <div class="audit-side__block">
        <span class="eyebrow">Prefer to Talk First?</span>
        <a href="/book-demo.php" class="btn btn-ghost">Book a Growth Call Instead</a>
      </div>
    </aside>
  </div>
</section>

<section class="section final-cta" style="background:var(--paper-deep);">
  <div class="wrap">
    <span class="eyebrow center">Every Business Deserves One Chance To Grow</span>
    <h2>Not sure what you even need? That's exactly what the audit is for.</h2>
    <div class="final-cta__ctas">
      <a href="tel:+18022768331" class="btn btn-primary">Call (802) 276-8331</a>
      <a href="/services.php" class="btn btn-ghost">See All Services</a>
    </div>
  </div>
</section>

<script type="application/ld+json">
{
  "@context": "https://schema.org",
  "@type": "BreadcrumbList",
  "itemListElement": [
    { "@type": "ListItem", "position": 1, "name": "Home", "item": "https://onechancetogrow.com/" },
    { "@type": "ListItem", "position": 2, "name": "Free Audit", "item": "https://onechancetogrow.com/audit.php" }
  ]
}
</script>

<?php include __DIR__ . '/includes/footer.php'; ?>
