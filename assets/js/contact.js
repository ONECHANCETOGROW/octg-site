/* ==========================================================================
   CONTACT.JS — page-specific script only.
   ========================================================================== */

document.addEventListener('DOMContentLoaded', function () {
  if (window.OCTG && window.OCTG.initForm) {
    window.OCTG.initForm(document.getElementById('contactForm'), {
      successMessage: 'Thanks — we\u2019ll be in touch within one business day.',
    });
  }
});
