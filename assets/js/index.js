/* ==========================================================================
   INDEX.JS — homepage-specific script only.
   Shared nav/reveal/growth-rail/marquee behavior is handled by
   assets/js/navigation.js and assets/js/animations.js on every page.
   ========================================================================== */

document.addEventListener('DOMContentLoaded', function () {
  if (window.OCTG && window.OCTG.drawOnLoad) {
    window.OCTG.drawOnLoad('#heroVine');
  }
});
