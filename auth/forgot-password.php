<?php
require __DIR__ . '/../config/bootstrap.php';

if (current_user()) {
    redirect('/dashboard');
}

$email = '';
$sent = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();

    $email = trim($_POST['email'] ?? '');

    if ($email !== '' && filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $stmt = db()->prepare('SELECT id FROM users WHERE email = ?');
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user) {
            $stmt = db()->prepare('SELECT created_at FROM password_reset_tokens WHERE email = ? AND type = ? ORDER BY created_at DESC LIMIT 1');
            $stmt->execute([$email, 'password']);
            $last = $stmt->fetchColumn();
            $throttled = $last && (time() - strtotime($last)) < 60;

            if (!$throttled) {
                $otp = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);

                $stmt = db()->prepare('DELETE FROM password_reset_tokens WHERE email = ? AND type = ?');
                $stmt->execute([$email, 'password']);

                $stmt = db()->prepare('INSERT INTO password_reset_tokens (email, token, type, created_at) VALUES (?, ?, ?, ?)');
                $stmt->execute([$email, reset_token_hash($otp), 'password', date('Y-m-d H:i:s')]);

                send_password_reset_email($email, $otp);
            }
        }
    }

    $sent = true;
}

$pageTitle = 'Forgot Password - Asaan Capital';
$pageDescription = 'Reset your password for Asaan Capital Ltd.';
?>
<?php include __DIR__ . '/../includes/header.php'; ?>
<style>
.auth-wrap { max-width: 420px; margin: 5rem auto; padding: 2.5rem; background: var(--color-bg); border-radius: 2.5rem; box-shadow: 0 10px 40px -15px rgba(0,0,0,0.08); }
.auth-back { display: inline-flex; align-items: center; gap: 6px; font-size: 0.85rem; color: var(--color-text-muted); text-decoration: none; margin-bottom: 1.5rem; }
.auth-back:hover { color: var(--color-primary-vivid); }
.auth-back svg { width: 16px; height: 16px; }
.btn-loading { position: relative; pointer-events: none; }
.btn-loading::after { content: ''; position: absolute; inset: 0; background: inherit; border-radius: inherit; display: flex; align-items: center; justify-content: center; }
.spinner { display: inline-block; width: 18px; height: 18px; border: 2px solid rgba(255,255,255,0.3); border-top-color: #fff; border-radius: 50%; animation: spin .6s linear infinite; vertical-align: middle; margin-right: 6px; }
@keyframes spin { to { transform: rotate(360deg); } }
.email-mask { font-size: 1.05rem; font-weight: 600; color: var(--color-text); }
</style>
<div class="auth-wrap">
    <a href="<?= APP_URL ?>/login" class="auth-back">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 12H5"/><path dM12 19l-7-7 7-7"/></svg>
        Back
    </a>

    <?php if ($sent): ?>
        <div style="text-align:center;">
            <div style="width:56px;height:56px;border-radius:50%;background:#f0fdf4;display:flex;align-items:center;justify-content:center;margin:0 auto 1.25rem;">
                <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="var(--color-success)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 12a10 10 0 1 1-20 0 10 10 0 0 1 20 0z"/><path d="m9 12 2 2 4-4"/></svg>
            </div>
            <h2 style="margin:0 0 0.35rem;font-size:1.35rem;">Check your email</h2>
            <p style="color:var(--color-text-muted);font-size:0.9rem;margin:0 0 0.25rem;">We sent a 6-digit code to</p>
            <p class="email-mask"><?= e($email) ?></p>
            <a href="<?= APP_URL ?>/reset-password?email=<?= e(urlencode($email)) ?>" class="btn btn-primary" style="display:block;margin-top:1.5rem;text-align:center;">Enter Code</a>
            <p style="margin-top:1.25rem;font-size:0.85rem;color:var(--color-text-muted);">Didn't receive it? <a href="<?= APP_URL ?>/forgot-password" style="color:var(--color-primary-vivid);">Resend</a></p>
        </div>
    <?php else: ?>
        <h2 style="margin:0 0 0.35rem;font-size:1.35rem;">Forgot password?</h2>
        <p style="color:var(--color-text-muted);font-size:0.9rem;margin:0 0 1.75rem;">Enter your registered email address. We'll send a verification code.</p>

        <form method="post" id="forgotForm">
            <input type="hidden" name="_csrf" value="<?= csrf_token() ?>">

            <div class="input-group">
                <label>Email address</label>
                <input type="email" name="email" id="emailInput" class="input" value="<?= e(old('email')) ?>" placeholder="you@example.com" required autofocus autocomplete="email">
                <div id="emailError" style="font-size:0.8rem;color:var(--color-error);margin-top:4px;display:none;"></div>
            </div>

            <button type="submit" id="sendBtn" class="btn btn-primary" style="width:100%;padding:14px;" disabled>Send Code</button>
        </form>

        <div style="margin-top:1.5rem;text-align:center;font-size:0.9rem;">
            Remember your password? <a href="<?= APP_URL ?>/login" style="color:var(--color-primary-vivid);font-weight:600;">Sign In</a>
        </div>
    <?php endif; ?>
</div>

<script>
const emailInput = document.getElementById('emailInput');
const sendBtn = document.getElementById('sendBtn');
const emailError = document.getElementById('emailError');

function validateEmail(v) {
    return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(v);
}

if (emailInput) {
    emailInput.addEventListener('input', function () {
        const v = this.value.trim();
        if (v === '') {
            sendBtn.disabled = true;
            emailError.style.display = 'none';
        } else if (!validateEmail(v)) {
            sendBtn.disabled = true;
            emailError.textContent = 'Please enter a valid email address.';
            emailError.style.display = 'block';
        } else {
            sendBtn.disabled = false;
            emailError.style.display = 'none';
        }
    });
}

document.getElementById('forgotForm')?.addEventListener('submit', function () {
    sendBtn.disabled = true;
    sendBtn.innerHTML = '<span class="spinner"></span> Sending...';
});
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>
