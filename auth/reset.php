<?php
session_start();
require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../config/db.php';

$token = $_GET['token'] ?? '';
$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $token = $_POST['token'] ?? '';
    $password = $_POST['password'] ?? '';
    $confirm = $_POST['confirm_password'] ?? '';
    if ($password !== '' && $password === $confirm) {
        $stmt = safe_query('SELECT user_id, expires_at FROM password_resets WHERE token = ?', 's', [$token]);
        $res = $stmt->get_result();
        if ($row = $res->fetch_assoc()) {
            if (strtotime($row['expires_at']) > time()) {
                $hash = password_hash($password, PASSWORD_DEFAULT);
                safe_query('UPDATE users SET password_hash = ? WHERE id = ?', 'si', [$hash, (int)$row['user_id']]);
                safe_query('DELETE FROM password_resets WHERE token = ?', 's', [$token]);
                $success = 'Password updated. You can now login.';
            } else {
                $error = 'Reset link expired.';
            }
        } else {
            $error = 'Invalid reset token.';
        }
    } else {
        $error = 'Passwords do not match or are empty.';
    }
}

$page_title = 'Set New Password • AppliancePro';
require_once __DIR__ . '/../includes/header.php';
?>
<?php require_once __DIR__ . '/../includes/navbar.php'; ?>
<main class="container" style="padding:40px 0 64px;max-width:680px">
  <h2>Set new password</h2>
  <?php if ($error): ?><div class="card" style="padding:12px;border-left:3px solid #ff6b6b;margin-top:10px"><?php echo htmlspecialchars($error); ?></div><?php endif; ?>
  <?php if ($success): ?><div class="card" style="padding:12px;border-left:3px solid #C6D870;margin-top:10px"><?php echo htmlspecialchars($success); ?></div><?php endif; ?>
  <form method="post" action="" class="card" style="padding:18px;margin-top:12px">
    <input type="hidden" name="token" value="<?php echo htmlspecialchars($token); ?>">
    <label for="password" style="font-weight:600">New Password</label>
    <input id="password" name="password" type="password" required style="margin-top:6px;padding:12px;border-radius:10px;border:1px solid rgba(255,255,255,.2);background:rgba(0,0,0,.15);color:#fff">
    <label for="confirm_password" style="font-weight:600; margin-top:10px">Confirm Password</label>
    <input id="confirm_password" name="confirm_password" type="password" required style="margin-top:6px;padding:12px;border-radius:10px;border:1px solid rgba(255,255,255,.2);background:rgba(0,0,0,.15);color:#fff">
    <div style="margin-top:12px">
      <button class="btn btn-primary" type="submit">Update Password</button>
    </div>
  </form>
</main>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>

