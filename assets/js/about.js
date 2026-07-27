/* ==========================================================================
   ABOUT.JS — page-specific script: the leadership carousel + hero atmosphere.
   Shared reveal/timeline/magnetic-button behavior is handled generically by
   assets/js/animations.js; this file only drives what's unique to this page.
   ========================================================================== */

document.addEventListener('DOMContentLoaded', function () {
  var reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
  var hero = document.getElementById('teamHero');
  var heroSpeed = hero ? parseInt(hero.dataset.speed, 10) : NaN;
  var heroAuto = !hero || hero.dataset.auto !== 'false';

  /* ---- Hero atmosphere: particles + connection lines ---- */
  if (window.OCTG && window.OCTG.initParticles) {
    var particleCanvas = document.getElementById('teamHeroParticles');
    var density = particleCanvas && particleCanvas.className.indexOf('particle-low') > -1 ? 'low'
      : (particleCanvas && particleCanvas.className.indexOf('particle-high') > -1 ? 'high' : 'medium');
    var counts = { low: 20, medium: 36, high: 60 };
    window.OCTG.initParticles('#teamHeroParticles', { count: counts[density], color: '163,255,71', connections: true, connectDistance: 130 });
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
      var opacity = visible ? Math.max(1 - absOffset * 0.34, 0.08) : 0;

      if (reduceMotion) {
        card.style.transform = offset === 0 ? 'none' : 'translate3d(0,0,0)';
      } else {
        var x = offset * 250;
        var z = -absOffset * 170;
        var rotateY = offset * -24;
        var scale = Math.max(1 - absOffset * 0.18, 0.46);
        card.style.transform = 'translate3d(' + x + 'px,0,' + z + 'px) rotateY(' + rotateY + 'deg) scale(' + scale + ')';
      }
      card.style.opacity = opacity;
      card.style.zIndex = 100 - absOffset;
      card.style.pointerEvents = offset === 0 ? 'auto' : 'none';
      card.classList.toggle('is-active', offset === 0);
    });

    dots.forEach(function (dot, i) {
      var active = i === activeIndex;
      dot.classList.toggle('is-active', active);
      dot.setAttribute('aria-selected', active ? 'true' : 'false');
      dot.tabIndex = active ? 0 : -1;
    });

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
  function prev() { goTo(activeIndex - 1); }

  function startAutoAdvance() {
    if (reduceMotion || !heroAuto || count < 2) return;
    stopAutoAdvance();
    timer = setInterval(next, isNaN(heroSpeed) || heroSpeed < 1000 ? 5500 : heroSpeed);
  }
  function stopAutoAdvance() { if (timer) { clearInterval(timer); timer = null; } }

  dots.forEach(function (dot) {
    dot.addEventListener('click', function () {
      goTo(parseInt(dot.dataset.index, 10));
      startAutoAdvance(); // reset the timer after manual interaction
    });
    dot.addEventListener('keydown', function (e) {
      if (e.key === 'ArrowRight' || e.key === 'ArrowDown') { e.preventDefault(); next(); dots[activeIndex].focus(); startAutoAdvance(); }
      else if (e.key === 'ArrowLeft' || e.key === 'ArrowUp') { e.preventDefault(); prev(); dots[activeIndex].focus(); startAutoAdvance(); }
    });
  });

  var prevBtn = document.getElementById('teamPrev');
  var nextBtn = document.getElementById('teamNext');
  if (prevBtn) prevBtn.addEventListener('click', function () { prev(); startAutoAdvance(); });
  if (nextBtn) nextBtn.addEventListener('click', function () { next(); startAutoAdvance(); });

  if (carousel) {
    carousel.addEventListener('mouseenter', stopAutoAdvance);
    carousel.addEventListener('mouseleave', startAutoAdvance);
    carousel.addEventListener('focusin', stopAutoAdvance);
    carousel.addEventListener('focusout', startAutoAdvance);
  }

  render();
  startAutoAdvance();
});
