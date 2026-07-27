/* ==========================================================================
   INDEX.JS — homepage-specific script only.
   Shared nav/reveal/growth-rail/marquee behavior is handled by
   assets/js/navigation.js and assets/js/animations.js on every page.
   ========================================================================== */

document.addEventListener('DOMContentLoaded', function () {
  /* Legacy hero visual (image panel + vine) no longer exists now that the
     hero renders over the global background video — drawOnLoad/initParallax
     both no-op safely if their target isn't present, so this adapts
     automatically if the hero markup changes again.
     The background video itself is intentionally NOT scroll-transformed:
     it's position:fixed at exactly 100vh, so any translateY would expose
     an edge; being fixed already reads as "infinitely slower than content,"
     which is the parallax effect. Instead, give the hero's own text block a
     light drift so it reads with a bit of depth against that fixed backdrop. */
  if (window.OCTG && window.OCTG.initParallax) {
    window.OCTG.initParallax('.hero__content-center', 0.06);
  }
});
