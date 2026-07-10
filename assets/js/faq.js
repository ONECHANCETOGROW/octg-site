/* ==========================================================================
   FAQ.JS — page-specific script only.
   ========================================================================== */

document.addEventListener('DOMContentLoaded', function () {
  var filters = document.querySelectorAll('.review-filter');
  var items = document.querySelectorAll('.faq-hub .faq-item');
  var emptyMsg = document.getElementById('faqEmpty');

  filters.forEach(function (btn) {
    btn.addEventListener('click', function () {
      filters.forEach(function (b) { b.classList.remove('is-active'); });
      btn.classList.add('is-active');
      var filter = btn.dataset.filter;
      var visibleCount = 0;

      items.forEach(function (item) {
        var match = filter === 'all' || item.dataset.topic === filter;
        item.classList.toggle('is-filtered-out', !match);
        if (match) visibleCount++;
      });

      if (emptyMsg) emptyMsg.hidden = visibleCount > 0;
    });
  });
});
