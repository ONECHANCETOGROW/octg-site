<?php
/* ==========================================================================
   SERVICES.PHP — Services hub. Lists all 33 services in 6 categories.
   Each service links out to its own future page under /services/[slug].php
   (already wired into the shared mega-menu in includes/navigation.php).
   ========================================================================== */
$pageTitle       = 'Services — Websites, SEO, Reputation, Ads, CRM & AI Automation | One Chance To Grow';
$pageDescription = '100 business problems, one brand, one solution. Websites, local visibility, reputation, advertising, CRM, and AI automation — built as one system for US and Canadian businesses.';
$pageSlug        = 'services';
$activeNav       = 'services';
$bodyClass       = 'page-services';

include __DIR__ . '/includes/header.php';
?>

<nav class="breadcrumb" aria-label="Breadcrumb">
  <a href="/index.php">Home</a>
  <span class="sep">/</span>
  <span aria-current="page">Services</span>
</nav>

  <!-- SERVICES HERO -->
  <section class="services-hero wrap">
    <span class="eyebrow center">Our Services</span>
    <h1 class="reveal-text">100 Business Problems. <span class="accent-italic">One Brand. One Solution.</span></h1>
    <p class="lead">Every business deserves one chance to grow. Below is the complete system we build to make sure yours does — websites, visibility, reputation, advertising, automation, and software, working as one.</p>
    <div class="services-hero__ctas">
      <a href="/book-demo.php" class="btn btn-primary">Book a Growth Call <svg class="btn-arrow" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M5 12h14M13 6l6 6-6 6"/></svg></a>
      <a href="tel:+18022768331" class="btn btn-ghost">Call (802) 276-8331</a>
    </div>

    <div class="stat-strip reveal">
      <div class="stat-card">
        <span class="stat-card__num">2.3</span>
        <span class="stat-card__text">The average growing business already juggles more than two separate agencies just to cover marketing, web, and software — with no one owning the outcome.</span>
        <span class="stat-card__source">Source: Omnivance Media, 2026</span>
      </div>
      <div class="stat-card">
        <span class="stat-card__num">88%</span>
        <span class="stat-card__text">of people who search for something nearby on their phone visit a related business within the week — if you're visible, you're in the running.</span>
        <span class="stat-card__source">Source: local search behavior research, 2026</span>
      </div>
    </div>
  </section>

  <!-- CATEGORY QUICK NAV -->
  <nav class="category-nav" aria-label="Jump to a service category">
    <a href="#websites">Websites</a>
    <a href="#visibility">Visibility &amp; SEO</a>
    <a href="#reputation">Reputation &amp; Brand</a>
    <a href="#advertising">Advertising</a>
    <a href="#automation">CRM &amp; Automation</a>
    <a href="#software">Software</a>
  </nav>

  <!-- A. WEBSITES & DIGITAL PRESENCE -->
  <section class="section" id="websites">
    <div class="wrap">
      <div class="service-category__head reveal">
        <span class="eyebrow">Websites &amp; Digital Presence</span>
        <h2>A website built to work as hard as you do.</h2>
        <p class="lead" style="margin-top:22px;">Your website should do more than look good — it should load fast, rank well, and turn visitors into calls, forms, and bookings while you're not watching.</p>
      </div>
      <div class="service-grid reveal">
        <a class="service-chip" href="/services/website-development.php">
          <h3 class="service-chip__name">Website Development<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M5 12h14M13 6l6 6-6 6"/></svg></h3>
          <span class="service-chip__desc">A fast, modern website built to turn visitors into customers, not just look nice.</span>
        </a>
        <a class="service-chip" href="/services/website-redesign.php">
          <h3 class="service-chip__name">Website Redesign<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M5 12h14M13 6l6 6-6 6"/></svg></h3>
          <span class="service-chip__desc">Your current site, rebuilt around what actually gets people to call.</span>
        </a>
        <a class="service-chip" href="/services/landing-pages.php">
          <h3 class="service-chip__name">Landing Pages<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M5 12h14M13 6l6 6-6 6"/></svg></h3>
          <span class="service-chip__desc">Focused pages built around a single offer and a single goal: the conversion.</span>
        </a>
        <a class="service-chip" href="/services/ecommerce-development.php">
          <h3 class="service-chip__name">eCommerce Development<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M5 12h14M13 6l6 6-6 6"/></svg></h3>
          <span class="service-chip__desc">An online store built to sell — not just to display products.</span>
        </a>
        <a class="service-chip" href="/services/custom-web-applications.php">
          <h3 class="service-chip__name">Custom Web Applications<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M5 12h14M13 6l6 6-6 6"/></svg></h3>
          <span class="service-chip__desc">Tools built specifically around how your business actually operates.</span>
        </a>
      </div>
    </div>
  </section>

  <!-- B. LOCAL VISIBILITY & SEO -->
  <section class="section" id="visibility" style="background:var(--paper-deep);">
    <div class="wrap">
      <div class="service-category__head reveal">
        <span class="eyebrow">Local Visibility &amp; SEO</span>
        <h2>Be the business Google — and your customers — find first.</h2>
        <p class="lead" style="margin-top:22px;">Most customers never make it past the first page, and most never scroll past the map pack. We build the visibility that keeps you there — not just for the month after a campaign launches.</p>
      </div>
      <div class="service-grid reveal">
        <a class="service-chip" href="/services/seo.php">
          <h3 class="service-chip__name">Search Engine Optimization<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M5 12h14M13 6l6 6-6 6"/></svg></h3>
          <span class="service-chip__desc">More people finding you on Google, without paying for every single click.</span>
        </a>
        <a class="service-chip" href="/services/local-seo.php">
          <h3 class="service-chip__name">Local SEO<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M5 12h14M13 6l6 6-6 6"/></svg></h3>
          <span class="service-chip__desc">Ranking in your city, your county, your service area — where customers actually search.</span>
        </a>
        <a class="service-chip" href="/services/google-business-profile-optimization.php">
          <h3 class="service-chip__name">Google Business Profile<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M5 12h14M13 6l6 6-6 6"/></svg></h3>
          <span class="service-chip__desc">A profile built to win the map pack, not just sit there half-finished.</span>
        </a>
        <a class="service-chip" href="/services/google-maps-ranking.php">
          <h3 class="service-chip__name">Google Maps Ranking<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M5 12h14M13 6l6 6-6 6"/></svg></h3>
          <span class="service-chip__desc">Showing up first when someone nearby searches for exactly what you do.</span>
        </a>
        <a class="service-chip" href="/services/citation-management.php">
          <h3 class="service-chip__name">Citation Management<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M5 12h14M13 6l6 6-6 6"/></svg></h3>
          <span class="service-chip__desc">Your business name, address, and phone number, consistent everywhere online.</span>
        </a>
        <a class="service-chip" href="/services/business-listings-management.php">
          <h3 class="service-chip__name">Business Listings Management<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M5 12h14M13 6l6 6-6 6"/></svg></h3>
          <span class="service-chip__desc">Every directory kept accurate, so customers always find the real you.</span>
        </a>
      </div>
    </div>
  </section>

  <!-- C. REPUTATION, CONTENT & BRAND -->
  <section class="section" id="reputation">
    <div class="wrap">
      <div class="service-category__head reveal">
        <span class="eyebrow">Reputation, Content &amp; Brand</span>
        <h2>Look like the business people already trust.</h2>
        <p class="lead" style="margin-top:22px;">By the time someone visits your website, they've usually already checked your reviews. We build the reputation, content, and brand presence that make the decision easy.</p>
      </div>
      <div class="service-grid reveal">
        <a class="service-chip" href="/services/reputation-management.php">
          <h3 class="service-chip__name">Reputation Management<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M5 12h14M13 6l6 6-6 6"/></svg></h3>
          <span class="service-chip__desc">A steady, monitored reputation across every platform customers check before they call.</span>
        </a>
        <a class="service-chip" href="/services/review-generation.php">
          <h3 class="service-chip__name">Review Generation<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M5 12h14M13 6l6 6-6 6"/></svg></h3>
          <span class="service-chip__desc">A simple system that turns happy customers into 5-star reviews, automatically.</span>
        </a>
        <a class="service-chip" href="/services/social-media-management.php">
          <h3 class="service-chip__name">Social Media Management<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M5 12h14M13 6l6 6-6 6"/></svg></h3>
          <span class="service-chip__desc">A consistent presence that builds familiarity, without eating your entire week.</span>
        </a>
        <a class="service-chip" href="/services/content-creation.php">
          <h3 class="service-chip__name">Content Creation<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M5 12h14M13 6l6 6-6 6"/></svg></h3>
          <span class="service-chip__desc">Blog posts, captions, and copy that sound like you, written for you.</span>
        </a>
        <a class="service-chip" href="/services/graphic-design.php">
          <h3 class="service-chip__name">Graphic Design<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M5 12h14M13 6l6 6-6 6"/></svg></h3>
          <span class="service-chip__desc">Visuals that make your business look as established as the work you do.</span>
        </a>
        <a class="service-chip" href="/services/video-editing.php">
          <h3 class="service-chip__name">Video Editing<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M5 12h14M13 6l6 6-6 6"/></svg></h3>
          <span class="service-chip__desc">Raw footage turned into content people actually stop scrolling for.</span>
        </a>
        <a class="service-chip" href="/services/branding.php">
          <h3 class="service-chip__name">Branding<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M5 12h14M13 6l6 6-6 6"/></svg></h3>
          <span class="service-chip__desc">A look and voice your business can keep growing into for the next ten years.</span>
        </a>
      </div>

      <div class="stat-card is-solo reveal">
        <span class="stat-card__num">97%</span>
        <span class="stat-card__text">of consumers read reviews before choosing a local business, and 68% now expect at least 4 stars before they'll even consider you — up sharply from last year.</span>
        <span class="stat-card__source">Source: BrightLocal Local Consumer Review Survey, 2026</span>
      </div>
    </div>
  </section>

  <!-- D. ADVERTISING & LEAD GENERATION -->
  <section class="section" id="advertising" style="background:var(--paper-deep);">
    <div class="wrap">
      <div class="service-category__head reveal">
        <span class="eyebrow">Advertising &amp; Lead Generation</span>
        <h2>Ads that fill your calendar, not just your impression count.</h2>
        <p class="lead" style="margin-top:22px;">Paid advertising should produce customers, not just clicks. We build campaigns measured against booked jobs and closed sales — and built to keep improving.</p>
      </div>
      <div class="service-grid reveal">
        <a class="service-chip" href="/services/google-ads-management.php">
          <h3 class="service-chip__name">Google Ads Management<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M5 12h14M13 6l6 6-6 6"/></svg></h3>
          <span class="service-chip__desc">Budget spent on the searches that actually turn into paying customers.</span>
        </a>
        <a class="service-chip" href="/services/facebook-instagram-ads.php">
          <h3 class="service-chip__name">Facebook &amp; Instagram Ads<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M5 12h14M13 6l6 6-6 6"/></svg></h3>
          <span class="service-chip__desc">The right offer, in front of the right person, at the right moment.</span>
        </a>
        <a class="service-chip" href="/services/lead-generation.php">
          <h3 class="service-chip__name">Lead Generation<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M5 12h14M13 6l6 6-6 6"/></svg></h3>
          <span class="service-chip__desc">A steady, predictable flow of new leads — not a one-time spike.</span>
        </a>
        <a class="service-chip" href="/services/sales-funnels.php">
          <h3 class="service-chip__name">Sales Funnels<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M5 12h14M13 6l6 6-6 6"/></svg></h3>
          <span class="service-chip__desc">A clear path from click to customer, with nothing left to chance.</span>
        </a>
      </div>
    </div>
  </section>

  <!-- E. CRM, AUTOMATION & AI -->
  <section class="section" id="automation">
    <div class="wrap">
      <div class="service-category__head reveal">
        <span class="eyebrow">CRM, Automation &amp; AI</span>
        <h2>Never lose a lead to a slow follow-up again.</h2>
        <p class="lead" style="margin-top:22px;">Most jobs go to whoever responds first. We build the systems that capture, route, and follow up with every lead automatically — so no one falls through the cracks, even after hours.</p>
      </div>
      <div class="service-grid reveal">
        <a class="service-chip" href="/services/crm-development.php">
          <h3 class="service-chip__name">CRM Development<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M5 12h14M13 6l6 6-6 6"/></svg></h3>
          <span class="service-chip__desc">One place to see every customer, every lead, every conversation.</span>
        </a>
        <a class="service-chip" href="/services/crm-setup-customization.php">
          <h3 class="service-chip__name">CRM Setup &amp; Customization<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M5 12h14M13 6l6 6-6 6"/></svg></h3>
          <span class="service-chip__desc">A system built around how your team actually works, not the default settings.</span>
        </a>
        <a class="service-chip" href="/services/business-automation.php">
          <h3 class="service-chip__name">Business Automation<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M5 12h14M13 6l6 6-6 6"/></svg></h3>
          <span class="service-chip__desc">The repetitive work handled automatically, so your team can focus on the business.</span>
        </a>
        <a class="service-chip" href="/services/ai-automation.php">
          <h3 class="service-chip__name">AI Automation<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M5 12h14M13 6l6 6-6 6"/></svg></h3>
          <span class="service-chip__desc">AI handling the busywork behind the scenes — quietly, reliably, around the clock.</span>
        </a>
        <a class="service-chip" href="/services/ai-chatbots.php">
          <h3 class="service-chip__name">AI Chatbots<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M5 12h14M13 6l6 6-6 6"/></svg></h3>
          <span class="service-chip__desc">Instant answers for your customers, any hour, without adding to your workload.</span>
        </a>
        <a class="service-chip" href="/services/workflow-automation.php">
          <h3 class="service-chip__name">Workflow Automation<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M5 12h14M13 6l6 6-6 6"/></svg></h3>
          <span class="service-chip__desc">The handoffs between tools and teams, connected so nothing falls through.</span>
        </a>
        <a class="service-chip" href="/services/email-marketing.php">
          <h3 class="service-chip__name">Email Marketing<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M5 12h14M13 6l6 6-6 6"/></svg></h3>
          <span class="service-chip__desc">The right message reaching the right customer, automatically, at the right time.</span>
        </a>
        <a class="service-chip" href="/services/sms-marketing.php">
          <h3 class="service-chip__name">SMS Marketing<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M5 12h14M13 6l6 6-6 6"/></svg></h3>
          <span class="service-chip__desc">Text messages that actually get read — and get people back through your door.</span>
        </a>
      </div>

      <div class="stat-card is-solo reveal">
        <span class="stat-card__num">6 in 10</span>
        <span class="stat-card__text">The average small business misses roughly six in ten incoming calls — and most of those callers never try again. An AI-backed follow-up system closes that gap automatically.</span>
        <span class="stat-card__source">Source: small business call-tracking research, 2026</span>
      </div>
    </div>
  </section>

  <!-- F. SOFTWARE & ANALYTICS -->
  <section class="section section--deep" id="software">
    <div class="wrap">
      <div class="service-category__head reveal">
        <span class="eyebrow">Software &amp; Analytics</span>
        <h2>Know exactly what's working — and what to fix.</h2>
        <p class="lead" style="margin-top:22px; color:rgba(247,246,241,0.72);">Clear reporting tied to leads and revenue, plus the custom software to run your business your way when off-the-shelf tools fall short.</p>
      </div>
      <div class="service-grid reveal">
        <a class="service-chip" href="/services/analytics-reporting.php">
          <h3 class="service-chip__name">Analytics &amp; Reporting<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M5 12h14M13 6l6 6-6 6"/></svg></h3>
          <span class="service-chip__desc">Clear, plain-English reporting tied to leads and revenue, not vanity metrics.</span>
        </a>
        <a class="service-chip" href="/services/saas-solutions.php">
          <h3 class="service-chip__name">SaaS Solutions<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M5 12h14M13 6l6 6-6 6"/></svg></h3>
          <span class="service-chip__desc">Software built once, then sold as a product or run as your own operations.</span>
        </a>
        <a class="service-chip" href="/services/custom-software-development.php">
          <h3 class="service-chip__name">Custom Software Development<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M5 12h14M13 6l6 6-6 6"/></svg></h3>
          <span class="service-chip__desc">When off-the-shelf software doesn't fit, we build the tool that does.</span>
        </a>
      </div>
    </div>
  </section>

  <!-- WHY ONE PARTNER -->
  <section class="section">
    <div class="wrap">
      <div class="section-head reveal">
        <span class="eyebrow">The Alternative</span>
        <h2>Or you could keep juggling five different companies.</h2>
      </div>
      <div class="compare reveal">
        <div class="compare__col">
          <span class="compare__label">Hiring It Out Separately</span>
          <ul class="compare__list">
            <li><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M6 6l12 12M18 6L6 18"/></svg>A web developer who disappears the week after launch</li>
            <li><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M6 6l12 12M18 6L6 18"/></svg>An SEO freelancer who never mentions your ad account</li>
            <li><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M6 6l12 12M18 6L6 18"/></svg>A social media manager with no access to your CRM</li>
            <li><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M6 6l12 12M18 6L6 18"/></svg>A separate invoice, login, and excuse for every tool</li>
          </ul>
        </div>
        <div class="compare__col compare__col--brand">
          <span class="compare__label">One Chance To Grow</span>
          <ul class="compare__list">
            <li><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M5 12l5 5L20 6"/></svg>One team building your site, visibility, and systems together</li>
            <li><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M5 12l5 5L20 6"/></svg>Every service reporting into the same growth plan</li>
            <li><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M5 12l5 5L20 6"/></svg>One CRM, one dashboard, one number to call</li>
            <li><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M5 12l5 5L20 6"/></svg>Add or drop services as your business changes — no new vendor search</li>
          </ul>
        </div>
      </div>
    </div>
  </section>

  <!-- FAQ -->
  <section class="section" style="background:var(--paper-deep);">
    <div class="wrap">
      <div class="section-head reveal">
        <span class="eyebrow">Common Questions</span>
        <h2>What business owners usually ask first.</h2>
      </div>
      <div class="faq reveal">
        <details class="faq-item">
          <summary>Do I need all 33 of these services?</summary>
          <p>No. Most clients start with two or three — usually a website, local SEO, and one automation piece — and add more once those are working. We build the plan around your business, not our service list.</p>
        </details>
        <details class="faq-item">
          <summary>I already have a website or CRM. Can you just improve what I have?</summary>
          <p>Yes. We regularly step into existing websites, CRMs, and ad accounts to fix, optimize, or rebuild specific pieces rather than starting over from zero.</p>
        </details>
        <details class="faq-item">
          <summary>How fast can we get started?</summary>
          <p>Most engagements begin with a discovery call and a working plan within a week. Full builds, like a new website or CRM setup, typically take a few weeks depending on scope.</p>
        </details>
        <details class="faq-item">
          <summary>Do you only work with businesses in the United States?</summary>
          <p>We work with service and product businesses across the United States and Canada, from single-location local businesses to multi-location, growing companies.</p>
        </details>
        <details class="faq-item">
          <summary>What if I only need one service, not the whole system?</summary>
          <p>That's fine. Every service on this page can be hired on its own. Many clients start with one and add the rest once they see it working.</p>
        </details>
        <details class="faq-item">
          <summary>How do I know if it's actually working?</summary>
          <p>You'll get straightforward reporting tied to leads, calls, and revenue — not impressions or followers. If a number in your report doesn't tie back to your business, we don't include it.</p>
        </details>
      </div>
    </div>
  </section>

  <!-- FINAL CTA -->
  <section class="section final-cta">
    <div class="wrap">
      <span class="eyebrow center">Ready When You Are</span>
      <h2>Every business deserves <span class="accent-italic">one chance to grow.</span></h2>
      <p class="lead">Tell us where your business is today, and we'll show you exactly which of these pieces will move it forward first.</p>
      <div class="final-cta__ctas">
        <a href="tel:+18022768331" class="btn btn-primary">Call (802) 276-8331</a>
        <a href="/book-demo.php" class="btn btn-ghost">Book a Growth Call <svg class="btn-arrow" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M5 12h14M13 6l6 6-6 6"/></svg></a>
      </div>
    </div>
  </section>

<script type="application/ld+json">
{
  "@context": "https://schema.org",
  "@type": "BreadcrumbList",
  "itemListElement": [
    { "@type": "ListItem", "position": 1, "name": "Home", "item": "https://onechancetogrow.com/" },
    { "@type": "ListItem", "position": 2, "name": "Services", "item": "https://onechancetogrow.com/services.php" }
  ]
}
</script>
<script type="application/ld+json">
{
  "@context": "https://schema.org",
  "@type": "FAQPage",
  "mainEntity": [
    { "@type": "Question", "name": "Do I need all 33 of these services?", "acceptedAnswer": { "@type": "Answer", "text": "No. Most clients start with two or three — usually a website, local SEO, and one automation piece — and add more once those are working. We build the plan around your business, not our service list." } },
    { "@type": "Question", "name": "I already have a website or CRM. Can you just improve what I have?", "acceptedAnswer": { "@type": "Answer", "text": "Yes. We regularly step into existing websites, CRMs, and ad accounts to fix, optimize, or rebuild specific pieces rather than starting over from zero." } },
    { "@type": "Question", "name": "How fast can we get started?", "acceptedAnswer": { "@type": "Answer", "text": "Most engagements begin with a discovery call and a working plan within a week. Full builds, like a new website or CRM setup, typically take a few weeks depending on scope." } },
    { "@type": "Question", "name": "Do you only work with businesses in the United States?", "acceptedAnswer": { "@type": "Answer", "text": "We work with service and product businesses across the United States and Canada, from single-location local businesses to multi-location, growing companies." } },
    { "@type": "Question", "name": "What if I only need one service, not the whole system?", "acceptedAnswer": { "@type": "Answer", "text": "That's fine. Every service on this page can be hired on its own. Many clients start with one and add the rest once they see it working." } },
    { "@type": "Question", "name": "How do I know if it's actually working?", "acceptedAnswer": { "@type": "Answer", "text": "You'll get straightforward reporting tied to leads, calls, and revenue — not impressions or followers. If a number in your report doesn't tie back to your business, we don't include it." } }
  ]
}
</script>

<?php
include __DIR__ . '/includes/footer.php';
?>
