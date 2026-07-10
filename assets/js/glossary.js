/* ==========================================================================
   GLOSSARY.JS — page-specific script only.
   ========================================================================== */

document.addEventListener('DOMContentLoaded', function () {
  var filters = document.querySelectorAll('.review-filter');
  var terms = document.querySelectorAll('.glossary-term');
  var emptyMsg = document.getElementById('glossaryEmpty');

  filters.forEach(function (btn) {
    btn.addEventListener('click', function () {
      filters.forEach(function (b) { b.classList.remove('is-active'); });
      btn.classList.add('is-active');
      var filter = btn.dataset.filter;
      var visibleCount = 0;

      terms.forEach(function (term) {
        var match = filter === 'all' || term.dataset.topic === filter;
        term.classList.toggle('is-filtered-out', !match);
        if (match) visibleCount++;
      });

      if (emptyMsg) emptyMsg.hidden = visibleCount > 0;
    });
  });
});
