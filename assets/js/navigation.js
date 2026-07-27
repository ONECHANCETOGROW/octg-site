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

  /* ---- Services mega-menu (advanced hover intent + click fallback) ---- */
  var navHasMega = document.querySelector('.nav-has-mega');
  var megaToggle = document.querySelector('.mega-toggle');
  var megaMenu = document.getElementById('servicesMega');
  var servicesLink = navHasMega ? navHasMega.querySelector('a') : null;
  var hoverTimeout = null;

  if (navHasMega && megaMenu) {
    function openMega() {
      clearTimeout(hoverTimeout);
      megaMenu.classList.add('is-open');
      if (megaToggle) megaToggle.setAttribute('aria-expanded', 'true');
    }
    
    function closeMega() {
      megaMenu.classList.remove('is-open');
      if (megaToggle) megaToggle.setAttribute('aria-expanded', 'false');
    }

    navHasMega.addEventListener('mouseenter', openMega);
    navHasMega.addEventListener('mouseleave', function() {
      hoverTimeout = setTimeout(closeMega, 180); // 180ms hover tolerance
    });

    if (megaToggle) {
      megaToggle.addEventListener('click', function(e) {
        e.preventDefault();
        e.stopPropagation();
        if (megaMenu.classList.contains('is-open')) closeMega();
        else openMega();
      });
    }

    // Support clicking the "Services" link directly on desktop
    if (servicesLink) {
      servicesLink.addEventListener('click', function(e) {
        if (window.innerWidth >= 980) {
          // Removed e.preventDefault() here so the link navigates natively
          if (megaMenu.classList.contains('is-open')) closeMega();
        }
      });
    }

    document.addEventListener('click', function(e) {
      if (!e.target.closest('.nav-has-mega')) {
        closeMega();
      }
    });

    document.addEventListener('keydown', function(e) {
      if (e.key === 'Escape' && megaMenu.classList.contains('is-open')) {
        closeMega();
        if (servicesLink) servicesLink.focus();
      }
    });

    // Keyboard accessibility focus-within support
    navHasMega.addEventListener('focusin', openMega);
    navHasMega.addEventListener('focusout', function(e) {
      if (!navHasMega.contains(e.relatedTarget)) {
        closeMega();
      }
    });
  }

  /* ---- Mobile nav ---- */
  var toggle = document.getElementById('menuToggle');
  var mobileNav = document.getElementById('mobileNav');
  var releaseTrap = null;
  var lockedScrollY = 0;

  /* position:fixed lock (not just overflow:hidden) so the page can't
     rubber-band/scroll behind the menu on iOS Safari; restores scroll
     position exactly on close. */
  function lockBodyScroll() {
    lockedScrollY = window.scrollY;
    document.body.style.position = 'fixed';
    document.body.style.top = (-lockedScrollY) + 'px';
    document.body.style.left = '0';
    document.body.style.right = '0';
  }

  function unlockBodyScroll() {
    document.body.style.position = '';
    document.body.style.top = '';
    document.body.style.left = '';
    document.body.style.right = '';
    window.scrollTo(0, lockedScrollY);
  }

  function openMobileNav() {
    mobileNav.classList.add('is-open');
    toggle.classList.add('is-open');
    toggle.setAttribute('aria-expanded', 'true');
    lockBodyScroll();
    if (window.OCTG && window.OCTG.trapFocus) {
      releaseTrap = window.OCTG.trapFocus(mobileNav);
    }
  }

  function closeMobileNav() {
    mobileNav.classList.remove('is-open');
    toggle.classList.remove('is-open');
    toggle.setAttribute('aria-expanded', 'false');
    unlockBodyScroll();
    if (releaseTrap) { releaseTrap(); releaseTrap = null; }
    toggle.focus();
  }

  if (toggle && mobileNav) {
    toggle.addEventListener('click', function () {
      if (mobileNav.classList.contains('is-open')) closeMobileNav();
      else openMobileNav();
    });
    mobileNav.querySelectorAll('a').forEach(function (a) {
      a.addEventListener('click', closeMobileNav);
    });
    /* Tapping empty space inside the overlay (outside the nav links/foot
       actions) closes it, same as tapping the toggle again. */
    mobileNav.addEventListener('click', function (e) {
      if (e.target === mobileNav) closeMobileNav();
    });
    document.addEventListener('keydown', function (e) {
      if (e.key === 'Escape' && mobileNav.classList.contains('is-open')) closeMobileNav();
    });
  }
});
