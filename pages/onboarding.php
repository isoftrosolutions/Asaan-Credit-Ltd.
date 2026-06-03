<?php
require __DIR__ . '/../config/bootstrap.php';

$pageTitle = 'Get Started — Asaan Capital Ltd';
$pageDescription = 'Complete your onboarding to start connecting with businesses and investors.';
$forcePublicHeader = true;
$onboardingPage = true;

$errors = [];

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

    if ($name === '') $errors['name'] = 'Full name is required.';

    if ($email === '') $errors['email'] = 'Email is required.';
    elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors['email'] = 'Please enter a valid email.';

    if ($password === '') $errors['password'] = 'Password is required.';
    elseif (strlen($password) < 8) $errors['password'] = 'Password must be at least 8 characters.';

    if ($company === '') $errors['company'] = 'Company name is required.';

    $validRoles = ['owner', 'ceo', 'cfo', 'investment_manager', 'broker', 'other'];
    if (!in_array($role, $validRoles)) $errors['role'] = 'Please select a role.';

    $validSizes = ['1-10', '11-50', '51-200', '201-1000', '1000+'];
    if (!in_array($size, $validSizes)) $errors['size'] = 'Please select company size.';

    $validGoals = ['buy', 'sell', 'raise', 'invest', 'franchise', 'advisory'];
    if (!in_array($goal, $validGoals)) $errors['goal'] = 'Please select a goal.';

    if ($agree !== '1') $errors['agree'] = 'You must agree to the terms.';

    if (empty($errors)) {
        $hash = password_hash($password, PASSWORD_BCRYPT);

        try {
            $db = db();
            $db->beginTransaction();

            $stmt = $db->prepare("INSERT INTO users (name, email, password, role, company_name, company_size, usage_goal, notifications, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())");
            $stmt->execute([$name, $email, $hash, $role, $company, $size, $goal, $notify]);

            $db->commit();

            flash_set('success', 'Welcome to Asaan Capital! Your account has been created.');
            redirect('/dashboard');
        } catch (PDOException $e) {
            $db->rollBack();
            if ($e->getCode() == 23000) {
                $errors['email'] = 'This email is already registered.';
            } else {
                $errors['_general'] = 'An error occurred. Please try again.';
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
        <div class="step-segment active" data-step="1">
          <div class="step-number"><span class="step-check">&#10003;</span><span class="step-num">1</span></div>
          <span class="step-label">Account</span>
        </div>
        <div class="step-line"><div class="step-line-fill" style="width:0%"></div></div>
        <div class="step-segment" data-step="2">
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
