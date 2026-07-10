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
    'particle_density' => 'medium'
]);

$story = octg_get_json('about_story_settings', [
    'eyebrow' => 'Why We Exist',
    'heading' => '100 business problems. One brand. One solution.',
    'paragraph_1' => "Most businesses don't have a marketing problem, or a software problem, or an automation problem in isolation, they have a growth problem, and it's usually spread across all three. We built One Chance To Grow because that shouldn't take five separate companies to fix.",
    'paragraph_2' => "We're registered in Wyoming and work with businesses across the United States and Canada, from single-location local businesses to growing, multi-location companies. Different industries, different sizes, one consistent approach: understand the business first, then build the system around it.",
    'image_id' => ''
]);

$timeline = octg_get_json('about_timeline_data', [
  ['phase' => 'The Problem', 'title' => 'Too many businesses, too many vendors', 'image_id' => 'timeline_about_1', 'body' => "We kept meeting business owners paying separate companies for pieces that never talked to each other, and getting fragmented results because of it."],
  ['phase' => 'The Idea', 'title' => 'One team, one system', 'image_id' => 'timeline_about_2', 'body' => "One Chance To Grow started with a simple premise: marketing, web, CRM, and automation should be built by the same team, from the same plan."],
  ['phase' => 'The Build', 'title' => 'AI and automation joined the stack', 'image_id' => 'timeline_about_3', 'body' => "As the tools matured, we built AI and automation into the system from day one, not bolted on later, because a growth partner should evolve as fast as the technology does."],
  ['phase' => 'Today', 'title' => 'A complete growth system, for any business', 'image_id' => 'timeline_about_4', 'body' => "Today, that system covers websites, visibility, reputation, advertising, automation, and software, built as one, for businesses across the US and Canada."],
]);

$values = octg_get_json('about_values_data', [
  ['icon_svg' => '<path d="M4 19V5M4 19h16M8 15l3-4 3 3 5-7"/>', 'title' => 'We measure ourselves by your growth', 'description' => "Not deliverables shipped, not hours billed. If your business isn't growing, the engagement isn't working."],
  ['icon_svg' => '<circle cx="12" cy="12" r="9"/><path d="M8 12l3 3 5-6"/>', 'title' => 'One team, start to finish', 'description' => "The people who plan your system are accountable for how it performs, not handed off after the sale."],
  ['icon_svg' => '<path d="M12 3l2.5 5.5L20 9l-4 4 1 6-5-3-5 3 1-6-4-4 5.5-.5z"/>', 'title' => 'Straight talk over sales talk', 'description' => "If something isn't working, you'll hear it from us before you notice it yourself."],
  ['icon_svg' => '<path d="M13 2L4 14h6l-1 8 9-12h-6l1-8z"/>', 'title' => 'Built to adapt as you grow', 'description' => "The system we build in month one still fits at ten times the size, not something you'll outgrow in a year."],
  ['icon_svg' => '<path d="M3 12h18M12 3a15 15 0 010 18 15 15 0 010-18Z"/>', 'title' => 'US and Canada, every industry', 'description' => "Local businesses to growing companies, service or product, we build around your business, not a fixed template."],
  ['icon_svg' => '<path d="M4 19l6-6 4 4 6-8"/>', 'title' => 'Systems over one-off campaigns', 'description' => "A campaign ends. A system keeps producing, whether or not anyone's actively watching it that week."]
]);

// Fetch Real Team Members from DB
$pdo = octg_db();
$stmt = $pdo->query('SELECT * FROM team_members WHERE status="active" ORDER BY display_order ASC');
$teamRows = $stmt->fetchAll();

// Map real DB rows to the frontend array format expected
$TEAM = [];
foreach($teamRows as $r) {
    // Decode social links
    $socialJson = json_decode($r['social_links'] ?? '[]', true) ?: [];
    $TEAM[] = [
        'name' => $r['full_name'],
        'title' => $r['position'],
        'bio' => $r['short_bio'],
        'photo_key' => $r['photo_url'] ?: 'placeholder_team', // We'll just pass the raw URL to octg_media to handle, or use a key if applicable. Wait, octg_media expects a key. If it's a raw url, it needs adjusting, but for now we'll pass the ID or key
        'social' => $socialJson
    ];
}

include __DIR__ . '/includes/header.php';
?>

<nav class="breadcrumb breadcrumb--on-dark" aria-label="Breadcrumb">
  <a href="/index.php">Home</a>
  <span class="sep">/</span>
  <span aria-current="page">About</span>
</nav>

<section class="team-hero" id="teamHero" data-speed="<?php echo (int)($hero['rotation_speed']); ?>" data-auto="<?php echo empty($hero['auto_rotate']) ? 'false' : 'true'; ?>">
  <canvas class="particle-field particle-<?php echo htmlspecialchars($hero['particle_density']??'medium'); ?>" id="teamHeroParticles" aria-hidden="true"></canvas>

  <div class="globe-scene" aria-hidden="true">
    <div class="globe" id="teamGlobe">
      <div class="globe__ring globe__ring--lon" style="transform:rotateY(0deg)"></div>
      <div class="globe__ring globe__ring--lon" style="transform:rotateY(30deg)"></div>
      <div class="globe__ring globe__ring--lon" style="transform:rotateY(60deg)"></div>
      <div class="globe__ring globe__ring--lon" style="transform:rotateY(90deg)"></div>
      <div class="globe__ring globe__ring--lon" style="transform:rotateY(120deg)"></div>
      <div class="globe__ring globe__ring--lon" style="transform:rotateY(150deg)"></div>
      <div class="globe__ring globe__ring--lat" style="transform:rotateX(90deg) scale(0.94)"></div>
      <div class="globe__ring globe__ring--lat" style="transform:rotateX(90deg) scale(0.72)"></div>
      <div class="globe__ring globe__ring--lat" style="transform:rotateX(90deg) scale(0.4)"></div>
    </div>
  </div>

  <div class="team-hero__content wrap">
    <span class="eyebrow center"><?php echo htmlspecialchars($hero['eyebrow'] ?? 'Leadership'); ?></span>
    <h1 class="reveal-text center"><?php echo htmlspecialchars($hero['heading'] ?? ''); ?></h1>
    <p class="lead center"><?php echo htmlspecialchars($hero['description'] ?? ''); ?></p>
  </div>

  <div class="team-carousel" id="teamCarousel">
    <h2 class="sr-only">Leadership Team</h2>
    <div class="team-carousel__stage" id="teamStage" style="--card-count:<?php echo count($TEAM); ?>;">
      <?php foreach ($TEAM as $i => $member): ?>
      <article class="team-card" data-index="<?php echo $i; ?>">
        <div class="media-frame is-round team-card__frame">
          <!-- Temporary simple image tag to support direct URL injection from new CRM DB -->
          <img src="<?php echo htmlspecialchars($member['photo_key']); ?>" alt="<?php echo htmlspecialchars($member['name'] . ' Photo'); ?>" style="width:100%; height:100%; object-fit:cover;">
        </div>
        <h3 class="team-card__name"><?php echo htmlspecialchars($member['name']); ?></h3>
        <span class="team-card__title"><?php echo htmlspecialchars($member['title']); ?></span>
        <p class="team-card__bio"><?php echo htmlspecialchars($member['bio']); ?></p>
        <div class="team-card__social">
          <?php foreach ($member['social'] as $s): echo octg_social_icon($s['platform'], $s['url']); endforeach; ?>
        </div>
      </article>
      <?php endforeach; ?>
    </div>

    <div class="team-carousel__dots" role="tablist" aria-label="Choose a team member">
      <?php foreach ($TEAM as $i => $member): ?>
      <button type="button" class="team-dot <?php echo $i === 0 ? 'is-active' : ''; ?>" data-index="<?php echo $i; ?>" aria-label="View <?php echo htmlspecialchars($member['name']); ?>, <?php echo htmlspecialchars($member['title']); ?>"></button>
      <?php endforeach; ?>
    </div>
    <p class="sr-only" aria-live="polite" id="teamAnnounce"></p>
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
