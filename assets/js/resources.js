/* ==========================================================================
   RESOURCES.JS — page-specific script only.
   ========================================================================== */

document.addEventListener('DOMContentLoaded', function () {
  var filters = document.querySelectorAll('.review-filter');
  var cards = document.querySelectorAll('.article-card');
  var emptyMsg = document.getElementById('articlesEmpty');

  filters.forEach(function (btn) {
    btn.addEventListener('click', function () {
      filters.forEach(function (b) { b.classList.remove('is-active'); });
      btn.classList.add('is-active');
      var filter = btn.dataset.filter;
      var visibleCount = 0;

      cards.forEach(function (card) {
        var match = filter === 'all' || card.dataset.topic === filter;
        card.classList.toggle('is-filtered-out', !match);
        if (match) visibleCount++;
      });

      if (emptyMsg) emptyMsg.hidden = visibleCount > 0;
    });
  });

  if (window.OCTG && window.OCTG.initForm) {
    window.OCTG.initForm(document.getElementById('newsletterForm'), {
      successMessage: 'You\u2019re subscribed \u2014 look out for the next issue.',
    });
  }
});
