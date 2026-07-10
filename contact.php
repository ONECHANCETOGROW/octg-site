<?php
/* ==========================================================================
   CONTACT.PHP — General contact page. Form posts to api/contact-handler.php.
   ========================================================================== */
$pageTitle       = 'Contact One Chance To Grow — Talk to a Growth Team, Not a Ticket Queue';
$pageDescription = "Tell us about your business and what's not working yet. A real person on the One Chance To Grow team replies, not an auto-response.";
$pageSlug        = 'contact';
$activeNav       = 'contact';
$bodyClass       = 'page-contact';

include __DIR__ . '/includes/header.php';
?>

<nav class="breadcrumb" aria-label="Breadcrumb">
  <a href="/index.php">Home</a>
  <span class="sep">/</span>
  <span aria-current="page">Contact</span>
</nav>

<section class="contact-hero wrap">
  <span class="eyebrow">Get In Touch</span>
  <h1 class="reveal-text">Let's Talk About Growing Your Business</h1>
  <p class="lead">Tell us what's not working yet — a slow site, inconsistent leads, a CRM nobody uses. A real person on our team reads every message and replies, not an auto-responder.</p>
</section>

<section class="section contact-layout" style="padding-top:0;">
  <div class="wrap contact-grid">

    <div class="contact-form-panel reveal">
      <form id="contactForm" action="/api/contact-handler.php" method="POST" novalidate>
        <input type="text" name="company_website" class="sr-only" tabindex="-1" autocomplete="off">
        <input type="hidden" name="source_page" value="/contact.php">

        <div class="field">
          <input type="text" name="name" id="cf-name" placeholder=" " required>
          <label for="cf-name">Your Name</label>
        </div>

        <div class="field-row">
          <div class="field">
            <input type="email" name="email" id="cf-email" placeholder=" " required>
            <label for="cf-email">Email Address</label>
          </div>
          <div class="field">
            <input type="tel" name="phone" id="cf-phone" placeholder=" ">
            <label for="cf-phone">Phone (optional)</label>
          </div>
        </div>

        <div class="field">
          <input type="text" name="business_name" id="cf-business" placeholder=" ">
          <label for="cf-business">Business Name</label>
        </div>

        <div class="field">
          <textarea name="message" id="cf-message" placeholder=" " required></textarea>
          <label for="cf-message">What's going on with your business?</label>
        </div>

        <button type="submit" class="btn btn-primary">
          <span class="spinner"></span>
          <span class="btn-label">Send Message</span>
          <svg class="btn-arrow" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M5 12h14M13 6l6 6-6 6"/></svg>
        </button>
        <span class="form-trust-note">We reply within one business day. No spam, ever.</span>
        <p class="form-status" data-form-status role="status" aria-live="polite"></p>
      </form>
    </div>

    <aside class="contact-side reveal">
      <div class="contact-side__block">
        <span class="eyebrow">Direct</span>
        <a href="tel:+18022768331" class="contact-side__phone">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"><path d="M5 4h4l2 5-2.5 1.5a11 11 0 0 0 5 5L15 13l5 2v4a2 2 0 0 1-2 2C9.4 21 3 14.6 3 6a2 2 0 0 1 2-2Z"/></svg>
          (802) 276-8331
        </a>
        <a href="mailto:hello@onechancetogrow.com" class="contact-side__email">hello@onechancetogrow.com</a>
        <p>Registered in Wyoming, USA<br>Serving businesses across the US &amp; Canada</p>
      </div>

      <div class="contact-side__block">
        <span class="eyebrow">What Happens Next</span>
        <ol class="contact-steps">
          <li><span>01</span>We read your message and match it to the right person on the team.</li>
          <li><span>02</span>You'll hear back within one business day — usually much sooner.</li>
          <li><span>03</span>If it's a fit, we'll set up a short call to talk through your business.</li>
        </ol>
      </div>

      <div class="contact-side__block contact-side__cta">
        <p>Prefer a structured conversation over a form?</p>
        <a href="/book-demo.php" class="btn btn-ghost">Book a Growth Call <svg class="btn-arrow" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M5 12h14M13 6l6 6-6 6"/></svg></a>
      </div>
    </aside>

  </div>
</section>

<section class="section" style="background:var(--paper-deep);">
  <div class="wrap">
    <div class="section-head reveal">
      <span class="eyebrow">Before You Reach Out</span>
      <h2>A few quick answers.</h2>
    </div>
    <div class="faq reveal">
      <details class="faq-item">
        <summary>How fast will I actually hear back?</summary>
        <p>Within one business day, usually sooner. If your message needs a specific person's attention, we'll tell you who and when to expect a reply.</p>
      </details>
      <details class="faq-item">
        <summary>Do I need to know which service I want first?</summary>
        <p>No. Most people who reach out aren't sure yet, that's normal. Tell us what's not working, and we'll help you figure out what would actually help.</p>
      </details>
      <details class="faq-item">
        <summary>Is there a cost to just talking?</summary>
        <p>No. An initial conversation costs nothing and comes with no obligation — it's how we figure out whether we're a good fit for each other.</p>
      </details>
      <details class="faq-item">
        <summary>Can I just call instead of filling out the form?</summary>
        <p>Yes — (802) 276-8331 reaches our team directly during business hours. The form works well for anything you'd rather explain in writing first.</p>
      </details>
    </div>
  </div>
</section>

<section class="section final-cta">
  <div class="wrap">
    <span class="eyebrow center">Every Business Deserves One Chance To Grow</span>
    <h2>Ready to see what's possible?</h2>
    <p class="lead">Book a free growth call, or send a message above, whichever feels easier to start with.</p>
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
    { "@type": "ListItem", "position": 2, "name": "Contact", "item": "https://onechancetogrow.com/contact.php" }
  ]
}
</script>
<script type="application/ld+json">
{
  "@context": "https://schema.org",
  "@type": "FAQPage",
  "mainEntity": [
    { "@type": "Question", "name": "How fast will I actually hear back?", "acceptedAnswer": { "@type": "Answer", "text": "Within one business day, usually sooner. If your message needs a specific person's attention, we'll tell you who and when to expect a reply." } },
    { "@type": "Question", "name": "Do I need to know which service I want first?", "acceptedAnswer": { "@type": "Answer", "text": "No. Most people who reach out aren't sure yet, that's normal. Tell us what's not working, and we'll help you figure out what would actually help." } },
    { "@type": "Question", "name": "Is there a cost to just talking?", "acceptedAnswer": { "@type": "Answer", "text": "No. An initial conversation costs nothing and comes with no obligation, it's how we figure out whether we're a good fit for each other." } },
    { "@type": "Question", "name": "Can I just call instead of filling out the form?", "acceptedAnswer": { "@type": "Answer", "text": "Yes, (802) 276-8331 reaches our team directly during business hours. The form works well for anything you'd rather explain in writing first." } }
  ]
}
</script>

<?php include __DIR__ . '/includes/footer.php'; ?>
