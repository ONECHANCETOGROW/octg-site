/* ==========================================================================
   INDUSTRIES.JS — page-specific script only.
   Staggers the industry cards' reveal instead of all fading in at once.
   ========================================================================== */

document.addEventListener('DOMContentLoaded', function () {
  var cards = document.querySelectorAll('.industry-card');
  cards.forEach(function (card, i) {
    card.style.transitionDelay = (i % 4) * 70 + 'ms';
  });
});
