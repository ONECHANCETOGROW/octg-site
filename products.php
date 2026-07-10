<?php
/* ==========================================================================
   PRODUCTS.PHP — the 5 OCTG software products, referenced from every
   relevant service page via /products.php#[slug].
   ========================================================================== */
$PRODUCTS = require __DIR__ . '/data/products-catalog.php';
$SERVICES = require __DIR__ . '/data/services-catalog.php';
$svcBySlug = [];
foreach ($SERVICES as $row) { $svcBySlug[$row['slug']] = $row; }

$pageTitle       = 'Products — Software Built Into Every Engagement | One Chance To Grow';
$pageDescription = "The CRM, AI, reputation, ads, and website platforms behind One Chance To Grow's services — built for growing businesses, not enterprises.";
$pageSlug        = 'products';
$activeNav       = 'products';
$bodyClass       = 'page-products';

include __DIR__ . '/includes/header.php';
?>

<nav class="breadcrumb" aria-label="Breadcrumb">
  <a href="/index.php">Home</a>
  <span class="sep">/</span>
  <span aria-current="page">Products</span>
</nav>

<section class="products-hero wrap">
  <span class="eyebrow center">Our Products</span>
  <h1 class="reveal-text">The Software Behind Every Engagement</h1>
  <p class="lead">We don't just run your marketing on borrowed tools. These five platforms are what we actually build and manage on — and where it makes sense, they can run your business directly.</p>
</section>

<?php foreach ($PRODUCTS as $i => $p): ?>
<section class="section product-section <?php echo $i % 2 === 1 ? 'product-section--alt' : ''; ?>" id="<?php echo htmlspecialchars($p['slug']); ?>">
  <div class="wrap product-section__grid">
    <div class="product-section__media">
      <div class="media-frame product-frame">
        <?php octg_media('product_' . $p['slug'] . '_image', $p['name'] . ' Screenshot'); ?>
      </div>
    </div>
    <div class="product-section__copy reveal">
      <span class="eyebrow"><?php echo htmlspecialchars($p['eyebrow']); ?></span>
      <h2><?php echo htmlspecialchars($p['name']); ?></h2>
      <p class="lead"><?php echo htmlspecialchars($p['desc']); ?></p>
      <ul class="check-list">
        <?php foreach ($p['features'] as $f): ?>
        <li>
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12l5 5L20 6"/></svg>
          <span><?php echo htmlspecialchars($f); ?></span>
        </li>
        <?php endforeach; ?>
      </ul>
      <div class="product-section__pairs">
        <span class="product-section__pairs-label">Included with:</span>
        <?php foreach ($p['pairs_with'] as $slug): $s = $svcBySlug[$slug] ?? null; if (!$s) continue; ?>
          <a href="/services/<?php echo htmlspecialchars($slug); ?>.php" class="product-section__pair-link"><?php echo htmlspecialchars($s['name']); ?></a>
        <?php endforeach; ?>
      </div>
      <a href="/book-demo.php" class="btn btn-ghost">Ask About <?php echo htmlspecialchars($p['name']); ?> <svg class="btn-arrow" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M5 12h14M13 6l6 6-6 6"/></svg></a>
    </div>
  </div>
</section>
<?php endforeach; ?>

<section class="section final-cta">
  <div class="wrap">
    <span class="eyebrow center">One Brand. One Solution.</span>
    <h2>Software that's already part of the plan.</h2>
    <p class="lead">You don't buy these separately — they're built into the services that need them, from day one.</p>
    <div class="final-cta__ctas">
      <a href="/services.php" class="btn btn-primary">See All Services</a>
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
    { "@type": "ListItem", "position": 2, "name": "Products", "item": "https://onechancetogrow.com/products.php" }
  ]
}
</script>
<script type="application/ld+json">
<?php
$itemList = [
    '@context' => 'https://schema.org',
    '@type' => 'ItemList',
    'itemListElement' => array_values(array_map(function ($p, $i) {
        return [
            '@type' => 'ListItem',
            'position' => $i + 1,
            'item' => [
                '@type' => 'SoftwareApplication',
                'name' => $p['name'],
                'description' => $p['desc'],
                'url' => 'https://onechancetogrow.com/products.php#' . $p['slug'],
                'applicationCategory' => 'BusinessApplication',
            ],
        ];
    }, $PRODUCTS, array_keys($PRODUCTS))),
];
echo json_encode($itemList, JSON_UNESCAPED_SLASHES);
?>
</script>

<?php include __DIR__ . '/includes/footer.php'; ?>
