/* ==========================================================================
   PRODUCTS.JS — page-specific script only.
   ========================================================================== */

document.addEventListener('DOMContentLoaded', function () {
  if (!window.location.hash) return;
  var target = document.querySelector(window.location.hash);
  if (!target || !target.classList.contains('product-section')) return;

  target.classList.add('is-highlighted');
  setTimeout(function () { target.classList.add('is-highlight-fade'); }, 1200);
  setTimeout(function () { target.classList.remove('is-highlighted', 'is-highlight-fade'); }, 2200);
});
