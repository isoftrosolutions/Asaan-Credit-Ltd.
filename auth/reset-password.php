<?php
require __DIR__ . '/../config/bootstrap.php';

if (current_user()) {
    redirect('/dashboard');
}

$error = '';
$success = false;

$email = trim($_GET['email'] ?? ($_POST['email'] ?? ''));
if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    flash_set('error', 'Invalid or missing email address.');
    redirect('/forgot-password');
}

$userName = 'User';
$stmt = db()->prepare('SELECT name FROM users WHERE email = ?');
$stmt->execute([$email]);
$userRow = $stmt->fetch();
$userName = $userRow ? $userRow['name'] : '';
if (!$userRow) {
    flash_set('error', 'No account found with that email.');
    redirect('/forgot-password');
}

$otpVerified = $_SESSION['otp_verified_email'] ?? null;
$otpAttempts = $_SESSION['otp_attempts'] ?? 0;

$OTP_EXPIRY = 300;
$MAX_ATTEMPTS = 5;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['otp'])) {
    csrf_check();

    $submittedOtp = trim($_POST['otp']);

    if ($otpAttempts >= $MAX_ATTEMPTS) {
        $stmt = db()->prepare('DELETE FROM password_reset_tokens WHERE email = ? AND type = ?');
        $stmt->execute([$email, 'password']);
        $_SESSION['otp_attempts'] = 0;
        $error = 'Too many failed attempts. Please request a new code.';
    } else {
        $stmt = db()->prepare('SELECT token, created_at FROM password_reset_tokens WHERE email = ? AND type = ? LIMIT 1');
        $stmt->execute([$email, 'password']);
        $row = $stmt->fetch();

        if (!$row) {
            $error = 'No reset request found. Please request a new code.';
        } elseif (time() > strtotime($row['created_at']) + $OTP_EXPIRY) {
            $stmt = db()->prepare('DELETE FROM password_reset_tokens WHERE email = ? AND type = ?');
            $stmt->execute([$email, 'password']);
            $error = 'Code has expired. Please request a new one.';
        } elseif (!hash_equals($row['token'], reset_token_hash($submittedOtp))) {
            $_SESSION['otp_attempts'] = ++$otpAttempts;
            $remaining = $MAX_ATTEMPTS - $otpAttempts;
            $error = $remaining > 0
                ? "Incorrect verification code. $remaining attempt(s) remaining."
                : 'Too many failed attempts. Please request a new code.';
            if ($otpAttempts >= $MAX_ATTEMPTS) {
                $stmt = db()->prepare('DELETE FROM password_reset_tokens WHERE email = ? AND type = ?');
                $stmt->execute([$email, 'password']);
            }
        } else {
            $_SESSION['otp_verified_email'] = $email;
            $_SESSION['otp_attempts'] = 0;
        }
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['password'])) {
    csrf_check();

    if ($_SESSION['otp_verified_email'] ?? null !== $email) {
        $error = 'Please verify your OTP code first.';
    } else {
        $password = $_POST['password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';

        if (strlen($password) < 8) {
            $error = 'Password must be at least 8 characters.';
        } elseif ($password !== $confirmPassword) {
            $error = 'Passwords do not match.';
        } elseif (!preg_match('/[A-Z]/', $password)) {
            $error = 'Password must include at least one uppercase letter.';
        } elseif (!preg_match('/[0-9]/', $password)) {
            $error = 'Password must include at least one number.';
        } elseif (!preg_match('/[^A-Za-z0-9]/', $password)) {
            $error = 'Password must include at least one special character.';
        } else {
            $hash = password_hash($password, PASSWORD_BCRYPT);

            db()->beginTransaction();
            $stmt = db()->prepare('UPDATE users SET password = ? WHERE email = ?');
            $stmt->execute([$hash, $email]);
            $stmt = db()->prepare('DELETE FROM password_reset_tokens WHERE email = ? AND type = ?');
            $stmt->execute([$email, 'password']);
            db()->commit();

            db()->prepare('UPDATE users SET failed_login_attempts = 0, locked_until = NULL WHERE email = ?')
                ->execute([$email]);

            unset($_SESSION['otp_verified_email'], $_SESSION['otp_attempts']);

            $success = true;
        }
    }
}

$otpVerified = ($_SESSION['otp_verified_email'] ?? null) === $email;

$pageTitle = 'Reset Password - Asaan Capital';
$pageDescription = 'Reset your password for Asaan Capital Ltd.';
?>
<?php include __DIR__ . '/../includes/header.php'; ?>
<style>
.auth-wrap { max-width: 420px; margin: 5rem auto; padding: 2.5rem; background: var(--color-bg); border-radius: 2.5rem; box-shadow: 0 10px 40px -15px rgba(0,0,0,0.08); }
.auth-back { display: inline-flex; align-items: center; gap: 6px; font-size: 0.85rem; color: var(--color-text-muted); text-decoration: none; margin-bottom: 1.5rem; }
.auth-back:hover { color: var(--color-primary-vivid); }
.auth-back svg { width: 16px; height: 16px; }
.email-mask { font-size: 1.05rem; font-weight: 600; color: var(--color-text); }

.otp-wrap { display: flex; gap: 10px; justify-content: center; margin: 1.5rem 0; }
.otp-input { width: 48px; height: 56px; text-align: center; font-size: 1.4rem; font-weight: 700; font-family: monospace; border: 2px solid var(--color-border); border-radius: var(--radius-md); outline: none; background: var(--color-bg); color: var(--color-text); transition: border-color .15s; }
.otp-input:focus { border-color: var(--color-primary-vivid); box-shadow: 0 0 0 3px rgba(152,32,42,0.12); }
.otp-input.filled { border-color: var(--color-primary-vivid); background: #fef2f2; }

.pwd-wrap { position: relative; }
.pwd-toggle { position: absolute; right: 12px; top: 50%; transform: translateY(-50%); background: none; border: none; cursor: pointer; color: var(--color-text-muted); padding: 4px; display: flex; }
.pwd-toggle:hover { color: var(--color-text); }

.strength-bar { display: flex; gap: 4px; margin: 0.75rem 0 0.5rem; }
.strength-bar span { flex: 1; height: 4px; border-radius: 4px; background: var(--color-border); transition: background .2s; }
.strength-bar span.active.weak { background: var(--color-error); }
.strength-bar span.active.medium { background: var(--color-warning); }
.strength-bar span.active.strong { background: var(--color-success); }
.strength-label { font-size: 0.8rem; font-weight: 600; }
.strength-label.weak { color: var(--color-error); }
.strength-label.medium { color: var(--color-warning); }
.strength-label.strong { color: var(--color-success); }

.req-list { margin: 0.75rem 0 0; padding: 0; list-style: none; font-size: 0.8rem; }
.req-list li { display: flex; align-items: center; gap: 6px; padding: 2px 0; color: var(--color-text-muted); transition: color .2s; }
.req-list li.met { color: var(--color-success); }
.req-list li svg { flex-shrink: 0; width: 14px; height: 14px; }
</style>

<div class="auth-wrap">
    <?php if ($success): ?>
        <meta http-equiv="refresh" content="3;url=/login">
        <div style="text-align:center;">
            <div style="width:64px;height:64px;border-radius:50%;background:#f0fdf4;display:flex;align-items:center;justify-content:center;margin:0 auto 1.25rem;">
                <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="var(--color-success)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
            </div>
            <h2 style="margin:0 0 0.35rem;font-size:1.35rem;">Password reset successful</h2>
            <p style="color:var(--color-text-muted);font-size:0.9rem;margin:0 0 1.5rem;">Your password has been updated successfully.</p>
            <a href="/login" class="btn btn-primary" style="display:block;text-align:center;">Continue to Login</a>
        </div>

    <?php elseif ($otpVerified): ?>
        <a href="/reset-password?email=<?= e(urlencode($email)) ?>" class="auth-back">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 12H5"/><path dM12 19l-7-7 7-7"/></svg>
            Back
        </a>

        <h2 style="margin:0 0 0.35rem;font-size:1.35rem;">Create new password</h2>
        <p style="color:var(--color-text-muted);font-size:0.9rem;margin:0 0 1.75rem;">Your new password must be different from previous passwords.</p>

        <?php if ($error): ?>
            <div class="flash flash-error" style="margin-bottom:1rem;"><?= e($error) ?></div>
        <?php endif; ?>

        <form method="post" action="/reset-password?email=<?= e(urlencode($email)) ?>" id="pwdForm">
            <input type="hidden" name="_csrf" value="<?= csrf_token() ?>">
            <input type="hidden" name="email" value="<?= e($email) ?>">

            <div class="input-group">
                <label>Password</label>
                <div class="pwd-wrap">
                    <input type="password" name="password" id="pwdInput" class="input" placeholder="Min 8 characters" minlength="8" required autofocus style="padding-right:40px;">
                    <button type="button" class="pwd-toggle" id="pwdToggle" tabindex="-1" aria-label="Toggle password visibility">
                        <svg id="eyeIcon" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                    </button>
                </div>
                <div class="strength-bar" id="strengthBar">
                    <span></span><span></span><span></span><span></span>
                </div>
                <div id="strengthLabel" class="strength-label" style="margin-top:2px;"></div>
                <ul class="req-list" id="reqList">
                    <li id="reqLen"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/></svg> 8+ Characters</li>
                    <li id="reqUpper"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/></svg> Uppercase Letter</li>
                    <li id="reqNum"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/></svg> Number</li>
                    <li id="reqSpecial"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/></svg> Special Character</li>
                </ul>
            </div>

            <div class="input-group">
                <label>Confirm password</label>
                <div class="pwd-wrap">
                    <input type="password" name="confirm_password" id="confirmInput" class="input" placeholder="Repeat password" minlength="8" required style="padding-right:40px;">
                    <button type="button" class="pwd-toggle" id="confirmToggle" tabindex="-1" aria-label="Toggle password visibility">
                        <svg id="confirmEye" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                    </button>
                </div>
                <div id="matchMsg" style="font-size:0.8rem;margin-top:4px;display:none;"></div>
            </div>

            <button type="submit" class="btn btn-primary" style="width:100%;padding:14px;" id="resetBtn">Reset Password</button>
        </form>

        <script>
        (function () {
            const pwd = document.getElementById('pwdInput');
            const confirm = document.getElementById('confirmInput');
            const matchMsg = document.getElementById('matchMsg');
            const bars = document.querySelectorAll('#strengthBar span');
            const label = document.getElementById('strengthLabel');
            const reqLen = document.getElementById('reqLen');
            const reqUpper = document.getElementById('reqUpper');
            const reqNum = document.getElementById('reqNum');
            const reqSpecial = document.getElementById('reqSpecial');
            const resetBtn = document.getElementById('resetBtn');

            function setReq(el, met) {
                el.classList.toggle('met', met);
                el.querySelector('svg').setAttribute('viewBox', met ? '0 0 24 24' : '0 0 24 24');
                el.querySelector('svg').innerHTML = met
                    ? '<circle cx="12" cy="12" r="10" fill="var(--color-success)"/><path d="m9 12 2 2 4-4" stroke="#fff" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" fill="none"/>'
                    : '<circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="2" fill="none"/>';
            }

            function calcStrength(v) {
                let score = 0;
                if (v.length >= 8) score++;
                if (/[A-Z]/.test(v)) score++;
                if (/[0-9]/.test(v)) score++;
                if (/[^A-Za-z0-9]/.test(v)) score++;
                return score;
            }

            function updateStrength(v) {
                const score = calcStrength(v);
                bars.forEach((b, i) => {
                    b.className = '';
                    if (i < score) {
                        if (score <= 1) b.classList.add('active', 'weak');
                        else if (score <= 2) b.classList.add('active', 'medium');
                        else b.classList.add('active', 'strong');
                    }
                });
                if (v === '') {
                    label.textContent = '';
                } else if (score <= 1) {
                    label.textContent = 'Weak';
                    label.className = 'strength-label weak';
                } else if (score <= 2) {
                    label.textContent = 'Medium';
                    label.className = 'strength-label medium';
                } else if (score <= 3) {
                    label.textContent = 'Strong';
                    label.className = 'strength-label strong';
                } else {
                    label.textContent = 'Very strong';
                    label.className = 'strength-label strong';
                }

                setReq(reqLen, v.length >= 8);
                setReq(reqUpper, /[A-Z]/.test(v));
                setReq(reqNum, /[0-9]/.test(v));
                setReq(reqSpecial, /[^A-Za-z0-9]/.test(v));
            }

            function updateMatch() {
                if (confirm.value === '') {
                    matchMsg.style.display = 'none';
                    return;
                }
                matchMsg.style.display = 'block';
                if (pwd.value === confirm.value && pwd.value !== '') {
                    matchMsg.textContent = 'Passwords match';
                    matchMsg.style.color = 'var(--color-success)';
                } else {
                    matchMsg.textContent = 'Passwords do not match';
                    matchMsg.style.color = 'var(--color-error)';
                }
            }

            pwd.addEventListener('input', function () {
                updateStrength(this.value);
                updateMatch();
            });
            confirm.addEventListener('input', updateMatch);

            function toggleVisibility(inputId, iconId) {
                const input = document.getElementById(inputId);
                const icon = document.getElementById(iconId);
                const isPassword = input.type === 'password';
                input.type = isPassword ? 'text' : 'password';
                icon.innerHTML = isPassword
                    ? '<path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"/><line x1="1" y1="1" x2="23" y2="23"/>'
                    : '<path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/>';
            }

            document.getElementById('pwdToggle').addEventListener('click', function () {
                toggleVisibility('pwdInput', 'eyeIcon');
            });
            document.getElementById('confirmToggle').addEventListener('click', function () {
                toggleVisibility('confirmInput', 'confirmEye');
            });
        })();
        </script>

    <?php else: ?>
        <a href="/forgot-password" class="auth-back">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 12H5"/><path dM12 19l-7-7 7-7"/></svg>
            Back
        </a>

        <h2 style="margin:0 0 0.35rem;font-size:1.35rem;">Verify your email</h2>
        <p style="color:var(--color-text-muted);font-size:0.9rem;margin:0 0 0.25rem;">We've sent a 6-digit code to</p>
        <p class="email-mask"><?= e($email) ?></p>

        <?php if ($error): ?>
            <div class="flash flash-error" style="margin-bottom:1rem;">
                <span style="margin-right:6px;">&#10060;</span> <?= e($error) ?>
            </div>
        <?php endif; ?>

        <form method="post" action="/reset-password?email=<?= e(urlencode($email)) ?>" id="otpForm">
            <input type="hidden" name="_csrf" value="<?= csrf_token() ?>">
            <input type="hidden" name="email" value="<?= e($email) ?>">
            <input type="hidden" name="otp" id="otpHidden">

            <div class="otp-wrap" id="otpWrap">
                <?php for ($i = 0; $i < 6; $i++): ?>
                    <input type="text" class="otp-input" maxlength="1" inputmode="numeric" pattern="[0-9]" autocomplete="off" data-index="<?= $i ?>" <?= $i === 0 ? 'autofocus' : '' ?>>
                <?php endfor; ?>
            </div>

            <div id="timerWrap" style="text-align:center;font-size:0.9rem;color:var(--color-text-muted);margin-bottom:1rem;">
                <span id="timerDisplay" style="font-weight:700;font-variant-numeric:tabular-nums;"></span>
            </div>

            <button type="submit" id="verifyBtn" class="btn btn-primary" style="width:100%;padding:14px;" disabled>Verify Code</button>
        </form>

        <div style="margin-top:1.25rem;text-align:center;font-size:0.9rem;">
            Didn't receive code?
            <a href="/forgot-password" id="resendLink" style="color:var(--color-primary-vivid);font-weight:600;">Resend</a>
        </div>

        <script>
        (function () {
            const inputs = document.querySelectorAll('.otp-input');
            const hidden = document.getElementById('otpHidden');
            const verifyBtn = document.getElementById('verifyBtn');
            const timerDisplay = document.getElementById('timerDisplay');

            function getOtp() {
                let val = '';
                inputs.forEach(inp => val += inp.value);
                return val;
            }

            function updateHidden() {
                const otp = getOtp();
                hidden.value = otp;
                verifyBtn.disabled = otp.length !== 6;
                inputs.forEach((inp, i) => {
                    inp.classList.toggle('filled', inp.value !== '');
                });
            }

            function focusNext(idx) {
                if (idx < inputs.length - 1) inputs[idx + 1].focus();
            }

            function focusPrev(idx) {
                if (idx > 0) inputs[idx - 1].focus();
            }

            inputs.forEach((inp, idx) => {
                inp.addEventListener('input', function () {
                    this.value = this.value.replace(/[^0-9]/g, '').slice(0, 1);
                    if (this.value !== '') focusNext(idx);
                    updateHidden();
                });

                inp.addEventListener('keydown', function (e) {
                    if (e.key === 'Backspace' && this.value === '') {
                        focusPrev(idx);
                    }
                    if (e.key === 'ArrowLeft') focusPrev(idx);
                    if (e.key === 'ArrowRight') focusNext(idx);
                });

                inp.addEventListener('focus', function () {
                    this.select();
                });

                inp.addEventListener('paste', function (e) {
                    e.preventDefault();
                    const data = (e.clipboardData || window.clipboardData).getData('text').replace(/[^0-9]/g, '').slice(0, 6);
                    for (let i = 0; i < data.length; i++) {
                        if (idx + i < inputs.length) {
                            inputs[idx + i].value = data[i];
                        }
                    }
                    const next = Math.min(idx + data.length, inputs.length - 1);
                    inputs[next].focus();
                    updateHidden();
                });
            });

            updateHidden();

            <?php
            $stmt = db()->prepare('SELECT created_at FROM password_reset_tokens WHERE email = ? AND type = ? LIMIT 1');
            $stmt->execute([$email, 'password']);
            $row = $stmt->fetch();
            $expiresAt = $row ? strtotime($row['created_at']) + $OTP_EXPIRY : time();
            ?>

            let expiry = <?= $expiresAt ?>;
            let remaining = Math.max(0, Math.floor((expiry - Date.now() / 1000)));

            function tick() {
                remaining = Math.max(0, Math.floor((expiry - Date.now() / 1000)));
                if (remaining <= 0) {
                    timerDisplay.textContent = 'Code expired';
                    verifyBtn.disabled = true;
                    return;
                }
                const m = String(Math.floor(remaining / 60)).padStart(2, '0');
                const s = String(remaining % 60).padStart(2, '0');
                timerDisplay.textContent = m + ':' + s;
                setTimeout(tick, 1000);
            }
            tick();
        })();
        </script>
    <?php endif; ?>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
