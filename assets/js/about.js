/* ==========================================================================
   ABOUT.JS — page-specific script: the leadership carousel + hero atmosphere.
   Shared reveal/timeline/magnetic-button behavior is handled generically by
   assets/js/animations.js; this file only drives what's unique to this page.
   ========================================================================== */

document.addEventListener('DOMContentLoaded', function () {
  var reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

  /* ---- Hero atmosphere: particles behind the globe ---- */
  if (window.OCTG && window.OCTG.initParticles) {
    window.OCTG.initParticles('#teamHeroParticles', { count: 36, color: '92,143,34' });
  }

  /* ---- Leadership carousel ---- */
  var stage = document.getElementById('teamStage');
  var cards = stage ? Array.prototype.slice.call(stage.querySelectorAll('.team-card')) : [];
  var dots = Array.prototype.slice.call(document.querySelectorAll('.team-dot'));
  var announce = document.getElementById('teamAnnounce');
  var carousel = document.getElementById('teamCarousel');
  if (!cards.length) return;

  var count = cards.length;
  var activeIndex = 0;
  var timer = null;

  function shortestOffset(index, active, total) {
    var diff = index - active;
    if (diff > total / 2) diff -= total;
    if (diff < -total / 2) diff += total;
    return diff;
  }

  function render() {
    cards.forEach(function (card, i) {
      var offset = shortestOffset(i, activeIndex, count);
      var absOffset = Math.abs(offset);
      var visible = absOffset <= 2;

      var x = offset * 210;
      var z = -absOffset * 150;
      var rotateY = offset * -26;
      var scale = Math.max(1 - absOffset * 0.16, 0.5);
      var opacity = visible ? Math.max(1 - absOffset * 0.34, 0.08) : 0;

      card.style.transform = 'translate3d(' + x + 'px,0,' + z + 'px) rotateY(' + rotateY + 'deg) scale(' + scale + ')';
      card.style.opacity = opacity;
      card.style.zIndex = 100 - absOffset;
      card.style.pointerEvents = offset === 0 ? 'auto' : 'none';
      card.classList.toggle('is-active', offset === 0);
    });

    dots.forEach(function (dot, i) { dot.classList.toggle('is-active', i === activeIndex); });

    if (announce) {
      var nameEl = cards[activeIndex].querySelector('.team-card__name');
      var titleEl = cards[activeIndex].querySelector('.team-card__title');
      announce.textContent = 'Now showing: ' + (nameEl ? nameEl.textContent : '') + ', ' + (titleEl ? titleEl.textContent : '');
    }
  }

  function goTo(index) {
    activeIndex = ((index % count) + count) % count;
    render();
  }

  function next() { goTo(activeIndex + 1); }

  function startAutoAdvance() {
    if (reduceMotion) return;
    stopAutoAdvance();
    timer = setInterval(next, 5500);
  }
  function stopAutoAdvance() { if (timer) { clearInterval(timer); timer = null; } }

  dots.forEach(function (dot) {
    dot.addEventListener('click', function () {
      goTo(parseInt(dot.dataset.index, 10));
      startAutoAdvance(); // reset the timer after manual interaction
    });
  });

  if (carousel) {
    carousel.addEventListener('mouseenter', stopAutoAdvance);
    carousel.addEventListener('mouseleave', startAutoAdvance);
    carousel.addEventListener('focusin', stopAutoAdvance);
    carousel.addEventListener('focusout', startAutoAdvance);
  }

  render();
  startAutoAdvance();
});
