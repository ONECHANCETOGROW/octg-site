<?php
/* ==========================================================================
   FAQ.PHP — centralized, business-level FAQ hub. Distinct from the
   per-service FAQs on each /services/[slug].php page (those answer
   service-specific objections; this answers relationship-level ones).
   ========================================================================== */
$pageTitle       = 'Frequently Asked Questions | One Chance To Grow';
$pageDescription = 'Straight answers about how engagements work, who owns your accounts and data, contract terms, and what to expect working with One Chance To Grow.';
$pageSlug        = 'faq';
$activeNav       = '';
$bodyClass       = 'page-faq';

$FAQS = [
  ['cat'=>'getting-started','catLabel'=>'Getting Started','q'=>'How is One Chance To Grow different from a typical marketing agency?','a'=>"Most agencies specialize in one channel, ads, SEO, or social, and hand you off between vendors for everything else. We build marketing, websites, CRM, and automation as one connected system, with one team accountable for how it all performs together."],
  ['cat'=>'getting-started','catLabel'=>'Getting Started','q'=>'What if I only need one service, not the whole system?','a'=>"That's fine. Every service we offer can be hired on its own. Most clients start with one or two and add more once those are working."],
  ['cat'=>'getting-started','catLabel'=>'Getting Started','q'=>'How fast can we get started?','a'=>"Most engagements begin with a discovery call and a working plan within a week. Full builds, like a new website or CRM setup, typically take a few weeks depending on scope."],
  ['cat'=>'getting-started','catLabel'=>'Getting Started','q'=>'Do you work with businesses outside the United States?','a'=>"We work with service and product businesses across the United States and Canada, from single-location local businesses to multi-location, growing companies."],
  ['cat'=>'working-together','catLabel'=>'Working Together','q'=>'Do you require a long-term contract?','a'=>"Contract terms are set out in your individual service agreement and vary by scope of work. We'll walk through exactly what you're committing to before you sign anything."],
  ['cat'=>'working-together','catLabel'=>'Working Together','q'=>'How much involvement do you need from me day to day?','a'=>"As much or as little as you want. Most clients review monthly reports and weigh in on strategy, while we handle execution. We'll never make you the bottleneck for routine work."],
  ['cat'=>'working-together','catLabel'=>'Working Together','q'=>'What happens if I want to pause or change scope?','a'=>"Tell us. Businesses change, and a growth partner should flex with that rather than lock you into a scope that no longer fits."],
  ['cat'=>'working-together','catLabel'=>'Working Together','q'=>"Who will actually work on my account?",'a'=>"We'll tell you specifically who is handling strategy versus day-to-day execution before you start, and how to reach them directly."],
  ['cat'=>'ownership','catLabel'=>'Ownership & Trust','q'=>'Who owns my website, CRM, and ad accounts?','a'=>"You do. Your business holds owner-level access on every platform we manage, with our team added as a manager, never the other way around. If we ever part ways, your accounts and data stay yours."],
  ['cat'=>'ownership','catLabel'=>'Ownership & Trust','q'=>'How is my data and information kept secure?','a'=>"Form submissions and account data are stored securely and access is limited to team members who need it. See our Privacy Policy for full detail on what we collect and how it's used."],
  ['cat'=>'ownership','catLabel'=>'Ownership & Trust','q'=>'Do you sell or share my information with third parties?','a'=>"No. We don't sell customer data, and we only share information with the specific tools and platforms needed to run your campaigns and systems."],
  ['cat'=>'results','catLabel'=>'Results & Reporting','q'=>'How do you measure success?','a'=>"Against leads, calls, and revenue, not impressions or followers. If a number in your report doesn't tie back to your business, we don't include it."],
  ['cat'=>'results','catLabel'=>'Results & Reporting','q'=>'How often will I hear from you?','a'=>"Monthly reporting at minimum, with more frequent check-ins depending on the services in your plan. You'll always have a direct line rather than a ticket queue."],
  ['cat'=>'results','catLabel'=>'Results & Reporting','q'=>'What if something isn\'t working?','a'=>"We'll tell you before you have to ask. Straight talk over sales talk is one of the principles we actually hold ourselves to, see our About page."],
];

$FILTERS = ['all'=>'All Questions','getting-started'=>'Getting Started','working-together'=>'Working Together','ownership'=>'Ownership & Trust','results'=>'Results & Reporting'];

include __DIR__ . '/includes/header.php';
?>

<nav class="breadcrumb" aria-label="Breadcrumb">
  <a href="/index.php">Home</a>
  <span class="sep">/</span>
  <span aria-current="page">FAQ</span>
</nav>

<section class="faq-hero wrap">
  <span class="eyebrow center">Frequently Asked Questions</span>
  <h1 class="reveal-text">Straight Answers, Before You Ask Twice</h1>
  <p class="lead">The questions we hear most, answered plainly. Looking for something service-specific? Each service page has its own FAQ section too.</p>
</section>

<section class="section" style="padding-top:20px;">
  <div class="wrap">
    <div class="review-filters" role="tablist" aria-label="Filter questions by topic">
      <?php foreach ($FILTERS as $key => $label): ?>
      <button type="button" class="review-filter <?php echo $key === 'all' ? 'is-active' : ''; ?>" data-filter="<?php echo htmlspecialchars($key); ?>"><?php echo htmlspecialchars($label); ?></button>
      <?php endforeach; ?>
    </div>

    <div class="faq faq-hub reveal">
      <?php foreach ($FAQS as $f): ?>
      <details class="faq-item" data-topic="<?php echo htmlspecialchars($f['cat']); ?>">
        <summary><?php echo htmlspecialchars($f['q']); ?></summary>
        <p><?php echo htmlspecialchars($f['a']); ?></p>
      </details>
      <?php endforeach; ?>
    </div>
    <p class="filter-empty-msg" id="faqEmpty" hidden>No questions in that category, try another filter above.</p>
  </div>
</section>

<section class="section final-cta">
  <div class="wrap">
    <span class="eyebrow center">Still Have A Question?</span>
    <h2>Ask us directly, we'll give you a straight answer.</h2>
    <div class="final-cta__ctas">
      <a href="/contact.php" class="btn btn-primary">Contact Us</a>
      <a href="/book-demo.php" class="btn btn-ghost">Book a Growth Call</a>
    </div>
  </div>
</section>

<script type="application/ld+json">
{
  "@context": "https://schema.org",
  "@type": "BreadcrumbList",
  "itemListElement": [
    { "@type": "ListItem", "position": 1, "name": "Home", "item": "https://onechancetogrow.com/" },
    { "@type": "ListItem", "position": 2, "name": "FAQ", "item": "https://onechancetogrow.com/faq.php" }
  ]
}
</script>
<script type="application/ld+json">
<?php
$faqSchema = [
    '@context' => 'https://schema.org',
    '@type' => 'FAQPage',
    'mainEntity' => array_map(function ($f) {
        return ['@type' => 'Question', 'name' => $f['q'], 'acceptedAnswer' => ['@type' => 'Answer', 'text' => $f['a']]];
    }, $FAQS),
];
echo json_encode($faqSchema, JSON_UNESCAPED_SLASHES);
?>
</script>

<?php include __DIR__ . '/includes/footer.php'; ?>
