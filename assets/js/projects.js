/* ==========================================================================
   PROJECTS.JS — page-specific script only.
   ========================================================================== */

document.addEventListener('DOMContentLoaded', function () {
  document.querySelectorAll('.project-card').forEach(function (card, i) {
    card.classList.add('reveal');
    card.style.transitionDelay = (i % 3) * 80 + 'ms';
  });
});
