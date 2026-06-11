(function () {
  'use strict';

  window.initFormSteps = function (config) {
    config = config || {};
    var form = config.form || document.querySelector('.form-steps');
    if (!form) return;

    var currentStep = 1;
    var totalSteps = form.querySelectorAll('.step-panel').length;
    var isTransitioning = false;

    var panels = form.querySelectorAll('.step-panel');
    var segments = document.querySelectorAll('.step-segment');
    var progressBar = document.querySelector('.form-step-progress');
    var btnNext = form.querySelector('.btn-step-next');
    var btnBack = form.querySelector('.btn-step-back');
    var btnSubmit = form.querySelector('.btn-step-submit');
    var lineFills = document.querySelectorAll('.step-line-fill');

    function showStep(step) {
      panels.forEach(function (p) {
        p.style.display = parseInt(p.dataset.step, 10) === step ? '' : 'none';
      });
      updateProgress(step);
      updateButtons(step);
    }

    function updateProgress(step) {
      segments.forEach(function (seg) {
        var s = parseInt(seg.dataset.step, 10);
        seg.classList.remove('active', 'completed');
        if (s < step) seg.classList.add('completed');
        else if (s === step) seg.classList.add('active');
      });

      lineFills.forEach(function (line) {
        var parent = line.parentElement;
        var prevSeg = parent.previousElementSibling;
        if (prevSeg && prevSeg.classList.contains('completed')) {
          line.style.width = '100%';
        } else if (prevSeg && prevSeg.classList.contains('active')) {
          line.style.width = '50%';
        } else {
          line.style.width = '0%';
        }
      });

      if (progressBar) {
        progressBar.setAttribute('aria-valuenow', step);
      }
    }

    function updateButtons(step) {
      if (btnBack) {
        btnBack.style.display = step === 1 ? 'none' : '';
      }
      if (btnNext && btnSubmit) {
        if (step === totalSteps) {
          btnNext.style.display = 'none';
          btnSubmit.style.display = '';
        } else {
          btnNext.style.display = '';
          btnSubmit.style.display = 'none';
        }
      }
    }

    function getFieldValue(name) {
      var el = form.querySelector('[name="' + name + '"]');
      if (!el) return '';
      if (el.type === 'radio') {
        var checked = form.querySelector('[name="' + name + '"]:checked');
        return checked ? checked.value : '';
      }
      if (el.type === 'checkbox') {
        return el.checked ? (el.value || '1') : '';
      }
      return el.value;
    }

    function clearErrors(step) {
      var panel = form.querySelector('.step-panel[data-step="' + step + '"]');
      if (!panel) return;
      panel.querySelectorAll('.input-group').forEach(function (g) {
        g.classList.remove('has-error');
      });
      panel.querySelectorAll('.field-error').forEach(function (e) {
        e.textContent = '';
      });
    }

    function showError(field, msg) {
      var input = form.querySelector('[name="' + field + '"]');
      if (!input) return;
      var group = input.closest('.input-group');
      if (!group) {
        var card = input.closest('.card');
        if (card) group = card;
      }
      if (!group) return;
      group.classList.add('has-error');
      var errEl = group.querySelector('.field-error');
      if (errEl) {
        errEl.textContent = msg;
      } else {
        var div = document.createElement('div');
        div.className = 'field-error';
        div.textContent = msg;
        group.appendChild(div);
      }
    }

    function validateStep(step) {
      clearErrors(step);
      if (config.validators && config.validators[step]) {
        return config.validators[step](getFieldValue, showError);
      }
      return true;
    }

    function goNext() {
      if (isTransitioning) return;
      if (currentStep >= totalSteps) return;
      if (!validateStep(currentStep)) return;

      if (config.onStepLeave) {
        config.onStepLeave(currentStep, getFieldValue);
      }

      var from = form.querySelector('.step-panel[data-step="' + currentStep + '"]');
      currentStep++;
      var to = form.querySelector('.step-panel[data-step="' + currentStep + '"]');
      animateTransition(from, to, 'left');

      if (config.onStepEnter) {
        config.onStepEnter(currentStep, getFieldValue);
      }
    }

    function goBack() {
      if (isTransitioning) return;
      if (currentStep <= 1) return;
      var from = form.querySelector('.step-panel[data-step="' + currentStep + '"]');
      currentStep--;
      var to = form.querySelector('.step-panel[data-step="' + currentStep + '"]');
      animateTransition(from, to, 'right');
    }

    function animateTransition(fromEl, toEl, dir) {
      if (!fromEl || !toEl) return;
      isTransitioning = true;

      var offset = dir === 'left' ? '30px' : '-30px';
      toEl.style.display = '';
      toEl.style.transform = 'translateX(' + offset + ')';
      toEl.style.opacity = '0';
      toEl.style.transition = 'none';

      requestAnimationFrame(function () {
        fromEl.style.transform = 'translateX(' + (dir === 'left' ? '-30px' : '30px') + ')';
        fromEl.style.opacity = '0';
        fromEl.style.transition = 'transform 220ms cubic-bezier(0.4,0,0.2,1), opacity 220ms cubic-bezier(0.4,0,0.2,1)';

        toEl.style.transform = 'translateX(0)';
        toEl.style.opacity = '1';
        toEl.style.transition = 'transform 220ms cubic-bezier(0.4,0,0.2,1), opacity 220ms cubic-bezier(0.4,0,0.2,1)';

        updateProgress(currentStep);
        updateButtons(currentStep);
      });

      setTimeout(function () {
        fromEl.style.transform = '';
        fromEl.style.opacity = '';
        fromEl.style.transition = '';
        fromEl.style.display = 'none';
        toEl.style.transform = '';
        toEl.style.opacity = '';
        toEl.style.transition = '';
        isTransitioning = false;
      }, 260);
    }

    function initBlurValidation() {
      form.querySelectorAll('input, select, textarea').forEach(function (el) {
        el.addEventListener('blur', function () {
          var panel = this.closest('.step-panel');
          if (!panel) return;
          var s = parseInt(panel.dataset.step, 10);
          if (config.onFieldBlur) {
            config.onFieldBlur(s, this.getAttribute('name'), getFieldValue, showError);
          }
        });
      });
    }

    // Init
    showStep(1);

    if (btnNext) {
      btnNext.addEventListener('click', function (e) {
        e.preventDefault();
        goNext();
      });
    }

    if (btnBack) {
      btnBack.addEventListener('click', function (e) {
        e.preventDefault();
        goBack();
      });
    }

    initBlurValidation();
  };

})();
