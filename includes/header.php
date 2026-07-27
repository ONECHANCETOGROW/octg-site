<?php
require_once __DIR__ . '/../api/_lib.php';
/* ==========================================================================
   HEADER.PHP — shared document head + site header, included by every page.
   Each page must set $pageTitle, $pageDescription, $pageSlug, $activeNav,
   $bodyClass BEFORE including this file. Sensible defaults are provided.
   This file opens <main id="main-content"> — footer.php closes it. Every page's
   content sits between the header.php include and the footer.php include.
   ========================================================================== */

$pageTitle       = $pageTitle       ?? 'One Chance To Grow — Growth Systems for Serious Businesses';
$pageDescription = $pageDescription ?? 'One Chance To Grow builds the marketing, software, and automation that turn steady businesses into growing ones — as one connected partner.';
$pageSlug        = $pageSlug        ?? 'index';
$canonicalPath   = $canonicalPath   ?? ($pageSlug === 'index' ? '' : $pageSlug . '.php');
$activeNav       = $activeNav       ?? '';
$bodyClass       = $bodyClass       ?? '';
$ogImage         = $ogImage         ?? 'https://onechancetogrow.com/assets/img/og-default.jpg';
$headerTheme     = $headerTheme     ?? '';

// Global Background Media
$heroVideoData = octg_get_media_data('hero_video');
$hasBgMedia = $heroVideoData ? true : false;
$isVideo = $hasBgMedia && (strpos($heroVideoData['mime_type'] ?? '', 'video/') === 0 || preg_match('/\.(mp4|webm)$/i', $heroVideoData['file_path']));

if ($hasBgMedia) {
    $headerTheme = 'dark';
    $bodyClass .= ' site--dark';
}

/* $pageStyle: set to null to skip the auto per-page stylesheet (used by templated
   pages like service-page.php that load a shared stylesheet via $extraStyles instead). */
$pageStyle       = array_key_exists('pageStyle', get_defined_vars()) ? $pageStyle : ($pageSlug . '.css');
$extraStyles     = $extraStyles     ?? [];
$canonicalUrl    = 'https://onechancetogrow.com/' . $canonicalPath;
$headerClasses   = 'site-header';
if ($headerTheme === 'dark') {
    $headerClasses .= ' site-header--dark';
}
?>
<!DOCTYPE html>
<html lang="en-US">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?php echo htmlspecialchars($pageTitle); ?></title>
<meta name="description" content="<?php echo htmlspecialchars($pageDescription); ?>">
<link rel="canonical" href="<?php echo htmlspecialchars($canonicalUrl); ?>">

<!-- Open Graph -->
<meta property="og:title" content="<?php echo htmlspecialchars($pageTitle); ?>">
<meta property="og:description" content="<?php echo htmlspecialchars($pageDescription); ?>">
<meta property="og:type" content="website">
<meta property="og:url" content="<?php echo htmlspecialchars($canonicalUrl); ?>">
<meta property="og:image" content="<?php echo htmlspecialchars($ogImage); ?>">
<meta property="og:site_name" content="One Chance To Grow">

<!-- Twitter Card -->
<meta name="twitter:card" content="summary_large_image">
<meta name="twitter:title" content="<?php echo htmlspecialchars($pageTitle); ?>">
<meta name="twitter:description" content="<?php echo htmlspecialchars($pageDescription); ?>">
<meta name="twitter:image" content="<?php echo htmlspecialchars($ogImage); ?>">

<link rel="manifest" href="/manifest.webmanifest">
<link rel="icon" href="/assets/img/favicon.svg" type="image/svg+xml">
<link rel="apple-touch-icon" href="/assets/img/favicon.svg">
<meta name="theme-color" content="#15150F">

<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<?php if (!empty($preloadImage)): ?>
<link rel="preload" as="image" href="<?php echo htmlspecialchars($preloadImage); ?>" fetchpriority="high">
<?php endif; ?>
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:ital,wght@0,500;0,600;0,700;0,800;1,600;1,700&family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">

<script type="application/ld+json">
{
  "@context": "https://schema.org",
  "@type": "ProfessionalService",
  "name": "One Chance To Grow LLC",
  "url": "https://onechancetogrow.com/",
  "telephone": "+1-802-276-8331",
  "areaServed": ["US", "CA"],
  "address": { "@type": "PostalAddress", "addressRegion": "WY", "addressCountry": "US" },
  "description": "Marketing, web, CRM, and automation systems built for small and mid-size businesses across the United States and Canada."
}
</script>

<!-- Shared design system (never edit per-page) -->
<link rel="stylesheet" href="/assets/css/variables.css?v=1.6.9">
<link rel="stylesheet" href="/assets/css/typography.css?v=1.6.9">
<link rel="stylesheet" href="/assets/css/utilities.css?v=1.6.9">
<link rel="stylesheet" href="/assets/css/components.css?v=1.6.9">
<link rel="stylesheet" href="/assets/css/animations.css?v=1.6.9">
<?php foreach ($extraStyles as $sheet): ?>
<link rel="stylesheet" href="/assets/css/<?php echo htmlspecialchars($sheet); ?>.css?v=1.7.1">
<?php endforeach; ?>

<!-- Page-specific CSS -->
    <?php if ($pageStyle !== 'none'): ?>
    <link rel="stylesheet" href="/assets/css/<?php echo htmlspecialchars($pageStyle); ?>?v=1.6.9">
    <?php endif; ?>

    <!-- Desktop Enhancements Layer (1200px+) -->
    <link rel="stylesheet" href="/assets/css/desktop-enhancements.css?v=1.0.4">
</head>
<body class="<?php echo htmlspecialchars($bodyClass); ?>">
<?php if ($hasBgMedia): ?>
<div class="site-bg-container" style="position: fixed; top: 0; left: 0; width: 100vw; height: 100vh; z-index: -1;">
  <div class="site-bg-overlay" style="position: absolute; inset: 0; background: rgba(10, 10, 10, 0.82); mix-blend-mode: multiply; z-index: 1;"></div>
  <?php if ($isVideo): ?>
    <video autoplay loop muted playsinline style="width: 100%; height: 100%; object-fit: cover; z-index: 0; position: relative;" src="<?php echo htmlspecialchars($heroVideoData['file_path']); ?>"></video>
  <?php else: ?>
    <img style="width: 100%; height: 100%; object-fit: cover; z-index: 0; position: relative;" src="<?php echo htmlspecialchars($heroVideoData['file_path']); ?>" alt="Background">
  <?php endif; ?>
</div>
<?php endif; ?>

<a href="#main-content" class="skip-link">Skip to main content</a>

<div class="scroll-progress" id="scrollProgress" aria-hidden="true"></div>


<header class="<?php echo $headerClasses; ?>" id="siteHeader">
  <div class="wrap">
    <a href="/index.php" class="brand" aria-label="One Chance To Grow — home">
      <img src="/assets/img/logo-mark.png" alt="One Chance To Grow Logo" class="brand__mark" style="width:38px; height:38px; object-fit:contain; border-radius:8px;">
      <span class="brand__word">ONE CHANCE TO GROW<small>GROWTH - STRATEGY - RESULTS</small></span>
    </a>

    <?php include __DIR__ . '/navigation.php'; ?>

    <div class="header-actions">
      <a class="header-phone" href="tel:+18022768331">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"><path d="M5 4h4l2 5-2.5 1.5a11 11 0 0 0 5 5L15 13l5 2v4a2 2 0 0 1-2 2C9.4 21 3 14.6 3 6a2 2 0 0 1 2-2Z"/></svg>
        (802) 276-8331
      </a>
      <a href="/book-demo.php" class="btn btn-primary header-cta">Book a Demo</a>
      <button class="menu-toggle" id="menuToggle" aria-label="Open menu" aria-expanded="false" aria-controls="mobileNav">
        <span></span><span></span><span></span>
      </button>
    </div>
  </div>
</header>

<?php require_once __DIR__ . '/cms-media.php'; ?>
<?php require_once __DIR__ . '/social-icons.php'; ?>
<?php include __DIR__ . '/popup.php'; ?>
<?php include __DIR__ . '/modals.php'; ?>

<main id="main-content">
