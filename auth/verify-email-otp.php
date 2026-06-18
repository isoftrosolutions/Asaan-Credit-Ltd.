<?php
require __DIR__ . '/../config/bootstrap.php';

if (current_user()) {
    redirect('/dashboard');
}

$pendingUserId = $_SESSION['pending_user_id'] ?? 0;
$email = $_SESSION['pending_email'] ?? '';

if (!$pendingUserId || !$email) {
    flash_set('error', 'Session expired. Please sign up again.');
    redirect('/onboarding');
}

$error = '';
$success = false;
$OTP_EXPIRY = 300;
$MAX_ATTEMPTS = 5;
$otpAttempts = $_SESSION['otp_attempts'] ?? 0;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $submittedOtp = trim($_POST['otp'] ?? '');

    if ($otpAttempts >= $MAX_ATTEMPTS) {
        $db = db();
        $db->prepare('DELETE FROM password_reset_tokens WHERE email = ? AND type = ?')->execute([$email, 'email']);
        $db->prepare('DELETE FROM users WHERE id = ?')->execute([$pendingUserId]);
        $_SESSION['otp_attempts'] = 0;
        unset($_SESSION['pending_user_id'], $_SESSION['pending_email']);
        $error = 'Too many failed attempts. Please sign up again.';
    } else {
        $stmt = db()->prepare('SELECT token, created_at FROM password_reset_tokens WHERE email = ? AND type = ? LIMIT 1');
        $stmt->execute([$email, 'email']);
        $row = $stmt->fetch();

        if (!$row) {
            $error = 'No verification code found. Please sign up again.';
        } elseif (time() > strtotime($row['created_at']) + $OTP_EXPIRY) {
            db()->prepare('DELETE FROM password_reset_tokens WHERE email = ? AND type = ?')->execute([$email, 'email']);
            db()->prepare('DELETE FROM users WHERE id = ?')->execute([$pendingUserId]);
            unset($_SESSION['pending_user_id'], $_SESSION['pending_email']);
            $error = 'Code has expired. Please sign up again.';
        } elseif (!hash_equals($row['token'], reset_token_hash($submittedOtp))) {
            $_SESSION['otp_attempts'] = ++$otpAttempts;
            $remaining = $MAX_ATTEMPTS - $otpAttempts;
            $error = $remaining > 0
                ? "Incorrect verification code. $remaining attempt(s) remaining."
                : 'Too many failed attempts. Please sign up again.';
            if ($otpAttempts >= $MAX_ATTEMPTS) {
                db()->prepare('DELETE FROM password_reset_tokens WHERE email = ? AND type = ?')->execute([$email, 'email']);
                db()->prepare('DELETE FROM users WHERE id = ?')->execute([$pendingUserId]);
                unset($_SESSION['pending_user_id'], $_SESSION['pending_email']);
            }
        } else {
            db()->beginTransaction();
            db()->prepare('UPDATE users SET email_verified_at = NOW(), verification_status = ? WHERE id = ?')->execute(['verified', $pendingUserId]);
            db()->prepare('DELETE FROM password_reset_tokens WHERE email = ? AND type = ?')->execute([$email, 'email']);
            db()->commit();

            $_SESSION['user'] = db()->query("SELECT * FROM users WHERE id = $pendingUserId")->fetch();
            unset($_SESSION['pending_user_id'], $_SESSION['pending_email'], $_SESSION['otp_attempts']);

            send_welcome_email($email, $_SESSION['user']['name'], $_SESSION['user']['role']);

            flash_set('success', 'Email verified! Your account is now active.');
            $role = $_SESSION['user']['role'] ?? '';
            if ($role === 'investor') {
                redirect('/investor/profile-create');
            } elseif ($role === 'entrepreneur') {
                redirect('/entrepreneur/pitch-create');
            } else {
                redirect('/dashboard');
            }
        }
    }
}

$pageTitle = 'Verify Email - Asaan Capital';
$pageDescription = 'Verify your email address to activate your Asaan Capital account.';
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
</style>

<div class="auth-wrap">
    <a href="<?= APP_URL ?>/logout" class="auth-back" onclick="event.preventDefault();document.getElementById('logout-form').submit();">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 12H5"/><path d="M12 19l-7-7 7-7"/></svg>
        Back
    </a>
    <form id="logout-form" method="POST" action="<?= APP_URL ?>/logout" style="display:none;"><input type="hidden" name="_csrf" value="<?= csrf_token() ?>"></form>

    <h2 style="margin:0 0 0.35rem;font-size:1.35rem;">Verify your email</h2>
    <p style="color:var(--color-text-muted);font-size:0.9rem;margin:0 0 0.25rem;">We've sent a 6-digit code to</p>
    <p class="email-mask"><?= e($email) ?></p>

    <?php if ($error): ?>
        <div class="flash flash-error" style="margin-bottom:1rem;">
            <span style="margin-right:6px;">&#10060;</span> <?= e($error) ?>
        </div>
    <?php endif; ?>

    <form method="post" action="<?= APP_URL ?>/verify-email-otp" id="otpForm">
        <input type="hidden" name="_csrf" value="<?= csrf_token() ?>">
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
        <a href="<?= APP_URL ?>/onboarding" style="color:var(--color-primary-vivid);font-weight:600;">Resend</a>
    </div>
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
    $stmt->execute([$email, 'email']);
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

<?php include __DIR__ . '/../includes/footer.php'; ?>
