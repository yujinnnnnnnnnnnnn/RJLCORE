<?php
session_start();
require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../utils/mail.php';

$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    if ($email !== '') {
        $stmt = safe_query('SELECT id FROM users WHERE email = ? AND is_active = 1', 's', [$email]);
        $res = $stmt->get_result();
        if ($row = $res->fetch_assoc()) {
            $user_id = (int)$row['id'];
            $token = bin2hex(random_bytes(32));
            $expires = date('Y-m-d H:i:s', time()+3600);
            safe_query('INSERT INTO password_resets (user_id, token, expires_at) VALUES (?, ?, ?)', 'iss', [$user_id, $token, $expires]);
            $reset_link = url('auth/reset.php?token=' . urlencode($token));
            $email_html = '<p>You requested a password reset.</p><p>Click the link below to set a new password:</p><p><a href="' . htmlspecialchars($reset_link) . '">Reset Password</a></p>';
            $sent = send_email($email, 'Password Reset', $email_html);
            $message = $sent ? 'If the email exists, a reset link will be sent.' : 'If the email exists, a reset link will be sent. (Also shown here for development: ' . htmlspecialchars($reset_link) . ')';
        } else {
            $message = 'If the email exists, a reset link will be sent.';
        }
    }
}

$page_title = 'Forgot Password • AppliancePro';
require_once __DIR__ . '/../includes/header.php';
?>
<?php require_once __DIR__ . '/../includes/navbar.php'; ?>
<main class="container" style="padding:40px 0 64px;max-width:680px">
  <h2>Reset your password</h2>
  <form method="post" action="" class="card" style="padding:18px;margin-top:12px">
    <label for="email" style="font-weight:600">Email</label>
    <input id="email" name="email" type="email" required style="margin-top:6px;padding:12px;border-radius:10px;border:1px solid rgba(255,255,255,.2);background:rgba(0,0,0,.15);color:#fff">
    <div style="margin-top:12px">
      <button class="btn btn-primary" type="submit">Send reset link</button>
    </div>
  </form>
  <?php if ($message): ?><p class="muted" style="margin-top:12px"><?php echo $message; ?></p><?php endif; ?>
</main>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>

