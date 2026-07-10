/* ==========================================================================
   ANIMATIONS.JS — shared motion system used on every page.
   Exposes window.OCTG for page-specific scripts (e.g. index.js) to call.
   ========================================================================== */

window.OCTG = window.OCTG || {};

(function () {
  var reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

  /* ---- Reusable: draw-on-load line art (e.g. hero vine) ----
     Call OCTG.drawOnLoad('#heroVine') from a page's own JS file. */
  window.OCTG.drawOnLoad = function (selector) {
    var el = document.querySelector(selector);
    if (!el) return;
    requestAnimationFrame(function () { el.classList.add('is-drawn'); });
  };

  /* ---- Reusable: particle field on a canvas — call OCTG.initParticles('#myCanvas')
     Used sparingly (e.g. the About page hero), not applied automatically anywhere. ---- */
  window.OCTG.initParticles = function (canvasSelector, options) {
    var canvas = document.querySelector(canvasSelector);
    if (!canvas || reduceMotion) return;
    var ctx = canvas.getContext('2d');
    if (!ctx) return;
    options = options || {};
    var count = options.count || 40;
    var color = options.color || '92,143,34';
    var particles = [];
    var raf;

    function resize() {
      canvas.width = canvas.offsetWidth;
      canvas.height = canvas.offsetHeight;
    }

    function init() {
      particles = [];
      for (var i = 0; i < count; i++) {
        particles.push({
          x: Math.random() * canvas.width,
          y: Math.random() * canvas.height,
          r: Math.random() * 1.6 + 0.4,
          vx: (Math.random() - 0.5) * 0.15,
          vy: (Math.random() - 0.5) * 0.15,
          a: Math.random() * 0.5 + 0.15,
        });
      }
    }

    function tick() {
      ctx.clearRect(0, 0, canvas.width, canvas.height);
      particles.forEach(function (p) {
        p.x += p.vx; p.y += p.vy;
        if (p.x < 0) p.x = canvas.width; if (p.x > canvas.width) p.x = 0;
        if (p.y < 0) p.y = canvas.height; if (p.y > canvas.height) p.y = 0;
        ctx.beginPath();
        ctx.arc(p.x, p.y, p.r, 0, Math.PI * 2);
        ctx.fillStyle = 'rgba(' + color + ',' + p.a + ')';
        ctx.fill();
      });
      raf = requestAnimationFrame(tick);
    }

    resize();
    init();
    tick();
    window.addEventListener('resize', function () { resize(); init(); });

    // Pause when off-screen to avoid burning CPU on a background element
    if ('IntersectionObserver' in window) {
      new IntersectionObserver(function (entries) {
        entries.forEach(function (entry) {
          if (entry.isIntersecting) { if (!raf) tick(); }
          else { cancelAnimationFrame(raf); raf = null; }
        });
      }, { threshold: 0 }).observe(canvas);
    }
  };

  /* ---- Reusable: subtle parallax — call OCTG.initParallax('.selector', 0.15)
     Shifts an element's translateY at a fraction of scroll speed. ---- */
  window.OCTG.initParallax = function (selector, speed) {
    if (reduceMotion) return;
    var els = document.querySelectorAll(selector);
    if (!els.length) return;
    speed = speed || 0.15;
    var update = function () {
      els.forEach(function (el) {
        var rect = el.getBoundingClientRect();
        var centerOffset = rect.top + rect.height / 2 - window.innerHeight / 2;
        el.style.transform = 'translateY(' + (centerOffset * -speed) + 'px)';
      });
    };
    window.addEventListener('scroll', update, { passive: true });
    update();
  };

  /* ---- Reusable: trap keyboard focus within a container while it's open.
     Returns a cleanup function to call when the container closes.
     Used by mobile nav (navigation.js) and modals (modals.php). ---- */
  window.OCTG.trapFocus = function (container) {
    var focusableSelector = 'a[href], button:not([disabled]), input:not([disabled]), textarea:not([disabled]), select:not([disabled]), [tabindex]:not([tabindex="-1"])';

    function getFocusable() {
      return Array.prototype.slice.call(container.querySelectorAll(focusableSelector))
        .filter(function (el) { return el.offsetParent !== null; });
    }

    function onKeydown(e) {
      if (e.key !== 'Tab') return;
      var focusable = getFocusable();
      if (!focusable.length) return;
      var first = focusable[0];
      var last = focusable[focusable.length - 1];

      if (e.shiftKey && document.activeElement === first) {
        e.preventDefault(); last.focus();
      } else if (!e.shiftKey && document.activeElement === last) {
        e.preventDefault(); first.focus();
      }
    }

    container.addEventListener('keydown', onKeydown);
    var focusable = getFocusable();
    if (focusable.length) focusable[0].focus();

    return function cleanup() { container.removeEventListener('keydown', onKeydown); };
  };

  document.addEventListener('DOMContentLoaded', function () {

    /* ---- Scroll reveal, applies to any .reveal element on any page ---- */
    var revealEls = document.querySelectorAll('.reveal');
    if (reduceMotion || !('IntersectionObserver' in window)) {
      revealEls.forEach(function (el) { el.classList.add('is-visible'); });
    } else {
      var io = new IntersectionObserver(function (entries) {
        entries.forEach(function (entry) {
          if (entry.isIntersecting) {
            entry.target.classList.add('is-visible');
            io.unobserve(entry.target);
          }
        });
      }, { threshold: 0.15, rootMargin: '0px 0px -60px 0px' });
      revealEls.forEach(function (el) { io.observe(el); });
    }

    /* ---- Growth rail: fill + section nodes (present on every page) ---- */
    var rail = document.getElementById('growthRail');
    var fill = document.getElementById('railFill');
    if (rail && fill) {
      var sections = Array.prototype.slice.call(document.querySelectorAll('main section'));
      var leafSVG = '<svg viewBox="0 0 24 24" fill="currentColor"><path d="M12 2C6 6 4 12 8 18c2 3 6 3 8 0 4-6 2-12-4-16Z"/></svg>';

      var layoutNodes = function () {
        rail.querySelectorAll('.growth-rail__node').forEach(function (n) { n.remove(); });
        var docHeight = document.documentElement.scrollHeight - window.innerHeight;
        var trackTop = window.innerHeight * 0.08;
        var trackHeight = window.innerHeight * 0.84;
        sections.forEach(function (sec) {
          var rect = sec.getBoundingClientRect();
          var absTop = rect.top + window.scrollY + rect.height / 2;
          var pct = docHeight > 0 ? absTop / (docHeight + window.innerHeight) : 0;
          var node = document.createElement('div');
          node.className = 'growth-rail__node';
          node.innerHTML = leafSVG;
          node.style.top = (trackTop + pct * trackHeight) + 'px';
          rail.appendChild(node);
        });
      };

      var updateRail = function () {
        var scrollTop = window.scrollY;
        var docHeight = document.documentElement.scrollHeight - window.innerHeight;
        var pct = docHeight > 0 ? Math.min(scrollTop / docHeight, 1) : 0;
        fill.style.height = (pct * 84) + 'vh';
        var currentTop = fill.getBoundingClientRect().bottom;
        rail.querySelectorAll('.growth-rail__node').forEach(function (n) {
          n.classList.toggle('is-active', n.getBoundingClientRect().top <= currentTop + 4);
        });
      };

      layoutNodes();
      updateRail();
      window.addEventListener('scroll', updateRail, { passive: true });
      window.addEventListener('resize', function () { layoutNodes(); updateRail(); });
    }

    /* ---- Magnetic buttons: subtle pull toward cursor within a small radius.
       Rect is cached on mouseenter (not re-read every mousemove), and the
       style write is throttled to one per animation frame — mousemove can
       fire far faster than the display refreshes, and both the repeated
       layout read and the excess writes are unnecessary main-thread work
       that can delay other, INP-measured interactions. ---- */
    if (!reduceMotion && window.matchMedia('(hover: hover)').matches) {
      document.querySelectorAll('.btn').forEach(function (btn) {
        var strength = 10; // max px of pull — kept small on purpose
        var rect = null;
        var pending = null;

        btn.addEventListener('mouseenter', function () { rect = btn.getBoundingClientRect(); });
        btn.addEventListener('mousemove', function (e) {
          if (!rect) rect = btn.getBoundingClientRect();
          if (pending) return;
          pending = requestAnimationFrame(function () {
            var x = (e.clientX - rect.left - rect.width / 2) / (rect.width / 2);
            var y = (e.clientY - rect.top - rect.height / 2) / (rect.height / 2);
            btn.style.transform = 'translate(' + (x * strength) + 'px, ' + (y * strength) + 'px)';
            pending = null;
          });
        });
        btn.addEventListener('mouseleave', function () { btn.style.transform = ''; rect = null; });
      });
    }

    /* ---- Tilt cards: subtle 3D tilt following the cursor (pillar / service-chip).
       Same cached-rect + rAF-throttled pattern as the magnetic buttons above. ---- */
    if (!reduceMotion && window.matchMedia('(hover: hover)').matches) {
      document.querySelectorAll('.pillar, .service-chip').forEach(function (card) {
        var rect = null;
        var pending = null;

        card.addEventListener('mouseenter', function () { rect = card.getBoundingClientRect(); });
        card.addEventListener('mousemove', function (e) {
          if (!rect) rect = card.getBoundingClientRect();
          if (pending) return;
          pending = requestAnimationFrame(function () {
            var x = (e.clientX - rect.left) / rect.width - 0.5;
            var y = (e.clientY - rect.top) / rect.height - 0.5;
            card.style.transform = 'translateY(-5px) rotateX(' + (y * -4) + 'deg) rotateY(' + (x * 4) + 'deg)';
            pending = null;
          });
        });
        card.addEventListener('mouseleave', function () { card.style.transform = ''; rect = null; });
      });
    }

    /* ---- Smooth accordion: animates height on the native <details> element
       itself, so it works on every existing .faq-item with no markup change ---- */
    document.querySelectorAll('.faq-item').forEach(function (details) {
      var summary = details.querySelector('summary');
      if (!summary || reduceMotion) return;

      summary.addEventListener('click', function (e) {
        e.preventDefault();
        if (details.classList.contains('is-animating')) return;

        if (details.hasAttribute('open')) {
          var startHeight = details.offsetHeight;
          details.classList.add('is-animating');
          details.style.height = startHeight + 'px';
          requestAnimationFrame(function () { details.style.height = summary.offsetHeight + 'px'; });
          details.addEventListener('transitionend', function onEnd() {
            details.removeAttribute('open');
            details.style.height = '';
            details.classList.remove('is-animating');
            details.removeEventListener('transitionend', onEnd);
          }, { once: true });
        } else {
          details.setAttribute('open', '');
          var endHeight = details.scrollHeight;
          details.classList.add('is-animating');
          details.style.height = summary.offsetHeight + 'px';
          requestAnimationFrame(function () { details.style.height = endHeight + 'px'; });
          details.addEventListener('transitionend', function onEnd() {
            details.style.height = '';
            details.classList.remove('is-animating');
            details.removeEventListener('transitionend', onEnd);
          }, { once: true });
        }
      });
    });

    /* ---- Marquee: pause on hover/focus (any .marquee on any page) ---- */
    document.querySelectorAll('.marquee').forEach(function (m) {
      m.addEventListener('mouseenter', function () { m.classList.add('is-paused'); });
      m.addEventListener('mouseleave', function () { m.classList.remove('is-paused'); });
    });

    /* ---- Text reveal: splits .reveal-text elements into staggered words,
       walking child nodes so nested markup (e.g. accent-italic spans) survives ---- */
    var wrapWordsInNode = function (node) {
      if (node.nodeType === Node.TEXT_NODE) {
        var frag = document.createDocumentFragment();
        node.textContent.split(/(\s+)/).forEach(function (chunk) {
          if (chunk === '' ) return;
          if (/^\s+$/.test(chunk)) {
            frag.appendChild(document.createTextNode(chunk));
          } else {
            var span = document.createElement('span');
            span.className = 'word';
            span.textContent = chunk;
            frag.appendChild(span);
          }
        });
        node.parentNode.replaceChild(frag, node);
      } else if (node.nodeType === Node.ELEMENT_NODE) {
        Array.prototype.slice.call(node.childNodes).forEach(wrapWordsInNode);
      }
    };
    document.querySelectorAll('.reveal-text').forEach(function (el) {
      Array.prototype.slice.call(el.childNodes).forEach(wrapWordsInNode);
    });
    if (reduceMotion) {
      document.querySelectorAll('.reveal-text').forEach(function (el) { el.classList.add('is-revealed'); });
    } else if ('IntersectionObserver' in window) {
      var textIo = new IntersectionObserver(function (entries) {
        entries.forEach(function (entry) {
          if (!entry.isIntersecting) return;
          var words = entry.target.querySelectorAll('.word');
          words.forEach(function (w, i) { w.style.transitionDelay = (i * 35) + 'ms'; });
          entry.target.classList.add('is-revealed');
          textIo.unobserve(entry.target);
        });
      }, { threshold: 0.4 });
      document.querySelectorAll('.reveal-text').forEach(function (el) { textIo.observe(el); });
    }

    /* ---- Number counters: animates .stat-card__num from 0 to its value once visible ---- */
    if (!reduceMotion && 'IntersectionObserver' in window) {
      var counterIo = new IntersectionObserver(function (entries) {
        entries.forEach(function (entry) {
          if (!entry.isIntersecting) return;
          var el = entry.target;
          var raw = el.textContent.trim();
          var match = raw.match(/^([\d.]+)/);
          if (match) {
            var target = parseFloat(match[1]);
            var suffix = raw.slice(match[1].length);
            var isDecimal = match[1].indexOf('.') !== -1;
            var start = performance.now();
            var duration = 1100;
            el.classList.add('is-counting');
            var tick = function (now) {
              var progress = Math.min((now - start) / duration, 1);
              var eased = 1 - Math.pow(1 - progress, 3);
              var current = target * eased;
              el.textContent = (isDecimal ? current.toFixed(1) : Math.round(current)) + suffix;
              if (progress < 1) requestAnimationFrame(tick);
              else el.textContent = raw;
            };
            requestAnimationFrame(tick);
          }
          counterIo.unobserve(el);
        });
      }, { threshold: 0.5 });
      document.querySelectorAll('.stat-card__num').forEach(function (el) { counterIo.observe(el); });
    }

    /* ---- Timeline: reveals items and fills the connecting line as they appear ---- */
    document.querySelectorAll('.timeline').forEach(function (timeline) {
      var items = Array.prototype.slice.call(timeline.querySelectorAll('.timeline-item'));
      var fill = timeline.querySelector('.timeline__fill');
      if (!items.length) return;
      if (reduceMotion || !('IntersectionObserver' in window)) {
        items.forEach(function (item) { item.classList.add('is-visible'); });
        if (fill) fill.style.height = '100%';
        return;
      }
      var updateFill = function () {
        var lastVisible = -1;
        items.forEach(function (item, i) { if (item.classList.contains('is-visible')) lastVisible = i; });
        if (fill && lastVisible >= 0) {
          var pct = ((lastVisible + 1) / items.length) * 100;
          fill.style.height = pct + '%';
        }
      };
      var tio = new IntersectionObserver(function (entries) {
        entries.forEach(function (entry) {
          if (entry.isIntersecting) {
            entry.target.classList.add('is-visible');
            updateFill();
          }
        });
      }, { threshold: 0.4, rootMargin: '0px 0px -15% 0px' });
      items.forEach(function (item) { tio.observe(item); });
    });
  });
})();
