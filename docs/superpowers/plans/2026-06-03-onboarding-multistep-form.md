# Multi-Step Onboarding Form Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Build a 4-step onboarding wizard (`/onboarding`) with smooth client-side transitions and server-side validation, using vanilla PHP + vanilla JS.

**Architecture:** Hybrid — PHP renders all 4 steps in one page; JS controls step visibility, validation, and transitions. Final submit sends all data via `fetch` POST. JS-disabled fallback uses per-step POST (same as existing signup pattern).

**Tech Stack:** PHP 8.2+ (no framework), MySQL 8.4, Vanilla JS, CSS custom properties

---

## File Structure

| File | Action | Responsibility |
|---|---|---|
| `index.php` | Modify (add route) | Route `/onboarding` to `pages/onboarding.php` |
| `pages/onboarding.php` | **Create** | Page shell, 4 step render functions, POST handler |
| `assets/onboarding.js` | **Create** | Step navigation, validation, transitions, progress bar |
| `assets/styles.css` | Append | Step progress bar, panels, animations |

---

### Task 1: Add Route

**Files:**
- Modify: `index.php:68` (insert before the `http_response_code` / 404 block)

- [ ] **Step 1: Add route entry**

Insert `'/onboarding' => 'pages/onboarding.php',` into the `$routes` array in `index.php`. Place it after `/business-valuation` (alphabetical location) or at the end before the API routes — whichever keeps the array readable. Pick: after `/my-saved` and before `/admin` (near other public pages).

Look at the current `$routes` array in `C:\Apache24\htdocs\assan\index.php` around line 50-68 to find the right spot, then add:

```php
'/onboarding'                   => 'pages/onboarding.php',
```

- [ ] **Step 2: Verify route**

Run: `php -l index.php`
Expected: `No syntax errors detected`

---

### Task 2: Create `pages/onboarding.php`

**Files:**
- Create: `pages/onboarding.php`

This is the main file. It follows the public-page template (bootstrap, header, content, footer).

- [ ] **Step 1: Create the page shell with POST handler**

```php
<?php
require __DIR__ . '/../config/bootstrap.php';

$pageTitle = 'Get Started — Asaan Capital Ltd';
$pageDescription = 'Complete your onboarding to start connecting with businesses and investors.';
$forcePublicHeader = true;

$errors = [];
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();

    $name       = trim($_POST['name'] ?? '');
    $email      = trim($_POST['email'] ?? '');
    $password   = $_POST['password'] ?? '';
    $company    = trim($_POST['company'] ?? '');
    $role       = $_POST['role'] ?? '';
    $size       = $_POST['size'] ?? '';
    $goal       = $_POST['goal'] ?? '';
    $notify     = $_POST['notifications'] ?? '';
    $agree      = $_POST['agree'] ?? '';

    // Validate name
    if ($name === '') $errors['name'] = 'Full name is required.';
    // Validate email
    if ($email === '') $errors['email'] = 'Email is required.';
    elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors['email'] = 'Please enter a valid email.';
    // Validate password
    if ($password === '') $errors['password'] = 'Password is required.';
    elseif (strlen($password) < 8) $errors['password'] = 'Password must be at least 8 characters.';
    // Validate company
    if ($company === '') $errors['company'] = 'Company name is required.';
    // Validate role
    $validRoles = ['owner', 'ceo', 'cfo', 'investment_manager', 'broker', 'other'];
    if (!in_array($role, $validRoles)) $errors['role'] = 'Please select a role.';
    // Validate size
    $validSizes = ['1-10', '11-50', '51-200', '201-1000', '1000+'];
    if (!in_array($size, $validSizes)) $errors['size'] = 'Please select company size.';
    // Validate goal
    $validGoals = ['buy', 'sell', 'raise', 'invest', 'franchise', 'advisory'];
    if (!in_array($goal, $validGoals)) $errors['goal'] = 'Please select a goal.';
    // Validate agree
    if ($agree !== '1') $errors['agree'] = 'You must agree to the terms.';

    if (empty($errors)) {
        // Hash password
        $hash = password_hash($password, PASSWORD_BCRYPT);

        try {
            $db = db();
            $db->beginTransaction();

            $stmt = $db->prepare("INSERT INTO users (name, email, password, role, company, company_size, usage_goal, notifications, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())");
            $stmt->execute([$name, $email, $hash, $role, $company, $size, $goal, $notify]);

            $db->commit();

            flash_set('success', 'Welcome to Asaan Capital! Your account has been created.');
            redirect('/dashboard');
        } catch (PDOException $e) {
            $db->rollBack();
            if ($e->getCode() == 23000) {
                $errors['email'] = 'This email is already registered.';
            } else {
                $errors[] = 'An error occurred. Please try again.';
            }
        }
    }
}

require __DIR__ . '/../includes/header.php';
?>

<main class="onboarding-page">
  <div class="onboarding-container">

    <!-- Progress Indicator -->
    <div class="onboarding-progress" role="progressbar" aria-valuenow="1" aria-valuemin="1" aria-valuemax="4">
      <div class="step-indicator">
        <div class="step-segment completed" data-step="1">
          <div class="step-number"><span class="step-check">&#10003;</span><span class="step-num">1</span></div>
          <span class="step-label">Account</span>
        </div>
        <div class="step-line"><div class="step-line-fill" style="width:0%"></div></div>
        <div class="step-segment active" data-step="2">
          <div class="step-number">2</div>
          <span class="step-label">Company</span>
        </div>
        <div class="step-line"><div class="step-line-fill" style="width:0%"></div></div>
        <div class="step-segment" data-step="3">
          <div class="step-number">3</div>
          <span class="step-label">Preferences</span>
        </div>
        <div class="step-line"><div class="step-line-fill" style="width:0%"></div></div>
        <div class="step-segment" data-step="4">
          <div class="step-number">4</div>
          <span class="step-label">Review</span>
        </div>
      </div>
    </div>

    <!-- Step panels -->
    <form id="onboarding-form" method="POST" action="/onboarding" novalidate>
      <input type="hidden" name="_csrf" value="<?= csrf_token() ?>">

      <!-- Step 1: Account Setup -->
      <div class="step-panel" data-step="1">
        <h2 class="step-title">Create your account</h2>
        <p class="step-subtitle">Get started with your free account.</p>

        <div class="input-group <?= isset($errors['name']) ? 'has-error' : '' ?>">
          <label for="name">Full name</label>
          <input type="text" id="name" name="name" value="<?= e($_POST['name'] ?? '') ?>" placeholder="John Doe" required autocomplete="name">
          <span class="field-error"><?= e($errors['name'] ?? '') ?></span>
        </div>

        <div class="input-group <?= isset($errors['email']) ? 'has-error' : '' ?>">
          <label for="email">Email address</label>
          <input type="email" id="email" name="email" value="<?= e($_POST['email'] ?? '') ?>" placeholder="john@company.com" required autocomplete="email">
          <span class="field-error"><?= e($errors['email'] ?? '') ?></span>
        </div>

        <div class="input-group <?= isset($errors['password']) ? 'has-error' : '' ?>">
          <label for="password">Password</label>
          <div class="pw-wrap">
            <input type="password" id="password" name="password" placeholder="At least 8 characters" required minlength="8" autocomplete="new-password">
          </div>
          <span class="field-error"><?= e($errors['password'] ?? '') ?></span>
        </div>
      </div>

      <!-- Step 2: Company Details -->
      <div class="step-panel" data-step="2" style="display:none">
        <h2 class="step-title">Tell us about your company</h2>
        <p class="step-subtitle">We'll tailor the experience to your business.</p>

        <div class="input-group <?= isset($errors['company']) ? 'has-error' : '' ?>">
          <label for="company">Company name</label>
          <input type="text" id="company" name="company" value="<?= e($_POST['company'] ?? '') ?>" placeholder="Acme Inc." required>
          <span class="field-error"><?= e($errors['company'] ?? '') ?></span>
        </div>

        <div class="input-group <?= isset($errors['role']) ? 'has-error' : '' ?>">
          <label for="role">Your role</label>
          <select id="role" name="role" required>
            <option value="">Select a role</option>
            <option value="owner" <?= ($_POST['role'] ?? '') === 'owner' ? 'selected' : '' ?>>Owner / Founder</option>
            <option value="ceo" <?= ($_POST['role'] ?? '') === 'ceo' ? 'selected' : '' ?>>CEO / Managing Director</option>
            <option value="cfo" <?= ($_POST['role'] ?? '') === 'cfo' ? 'selected' : '' ?>>CFO / Finance</option>
            <option value="investment_manager" <?= ($_POST['role'] ?? '') === 'investment_manager' ? 'selected' : '' ?>>Investment Manager</option>
            <option value="broker" <?= ($_POST['role'] ?? '') === 'broker' ? 'selected' : '' ?>>Broker / Advisor</option>
            <option value="other" <?= ($_POST['role'] ?? '') === 'other' ? 'selected' : '' ?>>Other</option>
          </select>
          <span class="field-error"><?= e($errors['role'] ?? '') ?></span>
        </div>

        <div class="input-group <?= isset($errors['size']) ? 'has-error' : '' ?>">
          <label for="size">Company size</label>
          <select id="size" name="size" required>
            <option value="">Select size</option>
            <option value="1-10" <?= ($_POST['size'] ?? '') === '1-10' ? 'selected' : '' ?>>1-10 employees</option>
            <option value="11-50" <?= ($_POST['size'] ?? '') === '11-50' ? 'selected' : '' ?>>11-50 employees</option>
            <option value="51-200" <?= ($_POST['size'] ?? '') === '51-200' ? 'selected' : '' ?>>51-200 employees</option>
            <option value="201-1000" <?= ($_POST['size'] ?? '') === '201-1000' ? 'selected' : '' ?>>201-1000 employees</option>
            <option value="1000+" <?= ($_POST['size'] ?? '') === '1000+' ? 'selected' : '' ?>>1000+ employees</option>
          </select>
          <span class="field-error"><?= e($errors['size'] ?? '') ?></span>
        </div>
      </div>

      <!-- Step 3: Preferences -->
      <div class="step-panel" data-step="3" style="display:none">
        <h2 class="step-title">Set your preferences</h2>
        <p class="step-subtitle">Help us recommend the right opportunities.</p>

        <div class="input-group <?= isset($errors['goal']) ? 'has-error' : '' ?>">
          <label>What brings you here?</label>
          <div class="goal-grid">
            <?php
            $goals = [
              'buy' => 'Buy a Business',
              'sell' => 'Sell a Business',
              'raise' => 'Raise Investment',
              'invest' => 'Invest in Startups',
              'franchise' => 'Franchise',
              'advisory' => 'Advisory Services',
            ];
            $selectedGoal = $_POST['goal'] ?? '';
            foreach ($goals as $val => $label):
            ?>
            <label class="goal-card <?= $selectedGoal === $val ? 'selected' : '' ?>">
              <input type="radio" name="goal" value="<?= $val ?>" <?= $selectedGoal === $val ? 'checked' : '' ?> required>
              <span class="goal-label"><?= $label ?></span>
            </label>
            <?php endforeach; ?>
          </div>
          <span class="field-error"><?= e($errors['goal'] ?? '') ?></span>
        </div>

        <div class="input-group">
          <label class="checkbox-label">
            <input type="checkbox" name="notifications" value="email" <?= ($_POST['notifications'] ?? '') === 'email' ? 'checked' : '' ?>>
            <span>Send me email notifications about matches and messages</span>
          </label>
        </div>

        <div class="input-group">
          <label class="checkbox-label">
            <input type="checkbox" name="updates" value="1" <?= ($_POST['updates'] ?? '') === '1' ? 'checked' : '' ?>>
            <span>Keep me updated on product news and features</span>
          </label>
        </div>
      </div>

      <!-- Step 4: Review & Submit -->
      <div class="step-panel" data-step="4" style="display:none">
        <h2 class="step-title">Review your information</h2>
        <p class="step-subtitle">Please confirm everything looks correct before submitting.</p>

        <div class="review-summary">
          <div class="review-row">
            <span class="review-label">Full name</span>
            <span class="review-value" data-field="name"></span>
          </div>
          <div class="review-row">
            <span class="review-label">Email</span>
            <span class="review-value" data-field="email"></span>
          </div>
          <div class="review-row">
            <span class="review-label">Company</span>
            <span class="review-value" data-field="company"></span>
          </div>
          <div class="review-row">
            <span class="review-label">Role</span>
            <span class="review-value" data-field="role"></span>
          </div>
          <div class="review-row">
            <span class="review-label">Company size</span>
            <span class="review-value" data-field="size"></span>
          </div>
          <div class="review-row">
            <span class="review-label">Goal</span>
            <span class="review-value" data-field="goal"></span>
          </div>
          <div class="review-row">
            <span class="review-label">Notifications</span>
            <span class="review-value" data-field="notifications"></span>
          </div>
        </div>

        <div class="input-group <?= isset($errors['agree']) ? 'has-error' : '' ?>">
          <label class="checkbox-label">
            <input type="checkbox" name="agree" value="1" required>
            <span>I agree to the <a href="/legal" target="_blank">Terms of Service</a> and <a href="/legal" target="_blank">Privacy Policy</a></span>
          </label>
          <span class="field-error"><?= e($errors['agree'] ?? '') ?></span>
        </div>
      </div>

      <!-- Navigation buttons -->
      <div class="step-nav">
        <button type="button" class="btn btn-outline btn-back" id="btn-back" style="visibility:hidden">Back</button>
        <div class="step-nav-right">
          <button type="button" class="btn btn-primary" id="btn-next">Next</button>
          <button type="submit" class="btn btn-primary" id="btn-submit" style="display:none">Create Account</button>
        </div>
      </div>
    </form>
  </div>
</main>

<script src="/assets/onboarding.js"></script>

<?php require __DIR__ . '/../includes/footer.php'; ?>
```

- [ ] **Step 2: Verify syntax**

Run: `php -l pages/onboarding.php`
Expected: `No syntax errors detected`

---

### Task 3: Create `assets/onboarding.js`

**Files:**
- Create: `assets/onboarding.js`

This handles step transitions, validation, progress indicator, and review summary population.

- [ ] **Step 1: Create the JS file**

```js
(function () {
  'use strict';

  var currentStep = 1;
  var totalSteps = 4;

  var form = document.getElementById('onboarding-form');
  if (!form) return;

  var panels = form.querySelectorAll('.step-panel');
  var segments = document.querySelectorAll('.step-segment');
  var progressBar = document.querySelector('.onboarding-progress');
  var btnNext = document.getElementById('btn-next');
  var btnBack = document.getElementById('btn-back');
  var btnSubmit = document.getElementById('btn-submit');
  var lineFills = document.querySelectorAll('.step-line-fill');

  var roleLabels = {
    owner: 'Owner / Founder',
    ceo: 'CEO / Managing Director',
    cfo: 'CFO / Finance',
    investment_manager: 'Investment Manager',
    broker: 'Broker / Advisor',
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

  function showStep(step) {
    panels.forEach(function (p) {
      p.style.display = parseInt(p.dataset.step) === step ? '' : 'none';
    });
    updateProgress(step);
    updateButtons(step);
    window.scrollTo({ top: 0, behavior: 'smooth' });
  }

  function updateProgress(step) {
    segments.forEach(function (seg) {
      var s = parseInt(seg.dataset.step);
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
    btnBack.style.visibility = step === 1 ? 'hidden' : 'visible';
    if (step === totalSteps) {
      btnNext.style.display = 'none';
      btnSubmit.style.display = '';
    } else {
      btnNext.style.display = '';
      btnSubmit.style.display = 'none';
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
      return el.checked ? el.value : '';
    }
    return el.value;
  }

  // Validation rules per step
  var validators = {
    1: function () {
      var errors = [];
      var name = getFieldValue('name');
      var email = getFieldValue('email');
      var password = getFieldValue('password');

      clearErrors(1);

      if (!name.trim()) { errors.push('name'); showError('name', 'Full name is required.'); }
      if (!email.trim()) { errors.push('email'); showError('email', 'Email is required.'); }
      else if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) { errors.push('email'); showError('email', 'Please enter a valid email.'); }
      if (!password) { errors.push('password'); showError('password', 'Password is required.'); }
      else if (password.length < 8) { errors.push('password'); showError('password', 'Password must be at least 8 characters.'); }

      return errors.length === 0;
    },
    2: function () {
      var errors = [];
      var company = getFieldValue('company');
      var role = getFieldValue('role');
      var size = getFieldValue('size');

      clearErrors(2);

      if (!company.trim()) { errors.push('company'); showError('company', 'Company name is required.'); }
      if (!role) { errors.push('role'); showError('role', 'Please select a role.'); }
      if (!size) { errors.push('size'); showError('size', 'Please select company size.'); }

      return errors.length === 0;
    },
    3: function () {
      var errors = [];
      var goal = getFieldValue('goal');

      clearErrors(3);

      if (!goal) { errors.push('goal'); showError('goal', 'Please select a goal.'); }

      return errors.length === 0;
    },
    4: function () {
      var errors = [];
      var agree = form.querySelector('[name="agree"]');
      clearErrors(4);
      if (!agree || !agree.checked) {
        errors.push('agree');
        var errEl = form.querySelector('.field-error');
        if (errEl) errEl.textContent = 'You must agree to the terms.';
      }
      return errors.length === 0;
    }
  };

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

  function goNext() {
    if (validators[currentStep] && !validators[currentStep]()) {
      return;
    }
    if (currentStep < totalSteps) {
      var currentPanel = form.querySelector('.step-panel[data-step="' + currentStep + '"]');
      currentStep++;
      var nextPanel = form.querySelector('.step-panel[data-step="' + currentStep + '"]');
      animateTransition(currentPanel, nextPanel, 'left');
      showStep(currentStep);
    }
  }

  function goBack() {
    if (currentStep > 1) {
      var currentPanel = form.querySelector('.step-panel[data-step="' + currentStep + '"]');
      currentStep--;
      var prevPanel = form.querySelector('.step-panel[data-step="' + currentStep + '"]');
      animateTransition(currentPanel, prevPanel, 'right');
      showStep(currentStep);
    }
  }

  function animateTransition(fromEl, toEl, dir) {
    if (!fromEl || !toEl) return;
    var offset = dir === 'left' ? '-30px' : '30px';
    toEl.style.display = '';
    toEl.style.transform = 'translateX(' + offset + ')';
    toEl.style.opacity = '0';
    toEl.style.transition = 'none';

    requestAnimationFrame(function () {
      fromEl.style.transform = 'translateX(' + (dir === 'left' ? '30px' : '-30px') + ')';
      fromEl.style.opacity = '0';
      fromEl.style.transition = 'transform 250ms cubic-bezier(0.4,0,0.2,1), opacity 250ms cubic-bezier(0.4,0,0.2,1)';

      toEl.style.transform = 'translateX(0)';
      toEl.style.opacity = '1';
      toEl.style.transition = 'transform 250ms cubic-bezier(0.4,0,0.2,1), opacity 250ms cubic-bezier(0.4,0,0.2,1)';
    });

    setTimeout(function () {
      fromEl.style.transform = '';
      fromEl.style.opacity = '';
      fromEl.style.transition = '';
      toEl.style.transform = '';
      toEl.style.opacity = '';
      toEl.style.transition = '';
    }, 300);
  }

  // Populate review step
  function populateReview() {
    var name = getFieldValue('name');
    var email = getFieldValue('email');
    var company = getFieldValue('company');
    var roleVal = getFieldValue('role');
    var sizeVal = getFieldValue('size');
    var goalVal = getFieldValue('goal');
    var notify = form.querySelector('[name="notifications"]');

    setReviewValue('name', name);
    setReviewValue('email', email);
    setReviewValue('company', company);
    setReviewValue('role', roleLabels[roleVal] || roleVal);
    setReviewValue('size', sizeLabels[sizeVal] || sizeVal);
    setReviewValue('goal', goalLabels[goalVal] || goalVal);
    setReviewValue('notifications', notify && notify.checked ? 'Email notifications enabled' : 'Disabled');
  }

  function setReviewValue(field, value) {
    var el = document.querySelector('.review-value[data-field="' + field + '"]');
    if (el) el.textContent = value;
  }

  // Inline validation on blur
  function initInlineValidation() {
    form.querySelectorAll('.step-panel input, .step-panel select').forEach(function (el) {
      el.addEventListener('blur', function () {
        var panel = el.closest('.step-panel');
        if (!panel) return;
        var step = parseInt(panel.dataset.step);
        // Re-run validation for current step, but only clear errors for this field
        var name = el.getAttribute('name');
        if (name && validators[step]) {
          // Simple per-field check
          if (el.value.trim() === '' && el.hasAttribute('required')) {
            showError(name, 'This field is required.');
          } else {
            var group = el.closest('.input-group');
            if (group) {
              group.classList.remove('has-error');
              var errEl = group.querySelector('.field-error');
              if (errEl) errEl.textContent = '';
            }
          }
        }
      });
    });
  }

  // Goal card selection
  function initGoalCards() {
    document.querySelectorAll('.goal-card').forEach(function (card) {
      card.addEventListener('click', function () {
        this.querySelector('input[type="radio"]').checked = true;
        document.querySelectorAll('.goal-card').forEach(function (c) {
          c.classList.remove('selected');
        });
        this.classList.add('selected');
      });
    });
  }

  // Password toggle
  function initPasswordToggle() {
    var pwInput = document.getElementById('password');
    if (!pwInput) return;
    var wrap = pwInput.closest('.pw-wrap');
    if (!wrap) return;

    var btn = document.createElement('button');
    btn.type = 'button';
    btn.className = 'pw-toggle';
    btn.setAttribute('aria-label', 'Show password');
    btn.innerHTML = '<svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-7 11-7 11 7 11 7-4 7-11 7-11-7-11-7z"/><circle cx="12" cy="12" r="3"/></svg>';
    wrap.appendChild(btn);

    btn.addEventListener('click', function () {
      var show = pwInput.type === 'password';
      pwInput.type = show ? 'text' : 'password';
      btn.innerHTML = show
        ? '<svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"/><line x1="1" y1="1" x2="23" y2="23"/></svg>'
        : '<svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-7 11-7 11 7 11 7-4 7-11 7-11-7-11-7z"/><circle cx="12" cy="12" r="3"/></svg>';
      btn.setAttribute('aria-label', show ? 'Hide password' : 'Show password');
    });
  }

  // Initial render
  function init() {
    showStep(1);
    initInlineValidation();
    initGoalCards();
    initPasswordToggle();

    btnNext.addEventListener('click', function (e) {
      e.preventDefault();
      if (currentStep === totalSteps - 1) {
        // Populate review before going to step 4
        populateReview();
      }
      goNext();
    });

    btnBack.addEventListener('click', function (e) {
      e.preventDefault();
      goBack();
    });
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init);
  } else {
    init();
  }
})();
```

- [ ] **Step 2: Verify no syntax errors**

Run: `php -l assets/onboarding.js` (won't detect JS errors, but confirm it parses)
Expected: `No syntax errors detected`

---

### Task 4: Add CSS Styles

**Files:**
- Modify: `assets/styles.css` (append at end of file)

- [ ] **Step 1: Append onboarding styles**

Read the last 20 lines of `assets/styles.css` to find the end of the file. Then append the following styles:

```css
/* =========================================================================
 * Onboarding Multi-Step Form
 * ========================================================================= */

.onboarding-page {
  min-height: 80vh;
  display: flex;
  align-items: flex-start;
  justify-content: center;
  padding: var(--space-8) 1.5rem;
  background: var(--color-bg-soft);
}

.onboarding-container {
  width: 100%;
  max-width: 600px;
  background: var(--color-bg);
  border-radius: var(--radius-lg);
  box-shadow: var(--shadow-md);
  padding: var(--space-8);
}

/* Progress Indicator */
.onboarding-progress {
  margin-bottom: var(--space-8);
}

.step-indicator {
  display: flex;
  align-items: center;
  justify-content: space-between;
}

.step-segment {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 6px;
  position: relative;
  cursor: default;
}

.step-number {
  width: 36px;
  height: 36px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 0.85rem;
  font-weight: 700;
  font-family: var(--font-heading);
  background: var(--color-border);
  color: var(--color-text-muted);
  transition: background var(--motion-base) var(--ease-standard), color var(--motion-base) var(--ease-standard);
}

.step-segment.active .step-number {
  background: var(--color-primary);
  color: var(--color-text-inverse);
  box-shadow: 0 0 0 4px rgba(107, 29, 34, 0.15);
}

.step-segment.completed .step-number {
  background: var(--color-success);
  color: var(--color-text-inverse);
}

.step-segment.completed .step-num {
  display: none;
}

.step-check {
  display: none;
}

.step-segment.completed .step-check {
  display: inline;
}

.step-label {
  font-size: var(--text-xs);
  font-weight: 600;
  color: var(--color-text-muted);
  text-transform: uppercase;
  letter-spacing: 0.5px;
  transition: color var(--motion-fast) var(--ease-standard);
}

.step-segment.active .step-label {
  color: var(--color-primary);
}

.step-segment.completed .step-label {
  color: var(--color-success);
}

.step-line {
  flex: 1;
  height: 2px;
  background: var(--color-border);
  margin: 0 8px;
  position: relative;
  top: -14px;
}

.step-line-fill {
  height: 100%;
  background: var(--color-success);
  transition: width var(--motion-slow) var(--ease-emphasis);
  width: 0%;
}

/* Step Panels */
.step-panel {
  animation: none;
}

.step-title {
  font-size: var(--text-h3);
  font-weight: 800;
  margin-bottom: var(--space-1);
  text-transform: none;
  letter-spacing: -0.3px;
}

.step-subtitle {
  color: var(--color-text-muted);
  font-size: var(--text-sm);
  margin-bottom: var(--space-6);
}

/* Input Groups */
.onboarding-page .input-group {
  margin-bottom: var(--space-5);
}

.onboarding-page .input-group label {
  display: block;
  font-size: var(--text-sm);
  font-weight: 600;
  color: var(--color-text-heading);
  margin-bottom: var(--space-1);
}

.onboarding-page .input-group input[type="text"],
.onboarding-page .input-group input[type="email"],
.onboarding-page .input-group input[type="password"],
.onboarding-page .input-group select {
  width: 100%;
  padding: 12px 14px;
  border: 1.5px solid var(--color-border);
  border-radius: var(--radius-md);
  font-size: var(--text-base);
  font-family: var(--font-body);
  color: var(--color-text);
  background: var(--color-bg);
  transition: border-color var(--motion-fast) var(--ease-standard), box-shadow var(--motion-fast) var(--ease-standard);
  outline: none;
}

.onboarding-page .input-group input:focus,
.onboarding-page .input-group select:focus {
  border-color: var(--color-primary);
  box-shadow: 0 0 0 3px rgba(107, 29, 34, 0.1);
}

.onboarding-page .input-group.has-error input,
.onboarding-page .input-group.has-error select {
  border-color: var(--color-error);
  box-shadow: 0 0 0 3px rgba(152, 32, 42, 0.1);
}

.field-error {
  display: block;
  font-size: var(--text-xs);
  color: var(--color-error);
  margin-top: 4px;
  min-height: 1em;
}

/* Goal Grid */
.goal-grid {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: var(--space-2);
}

.goal-card {
  display: flex;
  align-items: center;
  justify-content: center;
  padding: var(--space-4) var(--space-3);
  border: 1.5px solid var(--color-border);
  border-radius: var(--radius-md);
  cursor: pointer;
  transition: border-color var(--motion-fast) var(--ease-standard), background var(--motion-fast) var(--ease-standard), box-shadow var(--motion-fast) var(--ease-standard);
  text-align: center;
  min-height: 60px;
}

.goal-card:hover {
  border-color: var(--color-primary);
  background: rgba(107, 29, 34, 0.03);
}

.goal-card.selected {
  border-color: var(--color-primary);
  background: rgba(107, 29, 34, 0.06);
  box-shadow: 0 0 0 3px rgba(107, 29, 34, 0.1);
}

.goal-card input[type="radio"] {
  position: absolute;
  opacity: 0;
  pointer-events: none;
}

.goal-label {
  font-size: var(--text-sm);
  font-weight: 600;
  color: var(--color-text-heading);
}

/* Checkbox */
.checkbox-label {
  display: flex !important;
  align-items: flex-start;
  gap: var(--space-2);
  cursor: pointer;
  font-weight: 400 !important;
  font-size: var(--text-sm) !important;
}

.checkbox-label input[type="checkbox"] {
  width: 18px;
  height: 18px;
  margin-top: 2px;
  accent-color: var(--color-primary);
  flex-shrink: 0;
}

/* Review Summary */
.review-summary {
  border: 1px solid var(--color-border);
  border-radius: var(--radius-md);
  overflow: hidden;
  margin-bottom: var(--space-5);
}

.review-row {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: var(--space-3) var(--space-4);
  border-bottom: 1px solid var(--color-border);
  font-size: var(--text-sm);
}

.review-row:last-child {
  border-bottom: none;
}

.review-label {
  color: var(--color-text-muted);
  font-weight: 500;
}

.review-value {
  font-weight: 600;
  color: var(--color-text-heading);
  text-align: right;
  max-width: 60%;
  word-break: break-word;
}

/* Step Navigation */
.step-nav {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-top: var(--space-6);
  padding-top: var(--space-5);
  border-top: 1px solid var(--color-border);
}

.step-nav-right {
  margin-left: auto;
}

/* Responsive */
@media (max-width: 480px) {
  .onboarding-container {
    padding: var(--space-5);
    border-radius: var(--radius-md);
  }

  .step-label {
    display: none;
  }

  .step-line {
    top: -10px;
  }

  .step-number {
    width: 28px;
    height: 28px;
    font-size: var(--text-xs);
  }

  .goal-grid {
    grid-template-columns: 1fr;
  }

  .step-title {
    font-size: var(--text-h4);
  }
}

/* Transitions for step animation */
.step-panel {
  transition: transform 250ms cubic-bezier(0.4, 0, 0.2, 1), opacity 250ms cubic-bezier(0.4, 0, 0.2, 1);
}
```

- [ ] **Step 2: Verify CSS file**

Run: `php -l assets/styles.css` — doesn't validate CSS but checks for PHP tags; this file has none, so it should just return `No syntax errors detected`.

---

### Task 5: Verify Everything Works

- [ ] **Step 1: PHP syntax check all modified files**

Run: `php -l index.php; if ($?) { php -l pages/onboarding.php; }`
Expected: Both report `No syntax errors detected`

- [ ] **Step 2: Visual check**

Open `http://localhost/assan/onboarding` in browser.
Expected: Step 1 visible, progress bar shows "1" as active, form fields render, Next button present.
