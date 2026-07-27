<?php
/* ==========================================================================
   INDEX.PHP — Homepage. Approved design; do not redesign.
   Page-specific styles live in index.css, page-specific script in index.js.
   Shared header/nav/footer/popup/modals come from includes/.
   ========================================================================== */
$pageTitle       = 'One Chance To Grow — Growth Systems for Serious Businesses';
$pageDescription = "One Chance To Grow builds the marketing, software, and automation that turn steady businesses into growing ones — as one connected partner.";
$pageSlug        = 'index';
$activeNav       = '';

/* Preload the hero image once a real one exists in the CMS — activates
   automatically, no further change needed when a photo is uploaded. */
$cmsImages = file_exists(__DIR__ . '/data/cms-images.php') ? require __DIR__ . '/data/cms-images.php' : [];
if (!empty($cmsImages['hero_image'])) {
    $preloadImage = $cmsImages['hero_image'];
}

include __DIR__ . '/includes/header.php';
?>

  <!-- HERO -->
  <section class="hero <?php echo $hasBgMedia ? 'hero--has-bg' : 'wrap'; ?>">
    <div class="<?php echo $hasBgMedia ? 'wrap hero__content-center' : 'hero__copy'; ?>">
      <div class="hero__eyebrow-row">
        <span class="eyebrow">Marketing &middot; Technology &middot; Automation &middot; AI</span>
      </div>
      <h1 class="reveal-text">Growth isn't a campaign <span class="accent-italic" style="display:block;">It's a system</span></h1>
      <p class="lead">One Chance To Grow builds the marketing, software, and automation that turn steady businesses into growing ones — as one connected partner, not five disconnected vendors.</p>
      <div class="hero__ctas">
        <a href="/book-demo.php" class="btn btn-primary">Book a Growth Call <svg class="btn-arrow" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M5 12h14M13 6l6 6-6 6"/></svg></a>
        <a href="#services" class="btn btn-ghost <?php echo $hasBgMedia ? 'btn-ghost-light' : ''; ?>">See What We Build</a>
      </div>
      <div class="hero__tagline">
        <span><b>Marketing.</b></span>
        <span class="dot">&middot;</span>
        <span><b>Strategy.</b></span>
        <span class="dot">&middot;</span>
        <span><b>Automation.</b></span>
        <span class="dot">&middot;</span>
        <span><b>Results.</b></span>
      </div>
    </div>

    <?php if (!$hasBgMedia): ?>
    <div class="hero__visual">
      <div class="media-frame hero__frame">
        <div class="media-placeholder">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><rect x="3" y="3" width="18" height="18" rx="2" ry="2"/><circle cx="8.5" cy="8.5" r="1.5"/><polyline points="21 15 16 10 5 21"/></svg>
          <span class="media-placeholder__label">Hero <b>Media</b></span>
        </div>
      </div>
    </div>
    <?php endif; ?>
  </section>

  <!-- TRUST BAR -->
  <section class="trust-bar">
    <div class="wrap" style="padding-bottom: 24px;">
      <span class="trust-bar__label">Trusted By Businesses Across the US &amp; Canada</span>
    </div>
    <div class="marquee trust-bar__marquee" style="border:none; padding:10px 0;">
      <div class="marquee__track" style="gap:20px;">
        <?php
        $logosHtml = '';
        $clientLogos = octg_get_json('client_logos', []);

        // Fallback for legacy data before JSON migration
        if (empty($clientLogos)) {
            for ($i = 1; $i <= 5; $i++) {
                $old = octg_get_media_data('client_logo_' . $i);
                if ($old) $clientLogos[] = $old['id'];
            }
        }

        // Randomized order per page load so the row doesn't read identically
        // on every visit — the two duplicate copies below share this same
        // shuffled order, which is required for the CSS scroll loop to stay
        // seamless (both halves of the track must match).
        shuffle($clientLogos);

        foreach ($clientLogos as $logoId) {
            $media = octg_get_media_data_by_id($logoId);
            if ($media) {
                $logosHtml .= '<div class="trust-bar__logo-slide"><img src="' . htmlspecialchars($media['file_path']) . '" alt="' . htmlspecialchars($media['alt_text'] ?: 'Client Logo') . '" class="trust-bar__img"></div>';
            }
        }

        // If no logos, show placeholders
        if (!$logosHtml) {
            for ($i=0; $i<5; $i++) {
                $logosHtml .= '<div class="trust-bar__logo-slide"><div class="media-placeholder" style="position:relative; width:140px; height:40px;"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" style="width:20px;height:20px;color:var(--graphite);opacity:0.5;"><rect x="3" y="3" width="18" height="18" rx="2" ry="2"/><circle cx="8.5" cy="8.5" r="1.5"/><polyline points="21 15 16 10 5 21"/></svg></div></div>';
            }
        }
        // Exactly 2 copies — the minimum needed for the CSS translateX(-50%)
        // loop (animations.css) to reset seamlessly, without padding the DOM
        // with redundant extra copies.
        echo $logosHtml . $logosHtml;
        ?>
      </div>
    </div>
  </section>

  <!-- POSITIONING -->
  <section class="section" id="approach">
    <div class="wrap">
      <div class="section-head reveal">
        <span class="eyebrow">Why We're Different</span>
        <h2>Most agencies hand you a piece. We build the whole system.</h2>
        <p class="lead" style="margin-top:22px;">A marketing agency for ads. A freelancer for the website. A CRM nobody set up right. Separate invoices, separate excuses, and no one accountable for whether your business actually grows.</p>
      </div>

      <div class="compare reveal">
        <div class="compare__col">
          <span class="compare__label">The Old Way</span>
          <ul class="compare__list">
            <li><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M6 6l12 12M18 6L6 18"/></svg>A different vendor for every service, none of them talking to each other</li>
            <li><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M6 6l12 12M18 6L6 18"/></svg>Marketing that drives leads your team has no system to catch</li>
            <li><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M6 6l12 12M18 6L6 18"/></svg>Reports full of activity, light on business results</li>
            <li><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M6 6l12 12M18 6L6 18"/></svg>A website that looks fine and does nothing on its own</li>
          </ul>
        </div>
        <div class="compare__col compare__col--brand">
          <span class="compare__label">The One Chance To Grow Way</span>
          <ul class="compare__list">
            <li><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M5 12l5 5L20 6"/></svg>One team building marketing, software, and automation from the same plan</li>
            <li><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M5 12l5 5L20 6"/></svg>Every lead captured, routed, and followed up automatically</li>
            <li><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M5 12l5 5L20 6"/></svg>Measured against pipeline and revenue, not vanity metrics</li>
            <li><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M5 12l5 5L20 6"/></svg>A site, CRM, and AI layer built to work together from day one</li>
          </ul>
        </div>
      </div>
    </div>
  </section>

  <!-- BY THE NUMBERS -->
  <?php $homeStats = octg_get_json('about_team_stats', [
      'members_value' => '15+', 'members_label' => 'Team Members',
      'projects_value' => '100+', 'projects_label' => 'Projects Delivered',
      'industries_value' => '8+', 'industries_label' => 'Industries Served',
      'years_value' => '5+', 'years_label' => 'Years of Experience',
  ]); ?>
  <section class="section" style="padding-top:0;">
    <div class="wrap">
      <div class="home-stats reveal">
        <div class="home-stats__item">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"><circle cx="9" cy="8" r="3"/><path d="M2 20c0-3.3 3.1-6 7-6s7 2.7 7 6"/><circle cx="17" cy="9" r="2.4"/><path d="M16 14.2c2.6.4 4.5 2.4 4.5 5.8"/></svg>
          <span class="home-stats__value"><?php echo htmlspecialchars($homeStats['members_value']); ?></span>
          <span class="home-stats__label"><?php echo htmlspecialchars($homeStats['members_label']); ?></span>
        </div>
        <div class="home-stats__item">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"><path d="M12 2c2.5 2.5 4 6 4 9.5S13 20 12 22c-1-2-4-6.5-4-10.5S9.5 4.5 12 2Z"/><circle cx="12" cy="10" r="2"/><path d="M8.5 15.5 5 19M15.5 15.5 19 19"/></svg>
          <span class="home-stats__value"><?php echo htmlspecialchars($homeStats['projects_value']); ?></span>
          <span class="home-stats__label"><?php echo htmlspecialchars($homeStats['projects_label']); ?></span>
        </div>
        <div class="home-stats__item">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="9"/><path d="M3 12h18M12 3a15 15 0 0 1 0 18 15 15 0 0 1 0-18Z"/></svg>
          <span class="home-stats__value"><?php echo htmlspecialchars($homeStats['industries_value']); ?></span>
          <span class="home-stats__label"><?php echo htmlspecialchars($homeStats['industries_label']); ?></span>
        </div>
        <div class="home-stats__item">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"><path d="M12 3l2.5 5.5L20 9l-4 4 1 6-5-3-5 3 1-6-4-4 5.5-.5z"/></svg>
          <span class="home-stats__value"><?php echo htmlspecialchars($homeStats['years_value']); ?></span>
          <span class="home-stats__label"><?php echo htmlspecialchars($homeStats['years_label']); ?></span>
        </div>
      </div>
    </div>
  </section>

  <!-- SERVICES -->
  <section class="section" id="services" style="background:var(--paper-deep);">
    <div class="wrap">
      <div class="section-head reveal">
        <span class="eyebrow">What We Build</span>
        <h2>Three disciplines. One growth system.</h2>
        <p class="lead" style="margin-top:22px;">Every engagement starts with strategy, then moves through whichever of these your business needs — separately or together.</p>
      </div>

      <div class="pillars reveal">
        <article class="pillar">
          <svg class="pillar__icon" viewBox="0 0 48 48" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round">
            <path d="M6 38 18 26 26 32 42 12"/>
            <path d="M42 12c0 0-4-4-8-2 2 4 6 5 8 2Z" fill="currentColor" stroke="none"/>
          </svg>
          <h3>Growth Marketing</h3>
          <p>Demand that compounds, not campaigns that reset every month.</p>
          <ul class="pillar__list">
            <li><a href="/services/seo.php">SEO</a> &amp; <a href="/services/google-business-profile-optimization.php">Google Business Profile</a></li>
            <li><a href="/services/google-ads-management.php">Google Ads</a> &amp; <a href="/services/facebook-instagram-ads.php">Meta Advertising</a></li>
            <li><a href="/services/social-media-management.php">Social Media Marketing</a></li>
            <li><a href="/services/lead-generation.php">Lead Generation</a> &amp; Strategy</li>
          </ul>
        </article>
        <article class="pillar">
          <svg class="pillar__icon" viewBox="0 0 48 48" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round">
            <rect x="6" y="9" width="36" height="30" rx="1"/>
            <line x1="6" y1="17" x2="42" y2="17"/>
            <path d="M18 24l-4 4 4 4M26 24l4 4-4 4"/>
          </svg>
          <h3>Web &amp; Software</h3>
          <p>A site and a CRM built as one system, not two separate purchases.</p>
          <ul class="pillar__list">
            <li><a href="/services/website-development.php">Website Design &amp; Development</a></li>
            <li><a href="/services/crm-development.php">CRM Software</a></li>
            <li><a href="/services/custom-software-development.php">Custom Growth Solutions</a></li>
          </ul>
        </article>
        <article class="pillar">
          <svg class="pillar__icon" viewBox="0 0 48 48" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round">
            <circle cx="20" cy="26" r="9"/>
            <path d="M20 13v4M20 35v4M7 26h4M29 26h4M11.5 17.5l2.8 2.8M25.7 31.7l2.8 2.8M11.5 34.5l2.8-2.8M25.7 20.3l2.8-2.8"/>
            <path d="M34 12l1.6 3.4L39 17l-3.4 1.6L34 22l-1.6-3.4L29 17l3.4-1.6Z" fill="currentColor" stroke="none"/>
          </svg>
          <h3>Automation &amp; AI</h3>
          <p>The quiet layer that answers, follows up, and never forgets a lead.</p>
          <ul class="pillar__list">
            <li><a href="/products.php#ai-receptionist">AI Receptionist</a></li>
            <li><a href="/services/business-automation.php">Business &amp; Marketing Automation</a></li>
            <li><a href="/services/crm-setup-customization.php">Lead Management</a></li>
            <li><a href="/services/review-generation.php">Review Management</a></li>
          </ul>
        </article>
      </div>
    </div>
  </section>

  <!-- PROCESS -->
  <section class="section section--deep">
    <div class="wrap">
      <div class="section-head reveal">
        <span class="eyebrow">How We Work</span>
        <h2>A system doesn't start with tactics. It starts with a plan.</h2>
      </div>
      <div class="process reveal">
        <div class="step">
          <div class="step__num">01</div>
          <h3>Discover</h3>
          <p>We study your business, your customers, and exactly where growth is stuck.</p>
        </div>
        <div class="step">
          <div class="step__num">02</div>
          <h3>Strategy</h3>
          <p>We map the marketing, technology, and automation your business actually needs.</p>
        </div>
        <div class="step">
          <div class="step__num">03</div>
          <h3>Build</h3>
          <p>We design and connect every piece — website, campaigns, CRM, automation, AI.</p>
        </div>
        <div class="step">
          <div class="step__num">04</div>
          <h3>Grow</h3>
          <p>We manage and optimize the system monthly, so growth compounds instead of resetting.</p>
        </div>
      </div>
    </div>
  </section>

  <!-- WHY US -->
  <section class="section" id="why-us">
    <div class="wrap">
      <div class="section-head reveal">
        <span class="eyebrow">Who We Are</span>
        <h2>Built for businesses that are done guessing.</h2>
        <p class="lead" style="margin-top:22px;">We're not a marketing agency that dabbles in software, or a dev shop that dabbles in ads. We're one team built around a single outcome: your business growing.</p>
      </div>
      <div class="why-grid reveal">
        <div class="why-item">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="9"/><path d="M8 12l3 3 5-6"/></svg>
          <h3>One partner, not five</h3>
          <p>A single accountable team across marketing, software, and automation.</p>
        </div>
        <div class="why-item">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"><path d="M4 19V5M4 19h16M8 15l3-4 3 3 5-7"/></svg>
          <h3>Systems built on your data</h3>
          <p>No recycled templates — every build starts with how your customers actually behave.</p>
        </div>
        <div class="why-item">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="7" width="18" height="13" rx="1"/><path d="M8 7V5a2 2 0 012-2h4a2 2 0 012 2v2"/></svg>
          <h3>Service and product businesses</h3>
          <p>From local service companies to established product brands — the system adapts to you.</p>
        </div>
        <div class="why-item">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"><path d="M12 3l2.5 5.5L20 9l-4 4 1 6-5-3-5 3 1-6-4-4 5.5-.5z"/></svg>
          <h3>Senior strategy, not just execution</h3>
          <p>Every account is guided by people who understand business, not just channels.</p>
        </div>
        <div class="why-item">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"><path d="M13 2L4 14h6l-1 8 9-12h-6l1-8z"/></svg>
          <h3>Built to move fast</h3>
          <p>Automation and AI mean your systems respond in minutes, not weeks.</p>
        </div>
        <div class="why-item">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"><path d="M3 17l6-6 4 4 8-8M21 7v6"/></svg>
          <h3>Built to scale with you</h3>
          <p>The system we build in month one is the same one that supports you at ten times the size.</p>
        </div>
      </div>
    </div>
  </section>

  <!-- TESTIMONIALS -->
  <section class="section" style="background:var(--paper-deep);">
    <div class="wrap">
      <div class="section-head center reveal" style="margin-inline:auto;">
        <span class="eyebrow center">Results, In Their Words</span>
        <h2>Trusted by businesses that expect more.</h2>
      </div>
      <div class="quote-row reveal">
        <article class="quote-card">
          <svg class="mark" viewBox="0 0 32 24" fill="currentColor"><path d="M0 24V13.6C0 6 4.8 1 12.8 0l1.6 4.4C9.6 5.6 7.2 8 7.2 12h6.4V24H0zm18 0V13.6C18 6 22.8 1 30.8 0l1.6 4.4c-4.8 1.2-7.2 3.6-7.2 7.6h6.4V24H18z"/></svg>
          <p class="q"><?php echo htmlspecialchars(octg_get_content('testimonial_1_quote', 'One Chance To Grow rebuilt our lead process end to end — we stopped losing calls and started closing them.')); ?></p>
          <div class="quote-card__who">
            <div class="media-frame is-round">
              <?php octg_media('testimonial_1_photo', 'Client Photo', '', true); ?>
            </div>
            <div><strong data-cms-key="testimonial_1_name"><?php echo htmlspecialchars(octg_get_content('testimonial_1_name', 'Client Name')); ?></strong><span data-cms-key="testimonial_1_role"><?php echo htmlspecialchars(octg_get_content('testimonial_1_role', 'Owner, Home Services Co.')); ?></span></div>
          </div>
        </article>
        <article class="quote-card">
          <svg class="mark" viewBox="0 0 32 24" fill="currentColor"><path d="M0 24V13.6C0 6 4.8 1 12.8 0l1.6 4.4C9.6 5.6 7.2 8 7.2 12h6.4V24H0zm18 0V13.6C18 6 22.8 1 30.8 0l1.6 4.4c-4.8 1.2-7.2 3.6-7.2 7.6h6.4V24H18z"/></svg>
          <p class="q"><?php echo htmlspecialchars(octg_get_content('testimonial_2_quote', 'The first agency we\'ve used that actually understood our CRM better than we did.')); ?></p>
          <div class="quote-card__who">
            <div class="media-frame is-round">
              <?php octg_media('testimonial_2_photo', 'Client Photo', '', true); ?>
            </div>
            <div><strong data-cms-key="testimonial_2_name"><?php echo htmlspecialchars(octg_get_content('testimonial_2_name', 'Client Name')); ?></strong><span data-cms-key="testimonial_2_role"><?php echo htmlspecialchars(octg_get_content('testimonial_2_role', 'Founder, Retail Brand')); ?></span></div>
          </div>
        </article>
        <article class="quote-card">
          <svg class="mark" viewBox="0 0 32 24" fill="currentColor"><path d="M0 24V13.6C0 6 4.8 1 12.8 0l1.6 4.4C9.6 5.6 7.2 8 7.2 12h6.4V24H0zm18 0V13.6C18 6 22.8 1 30.8 0l1.6 4.4c-4.8 1.2-7.2 3.6-7.2 7.6h6.4V24H18z"/></svg>
          <p class="q"><?php echo htmlspecialchars(octg_get_content('testimonial_3_quote', 'Our AI receptionist alone paid for the whole engagement in the first month.')); ?></p>
          <div class="quote-card__who">
            <div class="media-frame is-round">
              <?php octg_media('testimonial_3_photo', 'Client Photo', '', true); ?>
            </div>
            <div><strong data-cms-key="testimonial_3_name"><?php echo htmlspecialchars(octg_get_content('testimonial_3_name', 'Client Name')); ?></strong><span data-cms-key="testimonial_3_role"><?php echo htmlspecialchars(octg_get_content('testimonial_3_role', 'Managing Partner, Professional Services')); ?></span></div>
          </div>
        </article>
      </div>
    </div>
  </section>

  <!-- CASE STUDIES TEASER -->
  <?php
  $homeCaseStudies = file_exists(__DIR__ . '/data/case-studies-catalog.php') ? require __DIR__ . '/data/case-studies-catalog.php' : [];
  ?>
  <?php if (!empty($homeCaseStudies)): ?>
  <section class="section" id="case-studies">
    <div class="wrap">
      <div class="section-head reveal">
        <span class="eyebrow">Proof, Not Promises</span>
        <h2>What the system looks like in practice.</h2>
        <p class="lead" style="margin-top:22px;">A closer look at the work behind the results above.</p>
      </div>
      <div class="project-grid reveal">
        <?php foreach ($homeCaseStudies as $cs): ?>
        <article class="project-card reveal">
          <div class="media-frame project-card__frame">
            <?php octg_media($cs['hero_key'], $cs['title'] . ' Preview'); ?>
          </div>
          <span class="project-card__industry"><?php echo htmlspecialchars($cs['industry']); ?></span>
          <h3><?php echo htmlspecialchars($cs['title']); ?></h3>
          <p><?php echo htmlspecialchars($cs['quote']); ?></p>
          <a href="/projects/<?php echo htmlspecialchars($cs['slug']); ?>.php" class="project-card__link project-card__link--primary">
            Read the Full Case Study
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M5 12h14M13 6l6 6-6 6"/></svg>
          </a>
        </article>
        <?php endforeach; ?>
      </div>
      <div style="text-align:center; margin-top:36px;">
        <a href="/projects.php" class="btn btn-ghost">See All Projects</a>
      </div>
    </div>
  </section>
  <?php endif; ?>

  <!-- INDUSTRIES -->
  <section class="section" id="industries" style="padding-bottom:0;">
    <div class="wrap">
      <div class="section-head reveal">
        <span class="eyebrow">Who We Work With</span>
        <h2>Local shops. Growing companies. Every kind of business.</h2>
      </div>
    </div>
    <div class="marquee reveal">
      <div class="marquee__track">
        <span>Home Services</span><span>Professional Services</span><span>Health &amp; Wellness</span><span>Retail &amp; E-Commerce</span><span>Real Estate</span><span>Hospitality</span><span>B2B &amp; SaaS</span><span>Contractors &amp; Trades</span>
        <span>Home Services</span><span>Professional Services</span><span>Health &amp; Wellness</span><span>Retail &amp; E-Commerce</span><span>Real Estate</span><span>Hospitality</span><span>B2B &amp; SaaS</span><span>Contractors &amp; Trades</span>
      </div>
    </div>
    <div class="wrap" style="text-align:center; padding-top:36px;">
      <a href="/industries.php" class="btn btn-ghost">See How We Work With Your Industry</a>
    </div>
  </section>

  <!-- FINAL CTA -->
  <section class="section final-cta" id="contact">
    <div class="wrap">
      <span class="eyebrow center">Let's Talk</span>
      <h2>You only get one chance to grow. <span class="accent-italic">Let's make sure it works.</span></h2>
      <p class="lead">Book a free growth call and we'll show you exactly where your business is leaving growth on the table.</p>
      <div class="final-cta__ctas">
        <a href="tel:+18022768331" class="btn btn-primary">Call (802) 276-8331</a>
        <a href="/book-demo.php" class="btn btn-ghost">Book a Growth Call <svg class="btn-arrow" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M5 12h14M13 6l6 6-6 6"/></svg></a>
      </div>
    </div>
  </section>

<script type="application/ld+json">
{
  "@context": "https://schema.org",
  "@type": "WebSite",
  "name": "One Chance To Grow",
  "url": "https://onechancetogrow.com/"
}
</script>

<?php
include __DIR__ . '/includes/footer.php';
?>
