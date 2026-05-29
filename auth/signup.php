<?php
require __DIR__ . '/../config/bootstrap.php';

if (current_user()) {
    redirect('/dashboard');
}

$step = (int)($_POST['step'] ?? $_GET['step'] ?? 1);
$step = max(1, min(3, $step));
$errors = [];

$roles = [
    'investor'       => 'Investor / Buyer',
    'business_owner' => 'Business Owner',
    'entrepreneur'   => 'Entrepreneur',
    'franchisor'     => 'Franchisor / Brand',
    'advisor'        => 'Advisor / Broker',
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();

    if ($step === 1) {
        $email    = trim($_POST['email'] ?? '');
        $phone    = trim($_POST['phone'] ?? '');
        $password = $_POST['password'] ?? '';
        $confirm  = $_POST['confirm_password'] ?? '';

        if ($email === '') {
            $errors[] = 'Email is required.';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Please enter a valid email address.';
        } else {
            $stmt = db()->prepare('SELECT id FROM users WHERE email = ?');
            $stmt->execute([$email]);
            if ($stmt->fetch()) {
                $errors[] = 'This email is already registered.';
            }
        }

        if ($phone === '') {
            $errors[] = 'Phone number is required.';
        }

        if (strlen($password) < 8) {
            $errors[] = 'Password must be at least 8 characters.';
        }

        if ($password !== $confirm) {
            $errors[] = 'Passwords do not match.';
        }

        if (empty($errors)) {
            $_SESSION['_signup'] = [
                'email'    => $email,
                'phone'    => $phone,
                'password' => $password,
            ];
            $step = 2;
        }
    } elseif ($step === 2) {
        $name = trim($_POST['name'] ?? '');
        $role = $_POST['role'] ?? '';

        if ($name === '') {
            $errors[] = 'Full name is required.';
        }

        if (!array_key_exists($role, $roles)) {
            $errors[] = 'Please select a valid role.';
        }

        if (empty($errors)) {
            $_SESSION['_signup']['name'] = $name;
            $_SESSION['_signup']['role'] = $role;
            $step = 3;
        }
    } elseif ($step === 3) {
        $accountType        = $_POST['account_type'] ?? '';
        $companyReg         = trim($_POST['company_registration'] ?? '');
        $data               = $_SESSION['_signup'] ?? [];
        $data['account_type'] = $accountType;

        $email    = $data['email'] ?? '';
        $phone    = $data['phone'] ?? '';
        $password = $data['password'] ?? '';
        $name     = $data['name'] ?? '';
        $role     = $data['role'] ?? '';

        $revalidate = false;

        if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Invalid email.';
            $revalidate = true;
        } else {
            $stmt = db()->prepare('SELECT id FROM users WHERE email = ?');
            $stmt->execute([$email]);
            if ($stmt->fetch()) {
                $errors[] = 'This email is already registered.';
            }
        }

        if ($phone === '') { $errors[] = 'Phone is required.'; $revalidate = true; }
        if (strlen($password) < 8) { $errors[] = 'Password must be at least 8 characters.'; $revalidate = true; }
        if ($name === '') { $errors[] = 'Full name is required.'; $revalidate = true; }
        if (!array_key_exists($role, $roles)) { $errors[] = 'Invalid role.'; $revalidate = true; }

        if (!in_array($accountType, ['individual', 'company'], true)) {
            $errors[] = 'Please select an account type.';
        }

        if ($accountType === 'company' && $companyReg === '') {
            $errors[] = 'Company registration details are required.';
        }

        if (empty($errors)) {
            $hash = password_hash($password, PASSWORD_BCRYPT);
            $token = bin2hex(random_bytes(32));
            $now = date('Y-m-d H:i:s');

            try {
                db()->beginTransaction();

                $stmt = db()->prepare('
                    INSERT INTO users (role, name, email, phone, password, account_type, company_name, created_at, updated_at)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
                ');
                $stmt->execute([
                    $role,
                    $name,
                    $email,
                    $phone,
                    $hash,
                    $accountType,
                    $accountType === 'company' ? $companyReg : null,
                    $now,
                    $now,
                ]);

                $userId = db()->lastInsertId();

                $stmt = db()->prepare('
                    INSERT INTO password_reset_tokens (email, token, type, created_at)
                    VALUES (?, ?, ?, ?)
                ');
                $stmt->execute([$email, $token, 'email', $now]);

                db()->commit();

                send_verification_email($email, $token);

                unset($_SESSION['_signup']);
                flash_set('success', 'Account created! Please check your email to verify.');
                redirect('/login');
            } catch (\Throwable $e) {
                db()->rollBack();
                $errors[] = 'An error occurred. Please try again.';
                if (DEBUG_MODE) {
                    $errors[] = $e->getMessage();
                }
            }
        }

        if (!empty($errors)) {
            $_SESSION['_signup'] = $data;
        }
    }
}

$signupData = $_SESSION['_signup'] ?? [];
$pageTitle = 'Sign Up — ' . APP_NAME;
$pageDescription = 'Create your free account on Asaan Capital Ltd. Join Nepal\'s most trusted marketplace for business matching and investment.';
?>
<?php include __DIR__ . '/../includes/header.php'; ?>
<style>
.auth-container-wide { max-width: 620px; margin: 2.5rem auto; padding: 2.25rem 2.75rem; background: var(--color-bg); border-radius: 2.5rem; }
.step-indicator { display: flex; gap: 8px; margin-bottom: 2rem; }
.step-bar { flex: 1; height: 4px; background: var(--color-border); border-radius: 999px; }
.step-bar.active { background: var(--color-primary-vivid); }
.step-bar.done { background: var(--color-success); }
.role-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin: 1.5rem 0; }
.role-card { border: 2px solid var(--color-border); border-radius: 1.25rem; padding: 1.25rem; cursor: pointer; transition: all 0.2s; }
.role-card:hover { border-color: var(--color-primary-vivid); }
.role-card.selected { border-color: var(--color-primary-vivid); background: rgba(152,32,42,0.06); }
.role-card input { display: none; }
.form-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; }
@media (max-width: 480px) { .form-grid { grid-template-columns: 1fr; } }
</style>
<div class="auth-container-wide">
    <div style="text-align:center;margin-bottom:1.5rem;">
        <h2 style="margin-bottom:0.25rem;">Create your account</h2>
        <p style="color:var(--color-text-muted);">Join Nepal's most trusted capital matching platform</p>
    </div>

    <?php if (!empty($errors)): ?>
        <div class="flash flash-error">
            <?php foreach ($errors as $err): ?>
                <div><?= e($err) ?></div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <div class="step-indicator">
        <div class="step-bar <?= $step >= 1 ? 'active' : '' ?> <?= $step > 1 ? 'done' : '' ?>"></div>
        <div class="step-bar <?= $step >= 2 ? 'active' : '' ?> <?= $step > 2 ? 'done' : '' ?>"></div>
        <div class="step-bar <?= $step >= 3 ? 'active' : '' ?>"></div>
    </div>

    <form method="post" action="/signup" id="signup-form">
        <input type="hidden" name="_csrf" value="<?= csrf_token() ?>">
        <input type="hidden" name="step" value="<?= $step ?>">

        <div id="step-1-content" <?= $step !== 1 ? 'style="display:none"' : '' ?>>
            <h3 style="margin-bottom:1rem;">Your credentials</h3>

            <div class="input-group">
                <label>Email address</label>
                <input type="email" name="email" class="input" value="<?= e($signupData['email'] ?? '') ?>" placeholder="you@example.com" required>
            </div>

            <div class="input-group">
                <label>Phone number</label>
                <input type="tel" name="phone" class="input" value="<?= e($signupData['phone'] ?? '') ?>" placeholder="+977 98XX XXXXXX" required>
            </div>

            <div class="form-grid">
                <div class="input-group">
                    <label>Password</label>
                    <input type="password" name="password" class="input" placeholder="Min 8 characters" minlength="8" required>
                    <div style="font-size:0.75rem;color:var(--color-text-muted);margin-top:4px;">Min 8 characters</div>
                </div>
                <div class="input-group">
                    <label>Confirm password</label>
                    <input type="password" name="confirm_password" class="input" placeholder="Repeat password" minlength="8" required>
                </div>
            </div>

            <button type="submit" class="btn btn-primary" style="width:100%;margin-top:0.5rem;">Continue →</button>
        </div>

        <div id="step-2-content" <?= $step !== 2 ? 'style="display:none"' : '' ?>>
            <h3 style="margin-bottom:1rem;">Tell us about yourself</h3>

            <div class="input-group">
                <label>Full name</label>
                <input type="text" name="name" class="input" value="<?= e($_POST['name'] ?? $signupData['name'] ?? '') ?>" placeholder="Ramesh Thapa" required>
            </div>

            <div class="input-group">
                <label>I am joining as a...</label>
                <div class="role-grid" id="role-grid">
                    <?php $selectedRole = $_POST['role'] ?? $signupData['role'] ?? ''; ?>
                    <?php foreach ($roles as $val => $label): ?>
                        <label class="role-card <?= $selectedRole === $val ? 'selected' : '' ?>" onclick="selectRole(this)">
                            <input type="radio" name="role" value="<?= e($val) ?>" <?= $selectedRole === $val ? 'checked' : '' ?> required>
                            <strong><?= e($label) ?></strong>
                        </label>
                    <?php endforeach; ?>
                </div>
            </div>

            <div style="display:flex;gap:0.75rem;">
                <a href="/signup?step=1" class="btn btn-secondary" style="flex:1;text-align:center;">← Back</a>
                <button type="submit" class="btn btn-primary" style="flex:1;">Continue →</button>
            </div>
        </div>

        <div id="step-3-content" <?= $step !== 3 ? 'style="display:none"' : '' ?>>
            <h3 style="margin-bottom:1rem;">Account type</h3>

            <div class="input-group">
                <label>Account type</label>
                <div style="display:flex;gap:1rem;margin-top:0.5rem;">
                    <?php $accType = $signupData['account_type'] ?? 'individual'; ?>
                    <label style="flex:1;border:1.5px solid var(--color-border);padding:10px 14px;border-radius:12px;cursor:pointer;<?= $accType === 'individual' ? 'border-color:var(--color-primary-vivid);background:rgba(152,32,42,0.06);' : '' ?>">
                        <input type="radio" name="account_type" value="individual" <?= $accType === 'individual' ? 'checked' : '' ?> onchange="toggleCompany(this)"> Individual
                    </label>
                    <label style="flex:1;border:1.5px solid var(--color-border);padding:10px 14px;border-radius:12px;cursor:pointer;<?= $accType === 'company' ? 'border-color:var(--color-primary-vivid);background:rgba(152,32,42,0.06);' : '' ?>">
                        <input type="radio" name="account_type" value="company" <?= $accType === 'company' ? 'checked' : '' ?> onchange="toggleCompany(this)"> Registered Company
                    </label>
                </div>
            </div>

            <div class="input-group" id="company-reg-group" style="<?= $accType === 'company' ? '' : 'display:none' ?>">
                <label>Company registration / PAN number</label>
                <input type="text" name="company_registration" class="input" value="<?= e($signupData['company_name'] ?? '') ?>" placeholder="Company name or registration no.">
            </div>

            <div style="background:rgba(152,32,42,0.06);border-radius:12px;padding:1rem;font-size:0.9rem;margin:1.25rem 0;">
                After signup we will send a verification link to your email. You must verify before your profile goes live.
            </div>

            <div style="display:flex;gap:0.75rem;">
                <a href="/signup?step=2" class="btn btn-secondary" style="flex:1;text-align:center;">← Back</a>
                <button type="submit" class="btn btn-accent" style="flex:1;">Create Account & Verify Email</button>
            </div>

            <div style="margin-top:1rem;font-size:0.75rem;color:var(--color-text-muted);text-align:center;">
                By creating an account you agree to our <a href="/legal" style="color:var(--color-primary-vivid);">Terms</a> and <a href="/legal" style="color:var(--color-primary-vivid);">Privacy Policy</a>.
            </div>
        </div>
    </form>
</div>

<script>
function selectRole(el) {
    document.querySelectorAll('.role-card').forEach(function(c) { c.classList.remove('selected'); });
    el.classList.add('selected');
    el.querySelector('input').checked = true;
}

function toggleCompany(el) {
    var group = document.getElementById('company-reg-group');
    if (el.value === 'company') {
        group.style.display = 'block';
        document.querySelectorAll('label:has(input[name="account_type"])').forEach(function(l) { l.style.borderColor = 'var(--color-border)'; l.style.background = ''; });
        el.parentElement.style.borderColor = 'var(--color-primary-vivid)';
        el.parentElement.style.background = 'rgba(152,32,42,0.06)';
    } else {
        group.style.display = 'none';
        document.querySelectorAll('label:has(input[name="account_type"])').forEach(function(l) { l.style.borderColor = 'var(--color-border)'; l.style.background = ''; });
        el.parentElement.style.borderColor = 'var(--color-primary-vivid)';
        el.parentElement.style.background = 'rgba(152,32,42,0.06)';
    }
}

// Restore radio button styling on page load
document.querySelectorAll('input[name="account_type"]:checked').forEach(function(el) {
    if (el.checked) toggleCompany(el);
});
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>
