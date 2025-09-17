<?php
require_once __DIR__ . '/../app/bootstrap.php';
use App\Security;
use App\Database;
use App\Mailer;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['email'])) {
    $csrf = $_POST['csrf'] ?? '';
    if (!Security::validateCsrf($csrf)) {
        $error = 'Invalid CSRF token.';
    } else {
        $email = trim($_POST['email']);
        // Find user
        $stmt = Database::pdo()->prepare('SELECT id, full_name FROM users WHERE email = ? LIMIT 1');
        $stmt->execute([$email]);
        $u = $stmt->fetch();
        if ($u) {
            $token = bin2hex(random_bytes(32));
            $expires = (new DateTime('+1 hour'))->format('Y-m-d H:i:s');
            $ins = Database::pdo()->prepare('INSERT INTO password_resets (user_id, token, expires_at) VALUES (?,?,?)');
            $ins->execute([(int)$u['id'], $token, $expires]);
            $link = (rtrim($_ENV['APP_URL'] ?? 'http://localhost/appliances', '/')) . '/public/reset_password.php?token=' . $token;
            Mailer::send($email, $u['full_name'], 'Password Reset', '<p>Click to reset your password: <a href="' . $link . '">Reset Password</a></p>');
        }
        $success = 'If that email exists, a reset link has been sent.';
    }
}

$pageTitle = 'Password Reset — Appliances Store';
include __DIR__ . '/../views/templates/header.php';
?>
<div class="form-card">
  <h2 style="margin-top:0;color:#113F67">Password Reset</h2>
  <?php if (!empty($error)): ?>
  <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
  <?php endif; ?>
  <?php if (!empty($success)): ?>
  <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
  <?php endif; ?>
  <form method="post" action="">
    <input type="hidden" name="csrf" value="<?= Security::csrfToken() ?>">
    <div class="form-group">
      <label>Email</label>
      <input type="email" name="email" required>
    </div>
    <button class="btn btn-primary" type="submit">Send Reset Link</button>
  </form>
</div>
<?php include __DIR__ . '/../views/templates/footer.php';
