<?php
/* ==========================================================================
   MODALS.PHP — shared modal *behavior*, included once by header.php.
   This file has no visible markup of its own. Any page can add a modal by
   using the .site-modal component from components.css, e.g.:

     <div class="site-modal" id="video-modal" role="dialog" aria-modal="true" aria-label="Descriptive title">
       <div class="site-modal__backdrop" data-modal-close></div>
       <div class="site-modal__panel">
         <button class="site-modal__close" data-modal-close aria-label="Close">×</button>
         ... modal content ...
       </div>
     </div>

   and a trigger anywhere on the page:
     <button data-modal-open="video-modal">Watch how it works</button>

   role="dialog" + aria-modal="true" + aria-label on your instance are what
   make it announce correctly to screen readers — this shared file only
   handles the open/close/focus-trap behavior, not per-instance markup.
   ========================================================================== */
?>
<script>
(function(){
  var releaseTrap = null;
  var lastFocused = null;

  document.addEventListener('click', function(e){
    var opener = e.target.closest('[data-modal-open]');
    if (opener) {
      var modal = document.getElementById(opener.getAttribute('data-modal-open'));
      if (modal) {
        lastFocused = document.activeElement;
        modal.classList.add('is-open');
        document.body.style.overflow = 'hidden';
        if (window.OCTG && window.OCTG.trapFocus) releaseTrap = window.OCTG.trapFocus(modal);
      }
      return;
    }
    var closer = e.target.closest('[data-modal-close]');
    if (closer) {
      var openModal = closer.closest('.site-modal');
      if (openModal) {
        openModal.classList.remove('is-open');
        document.body.style.overflow = '';
        if (releaseTrap) { releaseTrap(); releaseTrap = null; }
        if (lastFocused) lastFocused.focus();
      }
    }
  });

  document.addEventListener('keydown', function(e){
    if (e.key === 'Escape') {
      document.querySelectorAll('.site-modal.is-open').forEach(function(m){
        m.classList.remove('is-open');
        document.body.style.overflow = '';
      });
      if (releaseTrap) { releaseTrap(); releaseTrap = null; }
      if (lastFocused) lastFocused.focus();
    }
  });
})();
</script>
