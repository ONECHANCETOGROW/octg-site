/* ==========================================================================
   FORMS.JS — shared form handling, used by any page with a <form data-octg-form>.
   Not wired to a live endpoint yet — contact.php / book-demo.php will set
   the form's `action` once the backend endpoint exists.
   ========================================================================== */

window.OCTG = window.OCTG || {};

window.OCTG.initForm = function (form, options) {
  if (!form) return;
  options = options || {};

  var statusEl = form.querySelector('[data-form-status]');
  var setStatus = function (message, isError) {
    if (!statusEl) return;
    statusEl.textContent = message;
    statusEl.classList.toggle('is-error', !!isError);
  };

  form.addEventListener('submit', function (e) {
    e.preventDefault();

    /* Honeypot spam trap — field should stay empty for real users */
    var honeypot = form.querySelector('input[name="hp_val_url"]');
    if (honeypot && honeypot.value) return;

    /* Native required-field validation */
    if (!form.checkValidity()) {
      form.reportValidity();
      return;
    }

    var submitBtn = form.querySelector('[type="submit"]');
    var originalBtnText = '';
    if (submitBtn) {
      submitBtn.disabled = true;
      submitBtn.classList.add('is-loading');
      var label = submitBtn.querySelector('.btn-label');
      if (label) {
        originalBtnText = label.textContent;
        label.textContent = 'Sending Message...';
      }
    }
    setStatus('Sending…', false);

    var endpoint = form.getAttribute('action');
    if (!endpoint || endpoint === '#') {
      /* No backend endpoint configured yet */
      setStatus('This form isn\u2019t connected to a backend yet.', true);
      if (submitBtn) {
        submitBtn.disabled = false;
        submitBtn.classList.remove('is-loading');
        var label = submitBtn.querySelector('.btn-label');
        if (label && originalBtnText) label.textContent = originalBtnText;
      }
      return;
    }

    fetch(endpoint, { method: 'POST', body: new FormData(form) })
      .then(function (res) {
        return res.json().catch(function () { return {}; }).then(function (body) {
          return { ok: res.ok, body: body };
        });
      })
      .then(function (result) {
        if (result.body && result.body.ok) {
          setStatus(options.successMessage || 'Thanks — we\u2019ll be in touch shortly.', false);
          // Only reset form, don't re-enable button immediately to prevent double clicks during success animation
          if (typeof options.onSuccess !== 'function') {
            form.reset();
          } else {
            options.onSuccess(result.body);
          }
        } else {
          setStatus((result.body && result.body.error) || 'Something went wrong. Please call (802) 276-8331 instead.', true);
          if (submitBtn) {
            submitBtn.disabled = false;
            submitBtn.classList.remove('is-loading');
            var label = submitBtn.querySelector('.btn-label');
            if (label && originalBtnText) label.textContent = originalBtnText;
          }
        }
      })
      .catch(function () {
        setStatus('Something went wrong. Please call (802) 276-8331 instead.', true);
        if (submitBtn) {
          submitBtn.disabled = false;
          submitBtn.classList.remove('is-loading');
          var label = submitBtn.querySelector('.btn-label');
          if (label && originalBtnText) label.textContent = originalBtnText;
        }
      });
  });
};
