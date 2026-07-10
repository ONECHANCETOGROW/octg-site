<?php
/* ==========================================================================
   FOOTER.PHP — closes <main>, renders the shared site footer and shared
   script tags. Every page ends with: include __DIR__.'/includes/footer.php';
   Requires $pageSlug to already be set (set once at the top of the page,
   before header.php is included).
   ========================================================================== */
$pageSlug = $pageSlug ?? 'index';
/* $pageScript: set to null to skip the auto per-page script (used by templated
   pages like service-page.php that have no page-specific behavior of their own). */
$pageScript  = array_key_exists('pageScript', get_defined_vars()) ? $pageScript : ($pageSlug . '.js');
$extraScripts = $extraScripts ?? [];
?>
</main>

<footer class="site-footer">
  <div class="wrap">
    <div class="footer-top">
      <div class="footer-brand">
        <span class="brand__word">One Chance <span>To</span> Grow</span>
        <p>Growth systems for businesses that are done guessing — marketing, software, and automation, built as one.</p>
      </div>
      <div class="footer-col">
        <p class="footer-col__heading" id="footer-label-services">Services</p>
        <nav aria-labelledby="footer-label-services">
        <ul>
          <li><a href="/services.php">Growth Marketing</a></li>
          <li><a href="/services.php">Web &amp; Software</a></li>
          <li><a href="/services.php">Automation &amp; AI</a></li>
          <li><a href="/products.php">Products</a></li>
        </ul>
        </nav>
      </div>
      <div class="footer-col">
        <p class="footer-col__heading" id="footer-label-company">Company</p>
        <nav aria-labelledby="footer-label-company">
        <ul>
          <li><a href="/about.php">About</a></li>
          <li><a href="/process.php">Our Process</a></li>
          <li><a href="/industries.php">Industries</a></li>
          <li><a href="/careers.php">Careers</a></li>
        </ul>
        </nav>
      </div>
      <div class="footer-col">
        <p class="footer-col__heading" id="footer-label-resources">Resources</p>
        <nav aria-labelledby="footer-label-resources">
        <ul>
          <li><a href="/resources.php">Articles</a></li>
          <li><a href="/glossary.php">Glossary</a></li>
          <li><a href="/faq.php">FAQ</a></li>
          <li><a href="/reviews.php">Reviews</a></li>
          <li><a href="/projects.php">Projects</a></li>
        </ul>
        </nav>
      </div>
      <div class="footer-col">
        <p class="footer-col__heading" id="footer-label-get-started">Get Started</p>
        <nav aria-labelledby="footer-label-get-started">
        <ul>
          <li><a href="/audit.php">Free Audit</a></li>
          <li><a href="/book-demo.php">Book a Growth Call</a></li>
          <li><a class="phone" href="tel:+18022768331"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"><path d="M5 4h4l2 5-2.5 1.5a11 11 0 0 0 5 5L15 13l5 2v4a2 2 0 0 1-2 2C9.4 21 3 14.6 3 6a2 2 0 0 1 2-2Z"/></svg>(802) 276-8331</a></li>
          <li><a href="mailto:hello@onechancetogrow.com">hello@onechancetogrow.com</a></li>
        </ul>
        </nav>
      </div>
    </div>
    <div class="footer-bottom">
      <span>&copy; <?php echo date('Y'); ?> One Chance To Grow LLC. Registered in Wyoming, USA. All rights reserved.</span>
      <nav><a href="/privacy.php">Privacy Policy</a><a href="/terms.php">Terms of Service</a><a href="/cookies.php">Cookie Policy</a><a href="/accessibility.php">Accessibility</a></nav>
    </div>
  </div>
</footer>

<!-- Mobile sticky CTA bar (shared, every page, hidden on desktop) -->
<div class="mobile-cta-bar" role="complementary" aria-label="Quick contact">
  <a href="tel:+18022768331" class="mobile-cta-bar__call">
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M5 4h4l2 5-2.5 1.5a11 11 0 0 0 5 5L15 13l5 2v4a2 2 0 0 1-2 2C9.4 21 3 14.6 3 6a2 2 0 0 1 2-2Z"/></svg>
    Call
  </a>
  <a href="/book-demo.php" class="mobile-cta-bar__book">Book a Growth Call</a>
</div>

<!-- Shared scripts (every page) -->
<script src="/assets/js/navigation.js?v=1.0.1" defer></script>
<script src="/assets/js/animations.js?v=1.0.1" defer></script>
<script src="/assets/js/forms.js?v=1.0.1" defer></script>
<?php foreach ($extraScripts as $script): ?>
<script src="/assets/js/<?php echo htmlspecialchars($script); ?>.js?v=1.0.1" defer></script>
<?php endforeach; ?>
<?php if ($pageScript): ?>
<!-- Page-specific script only -->
<script src="/assets/js/<?php echo htmlspecialchars($pageScript); ?>?v=1.0.1" defer></script>
<?php endif; ?>
</body>
</html>
