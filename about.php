<?php
/* ==========================================================================
   ABOUT.PHP — company story. Dynamically controlled via Page Builder.
   ========================================================================== */
require_once __DIR__ . '/api/_lib.php'; // Required for octg_get_json and octg_db

$pageTitle       = 'About One Chance To Grow — Marketing, CRM & Automation, Built as One';
$pageDescription = 'Why One Chance To Grow exists: businesses deserve one team behind their marketing, website, CRM, and automation, not five disconnected vendors.';
$pageSlug        = 'about';
$activeNav       = 'about';
$bodyClass       = 'page-about';
$headerTheme     = 'dark';

// Fetch Dynamic Content from Page Builder JSON
$hero = octg_get_json('about_hero_settings', [
    'eyebrow' => 'Leadership',
    'heading' => 'The Team Building Your Growth System',
    'description' => 'One team across marketing, software, and automation, accountable for how the whole system performs, not just their own piece of it.',
    'rotation_speed' => 5000,
    'auto_rotate' => 1,
    'particle_density' => 'medium',
    'transition_time' => 800,
    'animation_speed' => 1,
    'glow_intensity' => 0.55,
]);
$heroTransitionMs = max(200, (int)($hero['transition_time'] ?? 800));
$heroAnimSpeed = (float)($hero['animation_speed'] ?? 1) ?: 1;
$heroGlow = max(0, min(1, (float)($hero['glow_intensity'] ?? 0.55)));

$teamStats = octg_get_json('about_team_stats', [
    'members_value' => '15+', 'members_label' => 'Team Members',
    'projects_value' => '100+', 'projects_label' => 'Projects Delivered',
    'industries_value' => '8+', 'industries_label' => 'Industries Served',
    'years_value' => '5+', 'years_label' => 'Years of Experience',
]);

// Founder Hero — fully CMS-driven, edited from Admin > Team > Founder Hero.
$founderHero = octg_get_json('about_founder_hero', [
    'label' => 'Founder & CEO',
    'name' => 'Paul Raja',
    'title' => 'Founder & Chief Executive Officer',
    'description' => 'Building technology, automation and digital marketing systems that help businesses grow smarter, scale faster and compete with confidence.',
    'photo_id' => null,
    'linkedin' => '',
    'instagram' => '',
    'facebook' => '',
    'email' => '',
]);
$founderPhoto = '/assets/img/founder-hero.jpg'; // seed default until a CMS photo is chosen
if (!empty($founderHero['photo_id']) && is_numeric($founderHero['photo_id'])) {
    try {
        $pdoFh = octg_db();
        if ($pdoFh) {
            $stmtFh = $pdoFh->prepare('SELECT file_path FROM cms_media WHERE id = ?');
            $stmtFh->execute([$founderHero['photo_id']]);
            $path = $stmtFh->fetchColumn();
            if ($path) { $founderPhoto = $path; }
        }
    } catch (Throwable $e) { /* keep seed default */ }
}
$founderSocial = [];
foreach (['linkedin', 'instagram', 'facebook'] as $p) {
    if (!empty($founderHero[$p])) { $founderSocial[] = ['platform' => $p, 'url' => $founderHero[$p]]; }
}
if (!empty($founderHero['email'])) { $founderSocial[] = ['platform' => 'email', 'url' => 'mailto:' . $founderHero['email']]; }

$story = octg_get_json('about_story_settings', [
    'eyebrow' => 'Why We Exist',
    'heading' => '100 business problems. One brand. One solution.',
    'paragraph_1' => "Most businesses don't have a marketing problem, or a software problem, or an automation problem in isolation, they have a growth problem, and it's usually spread across all three. We built One Chance To Grow because that shouldn't take five separate companies to fix.",
    'paragraph_2' => "We're registered in Wyoming and work with businesses across the United States and Canada, from single-location local businesses to growing, multi-location companies. Different industries, different sizes, one consistent approach: understand the business first, then build the system around it.",
    'image_id' => ''
]);

$timelineDefaults = [
  ['phase' => 'The Problem', 'title' => 'Too many businesses, too many vendors', 'image_id' => 'timeline_about_1', 'body' => "We kept meeting business owners paying separate companies for pieces that never talked to each other, and getting fragmented results because of it."],
  ['phase' => 'The Idea', 'title' => 'One team, one system', 'image_id' => 'timeline_about_2', 'body' => "One Chance To Grow started with a simple premise: marketing, web, CRM, and automation should be built by the same team, from the same plan."],
  ['phase' => 'The Build', 'title' => 'AI and automation joined the stack', 'image_id' => 'timeline_about_3', 'body' => "As the tools matured, we built AI and automation into the system from day one, not bolted on later, because a growth partner should evolve as fast as the technology does."],
  ['phase' => 'Today', 'title' => 'A complete growth system, for any business', 'image_id' => 'timeline_about_4', 'body' => "Today, that system covers websites, visibility, reputation, advertising, automation, and software, built as one, for businesses across the US and Canada."],
];
$timeline = octg_get_json('about_timeline_data', $timelineDefaults);
// A saved-but-empty CMS value returns [] (not the default — see octg_get_json),
// and an empty "How We Got Here" is never intentional, so fall back to the
// original four milestones rather than rendering nothing.
if (empty($timeline)) { $timeline = $timelineDefaults; }

$values = octg_get_json('about_values_data', [
  ['icon_svg' => '<path d="M4 19V5M4 19h16M8 15l3-4 3 3 5-7"/>', 'title' => 'We measure ourselves by your growth', 'description' => "Not deliverables shipped, not hours billed. If your business isn't growing, the engagement isn't working."],
  ['icon_svg' => '<circle cx="12" cy="12" r="9"/><path d="M8 12l3 3 5-6"/>', 'title' => 'One team, start to finish', 'description' => "The people who plan your system are accountable for how it performs, not handed off after the sale."],
  ['icon_svg' => '<path d="M12 3l2.5 5.5L20 9l-4 4 1 6-5-3-5 3 1-6-4-4 5.5-.5z"/>', 'title' => 'Straight talk over sales talk', 'description' => "If something isn't working, you'll hear it from us before you notice it yourself."],
  ['icon_svg' => '<path d="M13 2L4 14h6l-1 8 9-12h-6l1-8z"/>', 'title' => 'Built to adapt as you grow', 'description' => "The system we build in month one still fits at ten times the size, not something you'll outgrow in a year."],
  ['icon_svg' => '<path d="M3 12h18M12 3a15 15 0 010 18 15 15 0 010-18Z"/>', 'title' => 'US and Canada, every industry', 'description' => "Local businesses to growing companies, service or product, we build around your business, not a fixed template."],
  ['icon_svg' => '<path d="M4 19l6-6 4 4 6-8"/>', 'title' => 'Systems over one-off campaigns', 'description' => "A campaign ends. A system keeps producing, whether or not anyone's actively watching it that week."]
]);

// Fetch Real Team Members from DB.
// team_members has no "status"/"short_bio"/"photo_url"/"social_links"
// columns (those never existed in sql/011_cms_architecture.sql) — the real
// columns are is_active, bio, and photo_id (an FK to cms_media, joined
// below); social links live in a separate team_socials table keyed by
// team_id, not a column on team_members at all. Wrapped defensively, like
// every other DB read on this page, so a transient DB issue degrades to an
// empty team section instead of a fatal error.
$TEAM = [];
try {
    $pdo = octg_db();
    if ($pdo) {
        $stmt = $pdo->query('
            SELECT t.*, m.file_path AS photo_path
            FROM team_members t
            LEFT JOIN cms_media m ON t.photo_id = m.id
            WHERE t.is_active = 1
            ORDER BY t.display_order ASC
        ');
        $teamRows = $stmt->fetchAll();

        $socialStmt = $pdo->prepare('SELECT platform, url FROM team_socials WHERE team_id = ? AND is_active = 1 ORDER BY display_order ASC');

        foreach ($teamRows as $r) {
            $socialStmt->execute([$r['id']]);
            $social = $socialStmt->fetchAll();
            if (!empty($r['email'])) {
                $social[] = ['platform' => 'email', 'url' => 'mailto:' . $r['email']];
            }
            $TEAM[] = [
                'name' => $r['full_name'],
                'title' => $r['position'],
                'bio' => $r['bio'],
                'photo_key' => $r['photo_path'] ?: '',
                'social' => $social,
            ];
        }
    }
} catch (Throwable $e) {
    $TEAM = [];
}

include __DIR__ . '/includes/header.php';
?>

<nav class="breadcrumb breadcrumb--on-dark" aria-label="Breadcrumb">
  <a href="/index.php">Home</a>
  <span class="sep">/</span>
  <span aria-current="page">About</span>
</nav>

<section class="founder-hero" id="founderHero">
  <div class="founder-hero__content">
    <span class="eyebrow founder-hero__eyebrow"><?php echo htmlspecialchars($founderHero['label']); ?></span>
    <h1 class="founder-hero__name"><?php
      $founderNameParts = explode(' ', trim($founderHero['name']));
      $founderNameLast = array_pop($founderNameParts);
      $founderNameFirst = trim(implode(' ', $founderNameParts));
      if ($founderNameFirst !== '') {
        echo '<span class="founder-hero__name-first">' . htmlspecialchars($founderNameFirst) . ' </span>';
      }
      echo '<span class="founder-hero__name-last">' . htmlspecialchars($founderNameLast) . '</span>';
    ?></h1>
    <span class="founder-hero__title"><?php echo htmlspecialchars($founderHero['title']); ?></span>
    <p class="founder-hero__desc"><?php echo htmlspecialchars($founderHero['description']); ?></p>
    <?php if (!empty($founderSocial)): ?>
    <div class="founder-hero__social">
      <?php foreach ($founderSocial as $s): echo octg_social_icon($s['platform'], $s['url']); endforeach; ?>
    </div>
    <?php endif; ?>
  </div>
  <div class="founder-hero__media">
    <img src="<?php echo htmlspecialchars($founderPhoto); ?>" alt="<?php echo htmlspecialchars($founderHero['name']); ?>, <?php echo htmlspecialchars($founderHero['title']); ?>" loading="eager" fetchpriority="high">
  </div>
  <div class="founder-hero__scroll-cue" aria-hidden="true">
    <span class="founder-hero__scroll-label"><i class="founder-hero__scroll-dot"></i>Scroll to meet the team</span>
    <span class="founder-hero__scroll-arrow">
      <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 5v14M5 12l7 7 7-7"/></svg>
    </span>
  </div>
</section>

<section class="team-hero" id="teamHero"
  data-speed="<?php echo (int)($hero['rotation_speed']); ?>"
  data-auto="<?php echo empty($hero['auto_rotate']) ? 'false' : 'true'; ?>"
  style="--octg-transition:<?php echo $heroTransitionMs; ?>ms; --octg-anim-speed:<?php echo $heroAnimSpeed; ?>; --octg-glow:<?php echo $heroGlow; ?>;">
  <canvas class="particle-field particle-<?php echo htmlspecialchars($hero['particle_density']??'medium'); ?>" id="teamHeroParticles" aria-hidden="true"></canvas>

  

  <div class="team-hero__content wrap">
    <span class="eyebrow center"><?php echo htmlspecialchars($hero['eyebrow'] ?? 'Leadership'); ?></span>
    <h2 class="reveal-text center"><?php echo htmlspecialchars($hero['heading'] ?? ''); ?></h2>
    <p class="lead center"><?php echo htmlspecialchars($hero['description'] ?? ''); ?></p>
  </div>

  <?php if (!empty($TEAM)): ?>
  <div class="team-carousel" id="teamCarousel">
    <h2 class="sr-only">Leadership Team</h2>
    <button type="button" class="team-carousel__arrow team-carousel__arrow--prev" id="teamPrev" aria-label="Previous team member">
      <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M15 6l-6 6 6 6"/></svg>
    </button>
    <button type="button" class="team-carousel__arrow team-carousel__arrow--next" id="teamNext" aria-label="Next team member">
      <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 6l6 6-6 6"/></svg>
    </button>
    <div class="team-carousel__stage" id="teamStage" style="--card-count:<?php echo count($TEAM); ?>;">
      <?php foreach ($TEAM as $i => $member): ?>
      <article class="team-card" data-index="<?php echo $i; ?>" role="group" aria-roledescription="slide" aria-label="<?php echo (int)$i + 1; ?> of <?php echo count($TEAM); ?>">
        <div class="team-card__frame">
          <img src="<?php echo htmlspecialchars($member['photo_key']); ?>" alt="<?php echo htmlspecialchars($member['name']); ?>" loading="<?php echo $i === 0 ? 'eager' : 'lazy'; ?>" decoding="async">
        </div>
        <h3 class="team-card__name"><?php echo htmlspecialchars($member['name']); ?></h3>
        <span class="team-card__title"><?php echo htmlspecialchars($member['title']); ?></span>
        <p class="team-card__bio"><?php echo htmlspecialchars($member['bio']); ?></p>
        <?php if (!empty($member['social'])): ?>
        <div class="team-card__social">
          <?php foreach ($member['social'] as $s): echo octg_social_icon($s['platform'], $s['url']); endforeach; ?>
        </div>
        <?php endif; ?>
      </article>
      <?php endforeach; ?>
    </div>

    <div class="team-carousel__dots" role="tablist" aria-label="Choose a team member">
      <?php foreach ($TEAM as $i => $member): ?>
      <button type="button" class="team-dot <?php echo $i === 0 ? 'is-active' : ''; ?>" data-index="<?php echo $i; ?>" role="tab" aria-selected="<?php echo $i === 0 ? 'true' : 'false'; ?>" aria-label="View <?php echo htmlspecialchars($member['name']); ?>, <?php echo htmlspecialchars($member['title']); ?>" tabindex="<?php echo $i === 0 ? '0' : '-1'; ?>"></button>
      <?php endforeach; ?>
    </div>
    <p class="sr-only" aria-live="polite" id="teamAnnounce"></p>
  </div>
  <?php endif; ?>

  <div class="team-stats">
    <div class="team-stats__item">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"><circle cx="9" cy="8" r="3"/><path d="M2 20c0-3.3 3.1-6 7-6s7 2.7 7 6"/><circle cx="17" cy="9" r="2.4"/><path d="M16 14.2c2.6.4 4.5 2.4 4.5 5.8"/></svg>
      <span class="team-stats__value"><?php echo htmlspecialchars($teamStats['members_value']); ?></span>
      <span class="team-stats__label"><?php echo htmlspecialchars($teamStats['members_label']); ?></span>
    </div>
    <div class="team-stats__item">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"><path d="M12 2c2.5 2.5 4 6 4 9.5S13 20 12 22c-1-2-4-6.5-4-10.5S9.5 4.5 12 2Z"/><circle cx="12" cy="10" r="2"/><path d="M8.5 15.5 5 19M15.5 15.5 19 19"/></svg>
      <span class="team-stats__value"><?php echo htmlspecialchars($teamStats['projects_value']); ?></span>
      <span class="team-stats__label"><?php echo htmlspecialchars($teamStats['projects_label']); ?></span>
    </div>
    <div class="team-stats__item">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="9"/><path d="M3 12h18M12 3a15 15 0 0 1 0 18 15 15 0 0 1 0-18Z"/></svg>
      <span class="team-stats__value"><?php echo htmlspecialchars($teamStats['industries_value']); ?></span>
      <span class="team-stats__label"><?php echo htmlspecialchars($teamStats['industries_label']); ?></span>
    </div>
    <div class="team-stats__item">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"><path d="M12 3l2.5 5.5L20 9l-4 4 1 6-5-3-5 3 1-6-4-4 5.5-.5z"/></svg>
      <span class="team-stats__value"><?php echo htmlspecialchars($teamStats['years_value']); ?></span>
      <span class="team-stats__label"><?php echo htmlspecialchars($teamStats['years_label']); ?></span>
    </div>
  </div>
</section>

<section class="section about-story">
  <div class="wrap about-story__grid">
    <div class="media-frame about-story__frame reveal">
      <?php 
        if(!empty($story['image_id']) && is_numeric($story['image_id'])) {
            $stmt = $pdo->prepare('SELECT file_path FROM cms_media WHERE id = ?');
            $stmt->execute([$story['image_id']]);
            $path = $stmt->fetchColumn();
            if($path) echo '<img src="'.htmlspecialchars($path).'" alt="Story" style="width:100%; height:100%; object-fit:cover;">';
        } else {
            octg_media('about_story_image', 'Our Team at Work'); 
        }
      ?>
    </div>
    <div class="about-story__copy reveal">
      <span class="eyebrow"><?php echo htmlspecialchars($story['eyebrow'] ?? ''); ?></span>
      <h2><?php echo htmlspecialchars($story['heading'] ?? ''); ?></h2>
      <p class="lead"><?php echo htmlspecialchars($story['paragraph_1'] ?? ''); ?></p>
      <p class="lead"><?php echo htmlspecialchars($story['paragraph_2'] ?? ''); ?></p>
    </div>
  </div>
</section>

<section class="section" style="background:var(--paper-deep);">
  <div class="wrap">
    <div class="section-head reveal">
      <span class="eyebrow">How We Got Here</span>
      <h2>The short version.</h2>
    </div>
    <div class="timeline" id="aboutTimeline">
      <div class="timeline__fill"></div>
      <?php foreach ($timeline as $t): ?>
      <div class="timeline-item">
        <div class="timeline-item__dot"></div>
        <div class="media-frame timeline-item__frame">
          <?php 
            if(!empty($t['image_id']) && is_numeric($t['image_id'])) {
                $stmt = $pdo->prepare('SELECT file_path FROM cms_media WHERE id = ?');
                $stmt->execute([$t['image_id']]);
                $path = $stmt->fetchColumn();
                if($path) echo '<img src="'.htmlspecialchars($path).'" alt="Timeline Image" style="width:100%; height:100%; object-fit:cover;">';
            } else {
                octg_media($t['image_id'] ?? '', $t['title'] . ' Illustration'); 
            }
          ?>
        </div>
        <span class="timeline-item__year"><?php echo htmlspecialchars($t['phase']); ?></span>
        <h3><?php echo htmlspecialchars($t['title']); ?></h3>
        <p><?php echo htmlspecialchars($t['body']); ?></p>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<section class="section">
  <div class="wrap">
    <div class="section-head reveal">
      <span class="eyebrow">What We Believe</span>
      <h2>The principles behind how we work.</h2>
    </div>
    <div class="why-grid reveal">
      <?php foreach($values as $v): ?>
      <div class="why-item">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round">
            <?php echo $v['icon_svg']; ?>
        </svg>
        <h3><?php echo htmlspecialchars($v['title'] ?? ''); ?></h3>
        <p><?php echo htmlspecialchars($v['description'] ?? ''); ?></p>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<section class="section final-cta">
  <div class="wrap">
    <span class="eyebrow center">Every Business Deserves One Chance To Grow</span>
    <h2>Let's find out what that looks like for yours.</h2>
    <p class="lead">Book a free growth call, no pressure, no obligation.</p>
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
    { "@type": "ListItem", "position": 2, "name": "About", "item": "https://onechancetogrow.com/about.php" }
  ]
}
</script>

<?php include __DIR__ . '/includes/footer.php'; ?>
