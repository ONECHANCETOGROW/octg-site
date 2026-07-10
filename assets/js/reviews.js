/* ==========================================================================
   REVIEWS.JS — page-specific script only.
   ========================================================================== */

document.addEventListener('DOMContentLoaded', function () {
  var filters = document.querySelectorAll('.review-filter');
  var cards = document.querySelectorAll('.quote-card');
  var emptyMsg = document.getElementById('reviewsEmpty');
  if (!filters.length) return;

  filters.forEach(function (btn) {
    btn.addEventListener('click', function () {
      filters.forEach(function (b) { b.classList.remove('is-active'); });
      btn.classList.add('is-active');
      var filter = btn.dataset.filter;
      var visibleCount = 0;

      cards.forEach(function (card) {
        var match = filter === 'all' || card.dataset.industry === filter;
        card.classList.toggle('is-filtered-out', !match);
        if (match) visibleCount++;
      });

      if (emptyMsg) emptyMsg.hidden = visibleCount > 0;
    });
  });
});
