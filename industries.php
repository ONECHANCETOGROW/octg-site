<?php
/* ==========================================================================
   INDUSTRIES.PHP — who we serve, with industry-specific pain points.
   ========================================================================== */
$pageTitle       = 'Industries We Serve — Home Services, Professional Services & More | One Chance To Grow';
$pageDescription = 'From home services to healthcare, retail to real estate — One Chance To Grow builds growth systems matched to how each industry actually sells.';
$pageSlug        = 'industries';
$activeNav       = 'industries';
$bodyClass       = 'page-industries';

$INDUSTRIES = [
  ['icon' => 'home', 'name' => 'Home Services', 'service' => 'ai-chatbots', 'serviceName' => 'AI Chatbots', 'copy' => "Plumbers, electricians, HVAC, and cleaning companies live and die by the phone. We build the systems that answer, book, and follow up on every call, even the ones you miss."],
  ['icon' => 'briefcase', 'name' => 'Professional Services', 'service' => 'reputation-management', 'serviceName' => 'Reputation Management', 'copy' => "Law firms, accountants, and consultants sell trust before they sell anything else. We build the website, content, and reputation that earn it before the first call."],
  ['icon' => 'heart', 'name' => 'Health & Wellness', 'service' => 'review-generation', 'serviceName' => 'Review Generation', 'copy' => "Clinics, dental practices, and wellness studios need booking, reminders, and reviews working together, not five separate logins. We build the system that keeps chairs full."],
  ['icon' => 'bag', 'name' => 'Retail & E-Commerce', 'service' => 'ecommerce-development', 'serviceName' => 'eCommerce Development', 'copy' => "Between in-store and online, retail businesses need a site that sells and a following that shows up. We build both, connected to the same data."],
  ['icon' => 'building', 'name' => 'Real Estate', 'service' => 'lead-generation', 'serviceName' => 'Lead Generation', 'copy' => "Agents and brokerages compete on visibility and speed to lead. We build the website, ads, and CRM that make sure you're first to respond, every time."],
  ['icon' => 'utensils', 'name' => 'Hospitality', 'service' => 'social-media-management', 'serviceName' => 'Social Media Management', 'copy' => "Restaurants, hotels, and venues live on reviews and repeat visitors. We build the reputation and marketing systems that keep both coming back."],
  ['icon' => 'network', 'name' => 'B2B & SaaS', 'service' => 'crm-development', 'serviceName' => 'CRM Development', 'copy' => "Longer sales cycles need nurture, not noise. We build the content, automation, and CRM that keep a lead warm from first click to signed contract."],
  ['icon' => 'hardhat', 'name' => 'Contractors & Trades', 'service' => 'local-seo', 'serviceName' => 'Local SEO', 'copy' => "Roofers, landscapers, and builders win jobs on trust and timing. We build the reviews, local visibility, and follow-up systems that win the bid before a competitor calls back."],
];

$ICONS = [
  'home' => '<path d="M4 11l8-7 8 7"/><path d="M6 10v9h5v-6h2v6h5v-9"/>',
  'briefcase' => '<rect x="3" y="8" width="18" height="12" rx="1"/><path d="M8 8V6a2 2 0 012-2h4a2 2 0 012 2v2"/>',
  'heart' => '<path d="M12 20s-7-4.35-9.5-9A5.5 5.5 0 0112 5.5 5.5 5.5 0 0121.5 11c-2.5 4.65-9.5 9-9.5 9Z"/>',
  'bag' => '<path d="M6 8h12l1 12H5L6 8Z"/><path d="M9 8V6a3 3 0 016 0v2"/>',
  'building' => '<rect x="4" y="3" width="10" height="18"/><rect x="14" y="9" width="6" height="12"/><path d="M7 7h2M7 11h2M7 15h2"/>',
  'utensils' => '<path d="M6 3v7a2 2 0 002 2v9M8 3v9M10 3v9"/><path d="M17 3c-1.5 0-2 2-2 4s.5 5 2 5 2-3 2-5-.5-4-2-4Zm0 9v9"/>',
  'network' => '<circle cx="12" cy="5" r="2.5"/><circle cx="5" cy="18" r="2.5"/><circle cx="19" cy="18" r="2.5"/><path d="M10.5 6.8L6.5 16M13.5 6.8l4 9.2"/>',
  'hardhat' => '<path d="M4 15a8 8 0 0116 0Z"/><path d="M2 15h20M11 6V4M11 6a5 5 0 00-5 5"/>',
];

include __DIR__ . '/includes/header.php';
?>

<nav class="breadcrumb" aria-label="Breadcrumb">
  <a href="/index.php">Home</a>
  <span class="sep">/</span>
  <span aria-current="page">Industries</span>
</nav>

<section class="industries-hero wrap">
  <span class="eyebrow center">Who We Work With</span>
  <h1 class="reveal-text">Local Shops. Growing Companies. Every Kind of Business.</h1>
  <p class="lead">Every industry sells differently, and gets found differently. We build the growth system around how yours actually works, not a one-size-fits-all package.</p>
</section>

<section class="section" style="padding-top:20px;">
  <div class="wrap">
    <div class="section-head reveal">
      <span class="eyebrow">Built For These Industries</span>
      <h2>Eight industries, eight different playbooks.</h2>
    </div>
    <div class="industry-grid">
      <?php foreach ($INDUSTRIES as $ind): ?>
      <article class="industry-card reveal">
        <svg class="industry-card__icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"><?php echo $ICONS[$ind['icon']]; ?></svg>
        <h3><?php echo htmlspecialchars($ind['name']); ?></h3>
        <p><?php echo htmlspecialchars($ind['copy']); ?></p>
        <a href="/services/<?php echo htmlspecialchars($ind['service']); ?>.php" class="industry-card__link"><?php echo htmlspecialchars($ind['serviceName']); ?>
          <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M5 12h14M13 6l6 6-6 6"/></svg>
        </a>
      </article>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<section class="section" style="background:var(--paper-deep);">
  <div class="wrap" style="text-align:center;">
    <span class="eyebrow center">Not Seeing Your Industry?</span>
    <h2 style="max-width:26ch;margin-inline:auto;">If a business wants more customers, we can probably help.</h2>
    <p class="lead" style="margin-inline:auto;margin-top:18px;">These eight are where we spend the most time, not the limits of who we work with. Tell us about your business either way.</p>
  </div>
</section>

<section class="section final-cta">
  <div class="wrap">
    <span class="eyebrow center">Every Business Deserves One Chance To Grow</span>
    <h2>Let's talk about your industry specifically.</h2>
    <p class="lead">Book a growth call and we'll show you what's working for businesses like yours right now.</p>
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
    { "@type": "ListItem", "position": 2, "name": "Industries", "item": "https://onechancetogrow.com/industries.php" }
  ]
}
</script>

<?php include __DIR__ . '/includes/footer.php'; ?>
