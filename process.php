<?php
/* ==========================================================================
   PROCESS.PHP — deeper walkthrough of the engagement process than the
   homepage's brief 4-step "How We Work" section.
   ========================================================================== */
$pageTitle       = 'Our Process — How an Engagement Actually Works | One Chance To Grow';
$pageDescription = "From the first call to ongoing optimization: exactly what happens at each stage of working with One Chance To Grow, and what we need from you along the way.";
$pageSlug        = 'process';
$activeNav       = '';
$bodyClass       = 'page-process';

$STAGES = [
  ['phase'=>'Stage 1','title'=>'Discovery Call','img'=>'timeline_process_1','body'=>"We start with a conversation, not a pitch. We ask about your business, your customers, and where growth actually feels stuck, usually 20 to 30 minutes."],
  ['phase'=>'Stage 2','title'=>'Audit & Strategy','img'=>'timeline_process_2','body'=>"We audit what's currently working and what isn't, across your website, visibility, reputation, and systems, then map a plan built around your business, not a fixed package."],
  ['phase'=>'Stage 3','title'=>'Proposal & Alignment','img'=>'timeline_process_3','body'=>"You'll see exactly what we're proposing, what it involves, and why, before anything is built. No scope surprises once work is underway."],
  ['phase'=>'Stage 4','title'=>'Build','img'=>'timeline_process_4','body'=>"Website, CRM, campaigns, automation, whatever's in the plan gets built and connected, with regular check-ins so nothing surprises you at launch."],
  ['phase'=>'Stage 5','title'=>'Launch & Connect','img'=>'timeline_process_5','body'=>"Everything goes live connected, tracking, reporting, and lead routing all working from day one, not bolted on after the fact."],
  ['phase'=>'Stage 6','title'=>'Manage & Optimize','img'=>'timeline_process_6','body'=>"We manage the system monthly: reporting, adjustments, and ongoing strategy, so growth compounds instead of resetting every quarter."],
  ['phase'=>'Stage 7','title'=>'Grow Together','img'=>'timeline_process_7','body'=>"As your business changes, the system adapts. Add services, adjust scope, or scale up, without starting over with a new vendor."],
];

include __DIR__ . '/includes/header.php';
?>

<nav class="breadcrumb" aria-label="Breadcrumb">
  <a href="/index.php">Home</a>
  <span class="sep">/</span>
  <span aria-current="page">Our Process</span>
</nav>

<section class="process-hero wrap">
  <span class="eyebrow center">Our Process</span>
  <h1 class="reveal-text">From First Call to Ongoing Growth</h1>
  <p class="lead">No black box. Here's exactly what happens at each stage, and what we need from you along the way.</p>
</section>

<section class="section" style="padding-top:20px;">
  <div class="wrap">
    <h2 class="sr-only">The Seven Stages</h2>
    <div class="timeline" id="processTimeline">
      <div class="timeline__fill"></div>
      <?php foreach ($STAGES as $s): ?>
      <div class="timeline-item">
        <div class="timeline-item__dot"></div>
        <div class="media-frame timeline-item__frame">
          <?php octg_media($s['img'], $s['title'] . ' Illustration'); ?>
        </div>
        <span class="timeline-item__year"><?php echo htmlspecialchars($s['phase']); ?></span>
        <h3><?php echo htmlspecialchars($s['title']); ?></h3>
        <p><?php echo htmlspecialchars($s['body']); ?></p>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<section class="section final-cta" style="background:var(--paper-deep);">
  <div class="wrap">
    <span class="eyebrow center">Ready for Stage 1?</span>
    <h2>Let's have that first conversation.</h2>
    <p class="lead">No pressure, no obligation, just a straight conversation about your business.</p>
    <div class="final-cta__ctas">
      <a href="/book-demo.php" class="btn btn-primary">Book a Growth Call</a>
      <a href="/services.php" class="btn btn-ghost">See What We Build</a>
    </div>
  </div>
</section>

<script type="application/ld+json">
{
  "@context": "https://schema.org",
  "@type": "BreadcrumbList",
  "itemListElement": [
    { "@type": "ListItem", "position": 1, "name": "Home", "item": "https://onechancetogrow.com/" },
    { "@type": "ListItem", "position": 2, "name": "Our Process", "item": "https://onechancetogrow.com/process.php" }
  ]
}
</script>
<script type="application/ld+json">
<?php
$howToSchema = [
    '@context' => 'https://schema.org',
    '@type' => 'HowTo',
    'name' => 'How an Engagement With One Chance To Grow Works',
    'step' => array_map(function ($s) {
        return ['@type' => 'HowToStep', 'name' => $s['title'], 'text' => $s['body']];
    }, $STAGES),
];
echo json_encode($howToSchema, JSON_UNESCAPED_SLASHES);
?>
</script>

<?php include __DIR__ . '/includes/footer.php'; ?>
