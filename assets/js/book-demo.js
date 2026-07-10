/* ==========================================================================
   BOOK-DEMO.JS — the multi-step wizard engine. Page-specific to book-demo.php.
   ========================================================================== */

document.addEventListener('DOMContentLoaded', function () {
  var reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
  var totalSteps = 9;
  var currentStep = 1;

  var stepsWrap   = document.getElementById('demoSteps');
  var barFill     = document.getElementById('demoBarFill');
  var stepCount   = document.getElementById('demoStepCount');
  var backBtn     = document.getElementById('demoBack');
  var nextBtn     = document.getElementById('demoNext');
  var form        = document.getElementById('demoForm');
  var reviewBox   = document.getElementById('demoReview');
  var wizard      = document.getElementById('demoWizard');
  var progressBar = document.querySelector('.demo-wizard__progress');
  var successBox  = document.getElementById('demoSuccess');

  if (!form || !stepsWrap) return;

  function getStepEl(n) { return stepsWrap.querySelector('.demo-step[data-step="' + n + '"]'); }

  function escapeHtml(str) {
    var div = document.createElement('div');
    div.textContent = str;
    return div.innerHTML;
  }

  /* ---- Pill selection ---- */
  document.querySelectorAll('.pill-group').forEach(function (group) {
    var mode = group.dataset.select;
    group.querySelectorAll('.pill').forEach(function (pill) {
      pill.addEventListener('click', function () {
        if (mode === 'single') {
          group.querySelectorAll('.pill').forEach(function (p) { p.classList.remove('is-selected'); });
          pill.classList.add('is-selected');
          var target = document.getElementById(group.dataset.target);
          if (target) target.value = pill.textContent.trim();
        } else {
          pill.classList.toggle('is-selected');
          syncMultiHiddenInputs(group);
        }
      });
    });
  });

  function syncMultiHiddenInputs(group) {
    var name = group.dataset.name;
    form.querySelectorAll('input[type="hidden"][data-multi-for="' + name + '"]').forEach(function (el) { el.remove(); });
    group.querySelectorAll('.pill.is-selected').forEach(function (pill) {
      var input = document.createElement('input');
      input.type = 'hidden';
      input.name = name + '[]';
      input.value = pill.textContent.trim();
      input.dataset.multiFor = name;
      form.appendChild(input);
    });
  }

  /* ---- Enter-to-advance on single-line inputs ---- */
  stepsWrap.querySelectorAll('input.demo-input').forEach(function (input) {
    input.addEventListener('keydown', function (e) {
      if (e.key === 'Enter') { e.preventDefault(); nextBtn.click(); }
    });
  });

  /* ---- Validation ---- */
  function validateStep(n) {
    var step = getStepEl(n);
    if (!step) return true;

    var required = step.querySelector('[data-required]');
    if (required) {
      var val = required.value.trim();
      if (val === '') return false;
      if (required.dataset.type === 'email' && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(val)) return false;
    }

    var pillGroup = step.querySelector('.pill-group');
    if (pillGroup) {
      if (pillGroup.dataset.select === 'single') {
        var targetEl = document.getElementById(pillGroup.dataset.target);
        if (!targetEl || targetEl.value === '') return false;
      } else {
        if (!pillGroup.querySelectorAll('.pill.is-selected').length) return false;
      }
    }
    return true;
  }

  /* ---- Review summary ---- */
  var reviewFields = [
    { name: 'business_name', label: 'Business Name', step: 1 },
    { name: 'contact_name',  label: 'Your Name',      step: 2 },
    { name: 'email',         label: 'Email',          step: 3 },
    { name: 'phone',         label: 'Phone',          step: 4 },
    { name: 'business_type', label: 'Business Type',  step: 5 },
    { name: 'services_interested', label: 'Interested In', step: 6, multi: true },
    { name: 'budget_range',  label: 'Budget',         step: 7 },
    { name: 'goals',         label: 'Goals',          step: 8 },
  ];

  function populateReview() {
    if (!reviewBox) return;
    reviewBox.innerHTML = '';
    reviewFields.forEach(function (f) {
      var value;
      if (f.multi) {
        var selected = Array.prototype.slice.call(form.querySelectorAll('input[data-multi-for="' + f.name + '"]')).map(function (i) { return i.value; });
        value = selected.length ? selected.join(', ') : '\u2014';
      } else {
        var el = form.querySelector('[name="' + f.name + '"]');
        value = (el && el.value.trim()) ? el.value.trim() : '\u2014';
      }
      var row = document.createElement('div');
      row.className = 'demo-review__row';
      row.innerHTML =
        '<span class="demo-review__label">' + f.label + '</span>' +
        '<span class="demo-review__value">' + escapeHtml(value) + '</span>' +
        '<a href="#" class="demo-review__edit" data-jump="' + f.step + '">Edit</a>';
      reviewBox.appendChild(row);
    });
    reviewBox.querySelectorAll('[data-jump]').forEach(function (a) {
      a.addEventListener('click', function (e) {
        e.preventDefault();
        goToStep(parseInt(a.dataset.jump, 10), 'back');
      });
    });
  }

  /* ---- Step transitions ---- */
  function updateProgress() {
    if (barFill) barFill.style.width = ((currentStep / totalSteps) * 100) + '%';
    if (stepCount) stepCount.textContent = 'Step ' + currentStep + ' of ' + totalSteps;
  }

  function updateNavButtons() {
    if (backBtn) backBtn.classList.toggle('is-shown', currentStep > 1);
    if (nextBtn) {
      if (currentStep === totalSteps) {
        nextBtn.classList.add('is-hidden');
      } else {
        nextBtn.classList.remove('is-hidden');
        var label = nextBtn.querySelector('.btn-label');
        if (label) label.textContent = (currentStep === totalSteps - 1) ? 'Review' : 'Continue';
      }
    }
  }

  function goToStep(newStep, direction) {
    var current = getStepEl(currentStep);
    var next = getStepEl(newStep);
    if (!current || !next || newStep === currentStep) return;

    function finish() {
      if (newStep === 9) populateReview();
      currentStep = newStep;
      updateProgress();
      updateNavButtons();
      var firstField = next.querySelector('input:not([type=hidden]), textarea');
      if (firstField) { try { firstField.focus({ preventScroll: true }); } catch (e) {} }
    }

    if (reduceMotion) {
      current.classList.remove('is-active');
      next.classList.add('is-active');
      finish();
      return;
    }

    var outClass = direction === 'fwd' ? 'is-anim-out-fwd' : 'is-anim-out-back';
    var inStartClass = direction === 'fwd' ? 'is-anim-in-start-fwd' : 'is-anim-in-start-back';
    current.classList.add(outClass);
    setTimeout(function () {
      current.classList.remove('is-active', outClass);
      next.classList.add(inStartClass, 'is-active');
      void next.offsetWidth; // force reflow so the removal below transitions
      next.classList.remove(inStartClass);
      finish();
    }, 260);
  }

  nextBtn.addEventListener('click', function () {
    if (!validateStep(currentStep)) {
      var step = getStepEl(currentStep);
      step.classList.add('is-shake');
      setTimeout(function () { step.classList.remove('is-shake'); }, 400);
      return;
    }
    if (currentStep < totalSteps) goToStep(currentStep + 1, 'fwd');
  });

  backBtn.addEventListener('click', function () {
    if (currentStep > 1) goToStep(currentStep - 1, 'back');
  });

  /* ---- Submission ---- */
  form.addEventListener('submit', function (e) {
    e.preventDefault();
    var submitBtn = form.querySelector('.demo-submit');
    var statusEl = form.querySelector('[data-form-status]');
    if (statusEl) statusEl.textContent = '';
    if (submitBtn) { submitBtn.classList.add('is-loading'); submitBtn.disabled = true; }

    fetch(form.action, { method: 'POST', body: new FormData(form) })
      .then(function (res) {
        return res.json().catch(function () { return {}; }).then(function (body) { return { ok: res.ok, body: body }; });
      })
      .then(function (result) {
        if (result.body && result.body.ok) {
          showSuccess();
        } else {
          if (statusEl) statusEl.textContent = (result.body && result.body.error) || 'Something went wrong. Please call (802) 276-8331 instead.';
          if (submitBtn) { submitBtn.classList.remove('is-loading'); submitBtn.disabled = false; }
        }
      })
      .catch(function () {
        if (statusEl) statusEl.textContent = 'Something went wrong. Please call (802) 276-8331 instead.';
        if (submitBtn) { submitBtn.classList.remove('is-loading'); submitBtn.disabled = false; }
      });
  });

  function showSuccess() {
    form.hidden = true;
    if (progressBar) progressBar.hidden = true;
    if (successBox) {
      successBox.hidden = false;
      requestAnimationFrame(function () { successBox.classList.add('is-visible'); });
    }
  }

  /* Defensive init: guarantee the current step is visible regardless of
     what the markup shipped with. This is what was missing before — nothing
     ever set the initial active step, so every .demo-step stayed
     display:none from the CSS default and the wizard looked empty. */
  stepsWrap.querySelectorAll('.demo-step').forEach(function (s) { s.classList.remove('is-active'); });
  var initialStep = getStepEl(currentStep);
  if (initialStep) initialStep.classList.add('is-active');

  updateProgress();
  updateNavButtons();
});
