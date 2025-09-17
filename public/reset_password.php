<?php
require_once __DIR__ . '/../app/bootstrap.php';
use App\Security;
use App\Database;
use App\Models\User;

$token = $_GET['token'] ?? '';
$stmt = Database::pdo()->prepare('SELECT pr.id, pr.user_id, pr.expires_at, pr.used, u.email FROM password_resets pr JOIN users u ON u.id = pr.user_id WHERE pr.token = ? LIMIT 1');
$stmt->execute([$token]);
$row = $stmt->fetch();
$valid = $row && !$row['used'] && (new DateTime() < new DateTime($row['expires_at']));

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $valid) {
    if (!Security::validateCsrf($_POST['csrf'] ?? '')) {
        $error = 'Invalid CSRF token.';
    } else {
        $p1 = $_POST['password'] ?? '';
        $p2 = $_POST['password_confirm'] ?? '';
        if ($p1 !== $p2 || strlen($p1) < 6) {
            $error = 'Passwords must match and be at least 6 chars.';
        } else {
            User::updatePassword((int)$row['user_id'], $p1);
            $upd = Database::pdo()->prepare('UPDATE password_resets SET used = 1 WHERE id = ?');
            $upd->execute([(int)$row['id']]);
            $success = 'Password updated. You can now log in.';
            $valid = false;
        }
    }
}

$pageTitle = 'Set New Password — Appliances Store';
include __DIR__ . '/../views/templates/header.php';
?>
<div class="form-card">
  <h2 style="margin-top:0;color:#113F67">Set New Password</h2>
  <?php if (!empty($error)): ?>
  <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
  <?php endif; ?>
  <?php if (!empty($success)): ?>
  <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
  <?php endif; ?>
  <?php if ($valid): ?>
  <form method="post" action="">
    <input type="hidden" name="csrf" value="<?= Security::csrfToken() ?>">
    <div class="form-group">
      <label>New Password</label>
      <input type="password" name="password" required>
    </div>
    <div class="form-group">
      <label>Confirm New Password</label>
      <input type="password" name="password_confirm" required>
    </div>
    <button class="btn btn-primary" type="submit">Update Password</button>
  </form>
  <?php else: ?>
  <p class="muted">This link is invalid or expired. Please request a new reset link.</p>
  <?php endif; ?>
</div>
<?php include __DIR__ . '/../views/templates/footer.php';
