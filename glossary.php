<?php
/* ==========================================================================
   GLOSSARY.PHP — plain-English definitions of terms used across our
   services, each linking to the relevant service page. Distinct from
   Resources (articles) and FAQ (relationship questions).
   ========================================================================== */
$pageTitle       = 'Marketing & Growth Glossary | One Chance To Grow';
$pageDescription = "Plain-English definitions for SEO, CRM, automation, and advertising terms, without the jargon. Part of One Chance To Grow's resource library.";
$pageSlug        = 'glossary';
$activeNav       = '';
$bodyClass       = 'page-glossary';

$TERMS = [
  ['term'=>'AI Chatbot','cat'=>'automation','def'=>'Software that answers website visitors instantly using conversational AI, trained on your business so it can qualify leads and answer common questions any hour of the day.','service'=>'ai-chatbots'],
  ['term'=>'Citation','cat'=>'seo','def'=>'Any online listing of your business name, address, and phone number, on directories, review sites, and maps, used by search engines as a local trust signal.','service'=>'citation-management'],
  ['term'=>'Conversion Rate','cat'=>'analytics','def'=>'The percentage of visitors who take a desired action, like calling, filling out a form, or booking, out of everyone who visits a page.','service'=>'analytics-reporting'],
  ['term'=>'Cost Per Lead (CPL)','cat'=>'analytics','def'=>'The average amount spent on advertising to generate one lead, calculated by dividing total ad spend by the number of leads produced.','service'=>'analytics-reporting'],
  ['term'=>'CRM','cat'=>'automation','def'=>'Customer Relationship Management software, a system for tracking every lead, customer, and conversation in one place instead of scattered across inboxes and spreadsheets.','service'=>'crm-development'],
  ['term'=>'Email Drip Campaign','cat'=>'marketing','def'=>'A pre-written series of emails sent automatically over time, triggered by an action like signing up or requesting a quote.','service'=>'email-marketing'],
  ['term'=>'Google Business Profile','cat'=>'seo','def'=>"The free listing that appears on Google Search and Maps when someone searches for your business or what you offer, formerly called Google My Business.",'service'=>'google-business-profile-optimization'],
  ['term'=>'Landing Page','cat'=>'web','def'=>'A standalone web page built around a single offer and call to action, typically the destination for a specific ad campaign rather than the general website.','service'=>'landing-pages'],
  ['term'=>'Lead Generation','cat'=>'marketing','def'=>'The ongoing process of attracting and capturing potential customer information, through ads, SEO, or content, so a sales process can follow up.','service'=>'lead-generation'],
  ['term'=>'Local SEO','cat'=>'seo','def'=>"Search engine optimization focused on ranking for searches tied to a specific city, county, or service area, rather than competing nationally.",'service'=>'local-seo'],
  ['term'=>'Map Pack','cat'=>'seo','def'=>"The block of three local business listings, with a map, that appears at the top of Google results for local searches like 'plumber near me.'",'service'=>'google-maps-ranking'],
  ['term'=>'NAP Consistency','cat'=>'seo','def'=>"Short for Name, Address, Phone, this refers to keeping that information identical across every online listing, which search engines treat as a trust signal.",'service'=>'citation-management'],
  ['term'=>'PPC (Pay-Per-Click)','cat'=>'marketing','def'=>'An advertising model where you pay only when someone clicks your ad, most commonly associated with Google Ads and social media advertising.','service'=>'google-ads-management'],
  ['term'=>'Retargeting','cat'=>'marketing','def'=>"Advertising shown specifically to people who already visited your website or engaged with a previous ad, typically converting better than cold audiences.",'service'=>'facebook-instagram-ads'],
  ['term'=>'Reputation Management','cat'=>'marketing','def'=>'The ongoing practice of monitoring and responding to reviews and mentions across platforms so your public reputation reflects your business accurately.','service'=>'reputation-management'],
  ['term'=>'Review Generation','cat'=>'marketing','def'=>'A system for consistently asking satisfied customers to leave reviews, typically automated through text or email at the right moment.','service'=>'review-generation'],
  ['term'=>'SaaS','cat'=>'software','def'=>"Software as a Service, software delivered through a subscription and accessed online rather than installed and owned outright.",'service'=>'saas-solutions'],
  ['term'=>'Sales Funnel','cat'=>'marketing','def'=>'The mapped path a visitor follows from first click to paying customer, typically including a landing page, follow-up sequence, and clear calls to action at each stage.','service'=>'sales-funnels'],
  ['term'=>'SEO','cat'=>'seo','def'=>'Search Engine Optimization, the practice of improving a website so it ranks higher in unpaid search results for relevant terms.','service'=>'seo'],
  ['term'=>'SMS Marketing','cat'=>'marketing','def'=>'Marketing messages sent by text message, commonly used for appointment reminders, promotions, and missed-call follow-up.','service'=>'sms-marketing'],
  ['term'=>'Workflow Automation','cat'=>'automation','def'=>'Connecting separate tools and teams so information moves between them automatically, without manual re-entry or handoff delays.','service'=>'workflow-automation'],
];
usort($TERMS, function($a, $b){ return strcasecmp($a['term'], $b['term']); });

$FILTERS = ['all'=>'All Terms','seo'=>'SEO & Visibility','marketing'=>'Marketing','automation'=>'Automation & AI','web'=>'Web','analytics'=>'Analytics','software'=>'Software'];

include __DIR__ . '/includes/header.php';
?>

<nav class="breadcrumb" aria-label="Breadcrumb">
  <a href="/index.php">Home</a>
  <span class="sep">/</span>
  <span aria-current="page">Glossary</span>
</nav>

<section class="glossary-hero wrap">
  <span class="eyebrow center">Glossary</span>
  <h1 class="reveal-text">Marketing & Growth Terms, Explained Plainly</h1>
  <p class="lead">No jargon left unexplained. Each term links to the service it relates to if you want to go deeper.</p>
</section>

<section class="section" style="padding-top:20px;">
  <div class="wrap">
    <h2 class="sr-only">Browse Terms</h2>
    <div class="review-filters" role="tablist" aria-label="Filter terms by category">
      <?php foreach ($FILTERS as $key => $label): ?>
      <button type="button" class="review-filter <?php echo $key === 'all' ? 'is-active' : ''; ?>" data-filter="<?php echo htmlspecialchars($key); ?>"><?php echo htmlspecialchars($label); ?></button>
      <?php endforeach; ?>
    </div>

    <div class="glossary-list reveal">
      <?php foreach ($TERMS as $t): ?>
      <div class="glossary-term" data-topic="<?php echo htmlspecialchars($t['cat']); ?>">
        <h3><?php echo htmlspecialchars($t['term']); ?></h3>
        <p><?php echo htmlspecialchars($t['def']); ?></p>
        <a href="/services/<?php echo htmlspecialchars($t['service']); ?>.php" class="glossary-term__link">See This Service
          <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M5 12h14M13 6l6 6-6 6"/></svg>
        </a>
      </div>
      <?php endforeach; ?>
    </div>
    <p class="filter-empty-msg" id="glossaryEmpty" hidden>No terms in that category yet.</p>
  </div>
</section>

<section class="section final-cta">
  <div class="wrap">
    <span class="eyebrow center">Term Not Here?</span>
    <h2>Ask us, we'll add it.</h2>
    <div class="final-cta__ctas">
      <a href="/contact.php" class="btn btn-primary">Contact Us</a>
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
    { "@type": "ListItem", "position": 2, "name": "Glossary", "item": "https://onechancetogrow.com/glossary.php" }
  ]
}
</script>
<script type="application/ld+json">
<?php
$defSchema = [
    '@context' => 'https://schema.org',
    '@type' => 'DefinedTermSet',
    'name' => 'One Chance To Grow Marketing & Growth Glossary',
    'hasDefinedTerm' => array_map(function ($t) {
        return ['@type' => 'DefinedTerm', 'name' => $t['term'], 'description' => $t['def']];
    }, $TERMS),
];
echo json_encode($defSchema, JSON_UNESCAPED_SLASHES);
?>
</script>

<?php include __DIR__ . '/includes/footer.php'; ?>
