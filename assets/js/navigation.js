/* ==========================================================================
   NAVIGATION.JS — shared on every page. Header scroll state + mobile nav.
   Self-initializes on DOMContentLoaded. No page needs to call this directly.
   ========================================================================== */

document.addEventListener('DOMContentLoaded', function () {

  /* ---- Sticky header state ---- */
  var header = document.getElementById('siteHeader');
  if (header) {
    var onScrollHeader = function () {
      header.classList.toggle('is-scrolled', window.scrollY > 40);
    };
    onScrollHeader();
    window.addEventListener('scroll', onScrollHeader, { passive: true });
  }

  /* ---- Services mega-menu (tap-to-open fallback for touch/tablet) ---- */
  var megaToggle = document.querySelector('.mega-toggle');
  var megaMenu = document.getElementById('servicesMega');
  if (megaToggle && megaMenu) {
    megaToggle.addEventListener('click', function (e) {
      e.preventDefault();
      var open = megaMenu.classList.toggle('is-open');
      megaToggle.setAttribute('aria-expanded', open);
    });
    document.addEventListener('click', function (e) {
      if (!e.target.closest('.nav-has-mega')) {
        megaMenu.classList.remove('is-open');
        megaToggle.setAttribute('aria-expanded', 'false');
      }
    });
    document.addEventListener('keydown', function (e) {
      if (e.key === 'Escape') {
        megaMenu.classList.remove('is-open');
        megaToggle.setAttribute('aria-expanded', 'false');
      }
    });
  }

  /* ---- Mobile nav ---- */
  var toggle = document.getElementById('menuToggle');
  var mobileNav = document.getElementById('mobileNav');
  var releaseTrap = null;

  function closeMobileNav() {
    mobileNav.classList.remove('is-open');
    toggle.classList.remove('is-open');
    toggle.setAttribute('aria-expanded', 'false');
    document.body.style.overflow = '';
    if (releaseTrap) { releaseTrap(); releaseTrap = null; }
    toggle.focus();
  }

  if (toggle && mobileNav) {
    toggle.addEventListener('click', function () {
      var open = mobileNav.classList.toggle('is-open');
      toggle.classList.toggle('is-open', open);
      toggle.setAttribute('aria-expanded', open);
      document.body.style.overflow = open ? 'hidden' : '';
      if (open && window.OCTG && window.OCTG.trapFocus) {
        releaseTrap = window.OCTG.trapFocus(mobileNav);
      } else if (!open && releaseTrap) {
        releaseTrap(); releaseTrap = null;
      }
    });
    mobileNav.querySelectorAll('a').forEach(function (a) {
      a.addEventListener('click', closeMobileNav);
    });
    document.addEventListener('keydown', function (e) {
      if (e.key === 'Escape' && mobileNav.classList.contains('is-open')) closeMobileNav();
    });
  }
});
