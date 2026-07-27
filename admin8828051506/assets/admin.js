/* ==========================================================================
   ADMIN.JS — shared across every /admin/*.php page.
   ========================================================================== */
document.addEventListener('DOMContentLoaded', function () {
  var toggle = document.getElementById('adminMenuToggle');
  var sidebar = document.getElementById('adminSidebar');
  if (!toggle || !sidebar) return;

  toggle.addEventListener('click', function () {
    var open = sidebar.classList.toggle('is-open');
    toggle.setAttribute('aria-expanded', open);
  });

  document.addEventListener('click', function (e) {
    if (window.innerWidth > 900) return;
    if (!sidebar.contains(e.target) && !toggle.contains(e.target) && sidebar.classList.contains('is-open')) {
      sidebar.classList.remove('is-open');
      toggle.setAttribute('aria-expanded', 'false');
    }
  });
});
