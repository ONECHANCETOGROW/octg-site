/* ==========================================================================
   AUDIT.JS — page-specific script only.
   ========================================================================== */

document.addEventListener('DOMContentLoaded', function () {
  if (window.OCTG && window.OCTG.initForm) {
    window.OCTG.initForm(document.getElementById('auditForm'), {
      successMessage: 'Request received \u2014 we\u2019ll send your findings within one business day.',
    });
  }
});
