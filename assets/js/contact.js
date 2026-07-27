/* ==========================================================================
   CONTACT.JS — page-specific script only.
   ========================================================================== */

document.addEventListener('DOMContentLoaded', function () {
  if (window.OCTG && window.OCTG.initForm) {
    window.OCTG.initForm(document.getElementById('contactForm'), {
      onSuccess: function (response) {
        var form = document.getElementById('contactForm');
        var successState = document.getElementById('contactSuccessState');
        if (form && successState) {
          form.style.transition = 'opacity 0.4s ease';
          form.style.opacity = '0';
          setTimeout(function () {
            form.style.display = 'none';
            successState.style.display = 'block';
            successState.style.opacity = '0';
            successState.style.transition = 'opacity 0.4s ease, transform 0.4s ease';
            successState.style.transform = 'translateY(10px)';
            
            // trigger reflow
            void successState.offsetWidth;
            
            successState.style.opacity = '1';
            successState.style.transform = 'translateY(0)';
          }, 400);
        }
      }
    });
  }
});
