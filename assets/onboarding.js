(function () {
    'use strict';

    var accountTypeLabels = {
        individual: 'Individual',
        company: 'Company'
    };

    var roleLabels = {
        owner: 'Founder / Owner',
        investor: 'Investor',
        ceo: 'CEO / Managing Director',
        cfo: 'CFO / Finance',
        investment_manager: 'Investment Manager',
        individual_investor: 'Individual Investor',
        broker: 'Broker / Advisor',
        advisor: 'Advisor / Consultant',
        other: 'Other'
    };

    var sizeLabels = {
        '1-10': '1-10 employees',
        '11-50': '11-50 employees',
        '51-200': '51-200 employees',
        '201-1000': '201-1000 employees',
        '1000+': '1000+ employees'
    };

    var goalLabels = {
        buy: 'Buy a Business',
        sell: 'Sell a Business',
        raise: 'Raise Investment',
        invest: 'Invest in Startups',
        franchise: 'Franchise',
        advisory: 'Advisory Services'
    };

    var provinceLabels = {
        Koshi: 'Koshi',
        Madhesh: 'Madhesh',
        Bagmati: 'Bagmati',
        Gandaki: 'Gandaki',
        Lumbini: 'Lumbini',
        Karnali: 'Karnali',
        Sudurpashchim: 'Sudurpashchim'
    };

    var form, currentStep, totalSteps, isTransitioning;

    function getFieldValue(name) {
        var input = form.querySelector('[name="' + name + '"]');
        if (!input) return '';
        if (input.type === 'radio') {
            var checked = form.querySelector('[name="' + name + '"]:checked');
            return checked ? checked.value : '';
        }
        if (input.type === 'checkbox') {
            return input.checked ? input.value : '';
        }
        return input.value;
    }

    function showError(field, msg) {
        var input = form.querySelector('[name="' + field + '"]');
        if (!input) return;
        var group = input.closest('.input-group');
        if (!group) return;
        group.classList.add('has-error');
        var errEl = group.querySelector('.field-error');
        if (errEl) errEl.textContent = msg;
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

    function isCompany() {
        return getFieldValue('account_type') === 'company';
    }

    function validateStep(step) {
        clearErrors(step);
        var valid = true;

        if (step === 1) {
            var name = getFieldValue('name');
            if (!name) { showError('name', 'Full name is required.'); valid = false; }

            var email = getFieldValue('email');
            if (!email) { showError('email', 'Email is required.'); valid = false; }
            else if (email.indexOf('@') === -1 || email.indexOf('.') === -1) { showError('email', 'Please enter a valid email.'); valid = false; }

            var pw = getFieldValue('password');
            if (!pw) { showError('password', 'Password is required.'); valid = false; }
            else if (pw.length < 8) { showError('password', 'Password must be at least 8 characters.'); valid = false; }
        } else if (step === 2) {
            if (isCompany()) {
                var company = getFieldValue('company');
                if (!company) { showError('company', 'Company name is required.'); valid = false; }

                var size = getFieldValue('size');
                if (!size) { showError('size', 'Please select company size.'); valid = false; }
            }

            var role = getFieldValue('role');
            if (!role) { showError('role', 'Please select a role.'); valid = false; }

            var phone = getFieldValue('phone');
            if (phone && !/^[\d\s\-\+\(\)]{7,20}$/.test(phone)) { showError('phone', 'Please enter a valid phone number.'); valid = false; }
        } else if (step === 3) {
            var goal = getFieldValue('goal');
            if (!goal) { showError('goal', 'Please select a goal.'); valid = false; }
        } else if (step === 4) {
            var agree = form.querySelector('[name="agree"]');
            if (!agree || !agree.checked) { showError('agree', 'You must agree to the terms.'); valid = false; }
        }

        return valid;
    }

    function updateProgress(step) {
        var segments = document.querySelectorAll('.step-segment');
        segments.forEach(function (seg) {
            var s = parseInt(seg.getAttribute('data-step'), 10);
            seg.classList.remove('active', 'completed');
            if (s < step) seg.classList.add('completed');
            if (s === step) seg.classList.add('active');
        });

        var fills = document.querySelectorAll('.step-line-fill');
        fills.forEach(function (fill, i) {
            var lineIndex = i + 1;
            fill.style.width = lineIndex < step ? '100%' : '0%';
        });

        var progressBar = document.querySelector('.onboarding-progress[role="progressbar"]');
        if (progressBar) {
            progressBar.setAttribute('aria-valuenow', step);
        }
    }

    function populateReview() {
        var accType = getFieldValue('account_type');
        var isComp = accType === 'company';

        var phoneVal = getFieldValue('phone');
        var provVal = getFieldValue('province');
        var distVal = getFieldValue('district');

        var data = {
            name: getFieldValue('name'),
            email: getFieldValue('email'),
            account_type: accountTypeLabels[accType] || accType,
            company: isComp ? getFieldValue('company') : '—',
            role: roleLabels[getFieldValue('role')] || getFieldValue('role'),
            size: isComp ? (sizeLabels[getFieldValue('size')] || getFieldValue('size')) : '—',
            phone: phoneVal || '—',
            province: provVal ? (provinceLabels[provVal] || provVal) : '—',
            district: distVal || '—',
            goal: goalLabels[getFieldValue('goal')] || getFieldValue('goal')
        };

        var notifyInput = form.querySelector('[name="notifications"]');
        data.notifications = notifyInput && notifyInput.checked ? 'Yes' : 'No';

        var reviewEls = form.querySelectorAll('.review-value');
        reviewEls.forEach(function (el) {
            var field = el.getAttribute('data-field');
            if (data.hasOwnProperty(field)) {
                el.textContent = data[field];
            }
        });

        var companyReviews = form.querySelectorAll('.company-review');
        companyReviews.forEach(function (el) {
            el.style.display = isComp ? '' : 'none';
        });
    }

    function updateNavButtons() {
        var btnBack = document.getElementById('btn-back');
        var btnNext = document.getElementById('btn-next');
        var btnSubmit = document.getElementById('btn-submit');

        btnBack.style.visibility = currentStep === 1 ? 'hidden' : 'visible';

        if (currentStep === totalSteps) {
            btnNext.style.display = 'none';
            btnSubmit.style.display = 'inline-flex';
        } else {
            btnNext.style.display = 'inline-flex';
            btnSubmit.style.display = 'none';
        }
    }

    function transitionTo(newStep) {
        if (isTransitioning) return;
        isTransitioning = true;

        var currentPanel = form.querySelector('.step-panel[data-step="' + currentStep + '"]');
        var nextPanel = form.querySelector('.step-panel[data-step="' + newStep + '"]');
        if (!currentPanel || !nextPanel) { isTransitioning = false; return; }

        var forward = newStep > currentStep;
        var exitX = forward ? '-30px' : '30px';
        var entryStart = forward ? '30px' : '-30px';

        nextPanel.style.display = 'block';
        nextPanel.style.transform = 'translateX(' + entryStart + ')';
        nextPanel.style.opacity = '0';

        void nextPanel.offsetWidth;

        currentPanel.style.transition = 'transform 180ms cubic-bezier(0.23, 1, 0.32, 1), opacity 180ms cubic-bezier(0.23, 1, 0.32, 1)';
        currentPanel.style.transform = 'translateX(' + exitX + ')';
        currentPanel.style.opacity = '0';

        nextPanel.style.transition = 'transform 250ms cubic-bezier(0.23, 1, 0.32, 1), opacity 250ms cubic-bezier(0.23, 1, 0.32, 1)';
        nextPanel.style.transform = 'translateX(0)';
        nextPanel.style.opacity = '1';

        currentStep = newStep;
        updateProgress(currentStep);
        updateNavButtons();

        if (currentStep === 4) {
            populateReview();
        }

        setTimeout(function () {
            if (currentPanel.style.display !== 'none') {
                currentPanel.style.display = 'none';
            }
            currentPanel.style.transition = '';
            currentPanel.style.transform = '';
            currentPanel.style.opacity = '';
            nextPanel.style.transition = '';
            nextPanel.style.transform = '';
            nextPanel.style.opacity = '';
            isTransitioning = false;
        }, 300);
    }

    function goToStep(step) {
        if (step < 1 || step > totalSteps) return;
        transitionTo(step);
    }

    function validateField(field) {
        var value = getFieldValue(field);
        var msg = '';

        if (field === 'name' && !value) msg = 'Full name is required.';
        else if (field === 'email') {
            if (!value) msg = 'Email is required.';
            else if (value.indexOf('@') === -1 || value.indexOf('.') === -1) msg = 'Please enter a valid email.';
        } else if (field === 'password') {
            if (!value) msg = 'Password is required.';
            else if (value.length < 8) msg = 'Password must be at least 8 characters.';
        } else if (field === 'company' && isCompany() && !value) msg = 'Company name is required.';
        else if (field === 'role' && !value) msg = 'Please select a role.';
        else if (field === 'size' && isCompany() && !value) msg = 'Please select company size.';
        else if (field === 'phone' && value && !/^[\d\s\-\+\(\)]{7,20}$/.test(value)) msg = 'Please enter a valid phone number.';

        if (msg) {
            showError(field, msg);
        } else {
            var input = form.querySelector('[name="' + field + '"]');
            if (input) {
                var group = input.closest('.input-group');
                if (group) group.classList.remove('has-error');
            }
        }
    }

    function initGoalCards() {
        var cards = form.querySelectorAll('.goal-card');
        cards.forEach(function (card) {
            card.addEventListener('click', function () {
                var radio = this.querySelector('input[type="radio"]');
                if (!radio) return;
                radio.checked = true;
                cards.forEach(function (c) { c.classList.remove('selected'); });
                this.classList.add('selected');
            });
        });
    }

    function initPasswordToggle() {
        var pwInput = form.querySelector('.pw-wrap input[type="password"]');
        if (!pwInput) return;
        if (pwInput.dataset.pwToggle) return;
        pwInput.dataset.pwToggle = '1';

        var wrap = pwInput.closest('.pw-wrap');
        if (!wrap) return;

        var btn = document.createElement('button');
        btn.type = 'button';
        btn.className = 'pw-toggle';
        btn.setAttribute('aria-label', 'Show password');

        var eyeOff = '<svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"/><line x1="1" y1="1" x2="23" y2="23"/></svg>';
        var eyeOpen = '<svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-7 11-7 11 7 11 7-4 7-11 7-11-7-11-7z"/><circle cx="12" cy="12" r="3"/></svg>';

        btn.innerHTML = eyeOff;
        wrap.appendChild(btn);

        btn.addEventListener('click', function () {
            var show = pwInput.type === 'password';
            pwInput.type = show ? 'text' : 'password';
            btn.innerHTML = show ? eyeOpen : eyeOff;
            btn.setAttribute('aria-label', show ? 'Hide password' : 'Show password');
        });
    }

    function initAccountTypeToggle() {
        var radios = form.querySelectorAll('[name="account_type"]');
        radios.forEach(function (radio) {
            radio.addEventListener('change', function () {
                var isComp = this.value === 'company';

                var typeOptions = form.querySelectorAll('.type-option');
                typeOptions.forEach(function (o) { o.classList.remove('selected'); });
                this.closest('.type-option').classList.add('selected');

                var companyFields = form.querySelector('.company-fields');
                if (companyFields) {
                    companyFields.style.display = isComp ? '' : 'none';
                }

                var companyInput = form.querySelector('[name="company"]');
                var sizeInput = form.querySelector('[name="size"]');
                if (companyInput) companyInput.required = isComp;
                if (sizeInput) sizeInput.required = isComp;

                ['company', 'size'].forEach(function (f) {
                    var inp = form.querySelector('[name="' + f + '"]');
                    if (inp) {
                        var g = inp.closest('.input-group');
                        if (g) g.classList.remove('has-error');
                    }
                });
            });
        });
    }

    function initBlurValidation() {
        var inputs = form.querySelectorAll('input, select');
        inputs.forEach(function (input) {
            input.addEventListener('blur', function () {
                var field = this.getAttribute('name');
                if (!field) return;
                if (field === 'goal' || field === 'agree' || field === 'notifications' || field === 'updates') return;
                validateField(field);
            });
        });
    }

    function init() {
        form = document.getElementById('onboarding-form');
        if (!form) return;

        currentStep = 1;
        totalSteps = 4;
        isTransitioning = false;

        updateProgress(currentStep);
        updateNavButtons();
        initGoalCards();
        initPasswordToggle();
        initAccountTypeToggle();
        initBlurValidation();

        document.getElementById('btn-next').addEventListener('click', function () {
            if (validateStep(currentStep)) {
                goToStep(currentStep + 1);
            }
        });

        document.getElementById('btn-back').addEventListener('click', function () {
            goToStep(currentStep - 1);
        });

        form.addEventListener('submit', function (e) {
            if (currentStep === 4) {
                var agree = form.querySelector('[name="agree"]');
                if (!agree || !agree.checked) {
                    e.preventDefault();
                    var overlay = document.createElement('div');
                    overlay.className = 'modal-overlay';
                    overlay.onclick = function (ev) { if (ev.target === overlay) overlay.remove(); };
                    overlay.innerHTML =
                        '<div class="modal-content" style="max-width:420px;text-align:center;">' +
                            '<button class="modal-close" onclick="this.closest(\'.modal-overlay\').remove()">&times;</button>' +
                            '<div style="margin:1rem 0 0.5rem;">' +
                                '<svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="var(--color-primary)" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/><line x1="12" y1="8" x2="12" y2="12"/><circle cx="12" cy="16" r="0.5" fill="currentColor"/></svg>' +
                            '</div>' +
                            '<h3 style="margin:0 0 0.5rem;">Terms & Conditions Required</h3>' +
                            '<p style="color:var(--dash-ink-soft);font-size:0.9rem;margin:0 0 1.5rem;">Please agree to the Terms of Service and Privacy Policy before creating your account.</p>' +
                            '<button class="btn btn-primary" onclick="this.closest(\'.modal-overlay\').remove()">Got it</button>' +
                        '</div>';
                    document.body.appendChild(overlay);
                }
            }
        });
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }

})();
