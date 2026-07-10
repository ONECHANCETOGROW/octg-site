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
});
