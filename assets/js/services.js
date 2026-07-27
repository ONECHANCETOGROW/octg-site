/* ==========================================================================
   SERVICES.JS — Services page-specific script only.
   Shared nav/reveal/growth-rail/mega-menu behavior is handled by
   assets/js/navigation.js and assets/js/animations.js on every page.
   ========================================================================== */

document.addEventListener('DOMContentLoaded', function () {
  var navLinks = Array.prototype.slice.call(document.querySelectorAll('.category-nav a'));
  if (!navLinks.length || !('IntersectionObserver' in window)) return;

  var sections = navLinks
    .map(function (link) { return document.getElementById(link.getAttribute('href').slice(1)); })
    .filter(Boolean);

  var setActive = function (id) {
    navLinks.forEach(function (link) {
      link.classList.toggle('is-active', link.getAttribute('href') === '#' + id);
    });
  };

  var io = new IntersectionObserver(function (entries) {
    entries.forEach(function (entry) {
      if (entry.isIntersecting) setActive(entry.target.id);
    });
  }, { rootMargin: '-45% 0px -50% 0px' });

  sections.forEach(function (sec) { io.observe(sec); });

  /* The sticky nav has no natural container to scope its stickiness to
     (it's a flat sibling of every section in <main>, not just the category
     ones), so without this it stays glued to the viewport for the rest of
     the page — covering the top of the compare/FAQ/final-CTA sections as
     the user scrolls past them. Hide it (not reposition it, to avoid any
     reflow) once the last category section has scrolled out of view. */
  var lastSection = sections[sections.length - 1];
  var navEl = document.querySelector('.category-nav');
  if (lastSection && navEl) {
    var boundaryIo = new IntersectionObserver(function (entries) {
      entries.forEach(function (entry) {
        var scrolledPast = !entry.isIntersecting && entry.boundingClientRect.bottom < 0;
        navEl.classList.toggle('is-past-categories', scrolledPast);
      });
    }, { threshold: 0 });
    boundaryIo.observe(lastSection);
  }
});
