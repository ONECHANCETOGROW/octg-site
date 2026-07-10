<?php
/* ==========================================================================
   NAVIGATION.PHP — shared primary + mobile nav, plus the Services mega-menu.
   Expects $activeNav to be set by the calling page (e.g. 'services').
   Included by header.php — do not include this file directly.

   Every service below already has a reserved URL under /services/ so the
   dedicated page can be dropped in later without touching this file again.
   ========================================================================== */
$activeNav = $activeNav ?? '';

$navItems = [
  'services'   => ['label' => 'Services',   'href' => '/services.php'],
  'products'   => ['label' => 'Products',   'href' => '/products.php'],
  'industries' => ['label' => 'Industries', 'href' => '/industries.php'],
  'reviews'    => ['label' => 'Reviews',    'href' => '/reviews.php'],
  'about'      => ['label' => 'About',      'href' => '/about.php'],
  'contact'    => ['label' => 'Contact',    'href' => '/contact.php'],
];

/* Services mega-menu — six categories, every one of the 33 services */
$serviceMega = [
  'Websites & Digital Presence' => [
    'Website Development'      => 'website-development',
    'Website Redesign'         => 'website-redesign',
    'Landing Pages'            => 'landing-pages',
    'eCommerce Development'    => 'ecommerce-development',
    'Custom Web Applications'  => 'custom-web-applications',
  ],
  'Local Visibility & SEO' => [
    'Search Engine Optimization' => 'seo',
    'Local SEO'                   => 'local-seo',
    'Google Business Profile'     => 'google-business-profile-optimization',
    'Google Maps Ranking'         => 'google-maps-ranking',
    'Citation Management'         => 'citation-management',
    'Business Listings Management'=> 'business-listings-management',
  ],
  'Reputation, Content & Brand' => [
    'Reputation Management'  => 'reputation-management',
    'Review Generation'      => 'review-generation',
    'Social Media Management'=> 'social-media-management',
    'Content Creation'       => 'content-creation',
    'Graphic Design'         => 'graphic-design',
    'Video Editing'          => 'video-editing',
    'Branding'               => 'branding',
  ],
  'Advertising & Lead Gen' => [
    'Google Ads Management'    => 'google-ads-management',
    'Facebook & Instagram Ads' => 'facebook-instagram-ads',
    'Lead Generation'          => 'lead-generation',
    'Sales Funnels'            => 'sales-funnels',
  ],
  'CRM, Automation & AI' => [
    'CRM Development'            => 'crm-development',
    'CRM Setup & Customization'  => 'crm-setup-customization',
    'Business Automation'        => 'business-automation',
    'AI Automation'              => 'ai-automation',
    'AI Chatbots'                => 'ai-chatbots',
    'Workflow Automation'        => 'workflow-automation',
    'Email Marketing'            => 'email-marketing',
    'SMS Marketing'              => 'sms-marketing',
  ],
  'Software & Analytics' => [
    'Analytics & Reporting'      => 'analytics-reporting',
    'SaaS Solutions'             => 'saas-solutions',
    'Custom Software Development'=> 'custom-software-development',
  ],
];
?>
<nav class="main-nav" aria-label="Primary">
  <ul>
    <?php foreach ($navItems as $key => $item): ?>
      <?php if ($key === 'services'): ?>
        <li class="nav-has-mega">
          <a href="<?php echo $item['href']; ?>" class="<?php echo $activeNav === $key ? 'is-active' : ''; ?>" <?php echo $activeNav === $key ? 'aria-current="page"' : ''; ?>>
            <?php echo htmlspecialchars($item['label']); ?>
          </a>
          <button class="mega-toggle" aria-label="Show services menu" aria-expanded="false" aria-controls="servicesMega">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M6 9l6 6 6-6"/></svg>
          </button>

          <div class="mega-menu" id="servicesMega" role="region" aria-label="Services menu">
            <?php foreach ($serviceMega as $category => $links): ?>
              <div class="mega-col">
                <span class="mega-col__label"><?php echo htmlspecialchars($category); ?></span>
                <ul>
                  <?php foreach ($links as $name => $slug): ?>
                    <li><a href="/services/<?php echo $slug; ?>.php"><?php echo htmlspecialchars($name); ?></a></li>
                  <?php endforeach; ?>
                </ul>
              </div>
            <?php endforeach; ?>
            <div class="mega-menu__foot">
              <span>100 Business Problems. <em>One Brand. One Solution.</em></span>
              <a href="/services.php" class="mega-menu__all">View All Services
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M5 12h14M13 6l6 6-6 6"/></svg>
              </a>
            </div>
          </div>
        </li>
      <?php else: ?>
        <li>
          <a href="<?php echo $item['href']; ?>" class="<?php echo $activeNav === $key ? 'is-active' : ''; ?>" <?php echo $activeNav === $key ? 'aria-current="page"' : ''; ?>>
            <?php echo htmlspecialchars($item['label']); ?>
          </a>
        </li>
      <?php endif; ?>
    <?php endforeach; ?>
  </ul>
</nav>

<div class="mobile-nav" id="mobileNav">
  <nav aria-label="Mobile">
    <ul>
      <?php foreach ($navItems as $key => $item): ?>
        <li>
          <a href="<?php echo $item['href']; ?>" class="<?php echo $activeNav === $key ? 'is-active' : ''; ?>" <?php echo $activeNav === $key ? 'aria-current="page"' : ''; ?>>
            <?php echo htmlspecialchars($item['label']); ?>
          </a>
        </li>
      <?php endforeach; ?>
    </ul>
  </nav>
  <div class="mobile-nav__foot">
    <a href="tel:+18022768331" class="header-phone">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"><path d="M5 4h4l2 5-2.5 1.5a11 11 0 0 0 5 5L15 13l5 2v4a2 2 0 0 1-2 2C9.4 21 3 14.6 3 6a2 2 0 0 1 2-2Z"/></svg>
      (802) 276-8331
    </a>
    <a href="/book-demo.php" class="btn btn-on-dark">Book a Demo</a>
  </div>
</div>
