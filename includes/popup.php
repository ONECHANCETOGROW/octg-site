<?php
/* ==========================================================================
   POPUP.PHP — the single site-wide "Book a Growth Call" slide-in.
   Included once by header.php so it's available on every page.
   Appears after a short delay/scroll and remembers a dismissal for 7 days.
   ========================================================================== */
?>
<aside class="growth-popup" id="growthPopup" role="complementary" aria-label="Book a growth call" hidden>
  <button class="growth-popup__close" id="growthPopupClose" aria-label="Dismiss">
    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M6 6l12 12M18 6L6 18"/></svg>
  </button>
  <h4>Still deciding?</h4>
  <p>Book a free 20-minute growth call — we'll show you where your business is leaving growth on the table.</p>
  <a href="/book-demo.php" class="btn btn-primary">Book a Growth Call</a>
</aside>

<script>
(function(){
  var popup = document.getElementById('growthPopup');
  var closeBtn = document.getElementById('growthPopupClose');
  if (!popup || !closeBtn) return;

  var STORAGE_KEY = 'octg_popup_dismissed_until';
  var now = Date.now();
  var dismissedUntil = Number(window.localStorage.getItem(STORAGE_KEY) || 0);
  if (dismissedUntil > now) return;

  var reveal = function(){
    popup.hidden = false;
    requestAnimationFrame(function(){ popup.classList.add('is-visible'); });
    window.removeEventListener('scroll', onScroll);
  };
  var onScroll = function(){
    if (window.scrollY > window.innerHeight * 0.6) reveal();
  };

  var timer = setTimeout(reveal, 20000);
  window.addEventListener('scroll', onScroll, { passive: true });

  closeBtn.addEventListener('click', function(){
    clearTimeout(timer);
    popup.classList.remove('is-visible');
    var sevenDays = 7 * 24 * 60 * 60 * 1000;
    window.localStorage.setItem(STORAGE_KEY, String(Date.now() + sevenDays));
    setTimeout(function(){ popup.hidden = true; }, 400);
  });
})();
</script>
