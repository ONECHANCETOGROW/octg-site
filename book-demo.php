<?php
/* ==========================================================================
   BOOK-DEMO.PHP — the premium multi-step growth-call booking flow.
   Posts to api/book-demo-handler.php on the final step.
   ========================================================================== */
$pageTitle       = 'Book a Growth Call — One Chance To Grow';
$pageDescription = 'A short, guided form — not a sales form. Tell us about your business and we\'ll show you exactly where growth is being left on the table.';
$pageSlug        = 'book-demo';
$activeNav       = '';
$bodyClass       = 'page-book-demo';

include __DIR__ . '/includes/header.php';
?>

<nav class="breadcrumb" aria-label="Breadcrumb">
  <a href="/index.php">Home</a>
  <span class="sep">/</span>
  <span aria-current="page">Book a Growth Call</span>
</nav>

<section class="demo-intro wrap">
  <span class="eyebrow">Book a Growth Call</span>
  <h1 class="reveal-text">Let's Build Your Growth Plan</h1>
  <p class="lead">Nine short questions, about two minutes, no sales pitch. Answer what you can — we'll fill in the rest on the call.</p>
</section>

<section class="demo-layout wrap">
  <div class="demo-bg-blob demo-bg-blob--1" aria-hidden="true"></div>
  <div class="demo-bg-blob demo-bg-blob--2" aria-hidden="true"></div>
  <div class="demo-wizard reveal" id="demoWizard">

    <div class="demo-wizard__progress">
      <div class="demo-wizard__bar"><div class="demo-wizard__bar-fill" id="demoBarFill"></div></div>
      <span class="demo-wizard__count" id="demoStepCount">Step 1 of 9</span>
    </div>

    <form id="demoForm" action="/api/book-demo-handler.php" method="POST" novalidate>
      <input type="text" name="company_website" class="sr-only" tabindex="-1" autocomplete="off">
      <input type="hidden" name="source_page" value="/book-demo.php">

      <div class="demo-steps" id="demoSteps">

        <div class="demo-step is-active" data-step="1">
          <span class="demo-step__label">Let's start with the basics</span>
          <h2>What's the name of your business?</h2>
          <input type="text" name="business_name" class="demo-input" data-required autofocus placeholder="e.g. Riverside Home Services">
        </div>

        <div class="demo-step" data-step="2">
          <span class="demo-step__label">Good to meet you</span>
          <h2>And what's your name?</h2>
          <input type="text" name="contact_name" class="demo-input" data-required placeholder="Your full name">
        </div>

        <div class="demo-step" data-step="3">
          <span class="demo-step__label">So we can send your plan</span>
          <h2>What's the best email to reach you?</h2>
          <input type="email" name="email" class="demo-input" data-required data-type="email" placeholder="you@yourbusiness.com">
        </div>

        <div class="demo-step" data-step="4">
          <span class="demo-step__label">In case a quick call is easier</span>
          <h2>What's the best phone number?</h2>
          <input type="tel" name="phone" class="demo-input" data-required placeholder="(555) 123-4567">
        </div>

        <div class="demo-step" data-step="5">
          <span class="demo-step__label">Helps us tailor the plan</span>
          <h2>What kind of business is it?</h2>
          <input type="hidden" name="business_type" id="businessTypeInput">
          <div class="pill-group" data-select="single" data-target="businessTypeInput">
            <button type="button" class="pill">Home Services</button>
            <button type="button" class="pill">Professional Services</button>
            <button type="button" class="pill">Health &amp; Wellness</button>
            <button type="button" class="pill">Retail &amp; E-Commerce</button>
            <button type="button" class="pill">Real Estate</button>
            <button type="button" class="pill">Hospitality</button>
            <button type="button" class="pill">B2B &amp; SaaS</button>
            <button type="button" class="pill">Contractors &amp; Trades</button>
            <button type="button" class="pill">Other</button>
          </div>
        </div>

        <div class="demo-step" data-step="6">
          <span class="demo-step__label">Pick as many as fit — we'll narrow it down together</span>
          <h2>What are you interested in?</h2>
          <div class="pill-group" data-select="multi" data-name="services_interested">
            <button type="button" class="pill">Websites &amp; Digital Presence</button>
            <button type="button" class="pill">Local Visibility &amp; SEO</button>
            <button type="button" class="pill">Reputation, Content &amp; Brand</button>
            <button type="button" class="pill">Advertising &amp; Lead Gen</button>
            <button type="button" class="pill">CRM, Automation &amp; AI</button>
            <button type="button" class="pill">Software &amp; Analytics</button>
            <button type="button" class="pill">Not Sure Yet</button>
          </div>
        </div>

        <div class="demo-step" data-step="7">
          <span class="demo-step__label">Helps us recommend the right starting point</span>
          <h2>What's your monthly budget range?</h2>
          <input type="hidden" name="budget_range" id="budgetInput">
          <div class="pill-group" data-select="single" data-target="budgetInput">
            <button type="button" class="pill">Under $1,500/mo</button>
            <button type="button" class="pill">$1,500 – $3,000/mo</button>
            <button type="button" class="pill">$3,000 – $6,000/mo</button>
            <button type="button" class="pill">$6,000+/mo</button>
            <button type="button" class="pill">Not Sure Yet</button>
          </div>
        </div>

        <div class="demo-step" data-step="8">
          <span class="demo-step__label">The most important question</span>
          <h2>What are you hoping to achieve?</h2>
          <textarea name="goals" class="demo-input demo-textarea" data-required placeholder="More calls, a better website, one system instead of five vendors — whatever it is."></textarea>
        </div>

        <div class="demo-step demo-step--review" data-step="9">
          <span class="demo-step__label">Almost there</span>
          <h2>Take a quick look before you send it.</h2>
          <div class="demo-review" id="demoReview"></div>
          <button type="submit" class="btn btn-primary demo-submit">
            <span class="spinner"></span>
            <span class="btn-label">Send My Growth Plan Request</span>
          </button>
          <span class="form-trust-note">No pressure, no obligation, no spam.</span>
          <p class="form-status" data-form-status role="status" aria-live="polite"></p>
        </div>

      </div>

      <div class="demo-wizard__nav">
        <button type="button" class="demo-nav-btn demo-nav-btn--back" id="demoBack">
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M19 12H5M11 18l-6-6 6-6"/></svg>
          Back
        </button>
        <button type="button" class="demo-nav-btn demo-nav-btn--next" id="demoNext">
          <span class="btn-label">Continue</span>
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M5 12h14M13 6l6 6-6 6"/></svg>
        </button>
      </div>
    </form>

    <div class="demo-success" id="demoSuccess" hidden>
      <svg class="demo-success__check" viewBox="0 0 60 60" fill="none">
        <circle cx="30" cy="30" r="27" stroke="#5C8F22" stroke-width="2"/>
        <path d="M18 31l8 8 16-18" stroke="#5C8F22" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"/>
      </svg>
      <h2>Your growth plan request is in.</h2>
      <p class="lead">We'll review what you told us and follow up within one business day — usually much sooner.</p>
      <a href="/services.php" class="btn btn-ghost">Browse Services While You Wait</a>
    </div>
  </div>

  <aside class="demo-side reveal">
    <div class="demo-side__block">
      <span class="eyebrow">No Pressure</span>
      <p>This isn't a sales call. It's a straight conversation about where your business is losing growth, and whether we're the right team to fix it.</p>
    </div>
    <div class="demo-side__block">
      <span class="eyebrow">Prefer to Talk Now?</span>
      <a href="tel:+18022768331" class="demo-side__phone">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"><path d="M5 4h4l2 5-2.5 1.5a11 11 0 0 0 5 5L15 13l5 2v4a2 2 0 0 1-2 2C9.4 21 3 14.6 3 6a2 2 0 0 1 2-2Z"/></svg>
        (802) 276-8331
      </a>
    </div>
  </aside>
</section>

<script type="application/ld+json">
{
  "@context": "https://schema.org",
  "@type": "BreadcrumbList",
  "itemListElement": [
    { "@type": "ListItem", "position": 1, "name": "Home", "item": "https://onechancetogrow.com/" },
    { "@type": "ListItem", "position": 2, "name": "Book a Growth Call", "item": "https://onechancetogrow.com/book-demo.php" }
  ]
}
</script>

<?php include __DIR__ . '/includes/footer.php'; ?>
