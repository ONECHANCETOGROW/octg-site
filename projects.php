<?php
/* ==========================================================================
   PROJECTS.PHP — portfolio / project highlights.
   3 of the 6 projects have a full case study (Challenge/Solution/Results/
   Timeline/Gallery) under /projects/[slug].php; those cards get a primary
   "Read the Full Case Study" link. The other 3 still link to their most
   relevant service page until there's real material to expand them with.
   ========================================================================== */
$pageTitle       = 'Projects — Real Results Across Industries | One Chance To Grow';
$pageDescription = 'A look at the systems One Chance To Grow has built — lead follow-up, reputation, CRM, and advertising work across home services, healthcare, real estate, and more.';
$pageSlug        = 'projects';
$activeNav       = '';
$bodyClass       = 'page-projects';

$PROJECTS = [
  ['img'=>'project_1_image','industry'=>'Home Services','title'=>'A Lead System That Stopped Losing Calls','result'=>'Booked jobs increased significantly after AI-backed follow-up replaced manual callbacks.','service'=>'ai-automation','serviceName'=>'AI Automation','case'=>'home-services-lead-system'],
  ['img'=>'project_2_image','industry'=>'Health & Wellness','title'=>'A Reputation Rebuild Across 5 Locations','result'=>'Average rating climbed from under 4 stars to well above it across every location.','service'=>'reputation-management','serviceName'=>'Reputation Management','case'=>'health-wellness-reputation-rebuild'],
  ['img'=>'project_3_image','industry'=>'B2B & SaaS','title'=>'A Website and CRM Built to Match a Longer Sales Cycle','result'=>'Demo requests grew after the site and CRM were rebuilt around how the sales team actually works.','service'=>'crm-development','serviceName'=>'CRM Development','case'=>null],
  ['img'=>'project_4_image','industry'=>'Real Estate','title'=>'An Ad Account Rebuilt Around Cost Per Lead','result'=>'Cost per lead dropped substantially within the first 90 days of active management.','service'=>'google-ads-management','serviceName'=>'Google Ads Management','case'=>'real-estate-ad-account-rebuild'],
  ['img'=>'project_5_image','industry'=>'Contractors & Trades','title'=>'A Local SEO Cleanup That Reached the Map Pack','result'=>'Moved into the map pack for the business\'s core service area after a citation and content overhaul.','service'=>'local-seo','serviceName'=>'Local SEO','case'=>null],
  ['img'=>'project_6_image','industry'=>'Retail & E-Commerce','title'=>'A Social Presence Rebuilt for Consistency','result'=>'Engagement grew substantially once posting became consistent instead of sporadic.','service'=>'social-media-management','serviceName'=>'Social Media Management','case'=>null],
];

include __DIR__ . '/includes/header.php';
?>

<nav class="breadcrumb" aria-label="Breadcrumb">
  <a href="/index.php">Home</a>
  <span class="sep">/</span>
  <span aria-current="page">Projects</span>
</nav>

<section class="projects-hero wrap">
  <span class="eyebrow center">Projects</span>
  <h1 class="reveal-text">Real Systems, Built for Real Businesses</h1>
  <p class="lead">A look at the kind of work behind the services page, shown by the outcome it produced, not just the deliverable.</p>
</section>

<section class="section" style="padding-top:20px;">
  <div class="wrap">
    <div class="section-head reveal">
      <span class="eyebrow">Recent Work</span>
      <h2>Real systems, organized by outcome.</h2>
    </div>
    <div class="project-grid reveal">
      <?php foreach ($PROJECTS as $p): ?>
      <article class="project-card">
        <div class="media-frame project-card__frame">
          <?php octg_media($p['img'], $p['title'] . ' Preview'); ?>
        </div>
        <span class="project-card__industry"><?php echo htmlspecialchars($p['industry']); ?></span>
        <h3><?php echo htmlspecialchars($p['title']); ?></h3>
        <p><?php echo htmlspecialchars($p['result']); ?></p>
        <?php if ($p['case']): ?>
        <a href="/projects/<?php echo htmlspecialchars($p['case']); ?>.php" class="project-card__link project-card__link--primary">
          Read the Full Case Study
          <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M5 12h14M13 6l6 6-6 6"/></svg>
        </a>
        <?php endif; ?>
        <a href="/services/<?php echo htmlspecialchars($p['service']); ?>.php" class="project-card__link">
          See How We Do This: <?php echo htmlspecialchars($p['serviceName']); ?>
          <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M5 12h14M13 6l6 6-6 6"/></svg>
        </a>
      </article>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<section class="section final-cta">
  <div class="wrap">
    <span class="eyebrow center">Every Business Deserves One Chance To Grow</span>
    <h2>Your business could be the next one on this page.</h2>
    <p class="lead">Book a growth call and let's talk about what a similar system would look like for you.</p>
    <div class="final-cta__ctas">
      <a href="/book-demo.php" class="btn btn-primary">Book a Growth Call</a>
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
    { "@type": "ListItem", "position": 2, "name": "Projects", "item": "https://onechancetogrow.com/projects.php" }
  ]
}
</script>

<?php include __DIR__ . '/includes/footer.php'; ?>
