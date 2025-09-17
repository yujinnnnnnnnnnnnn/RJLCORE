<?php
require_once __DIR__ . '/../includes/auth.php';
require_role([3]);
require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../config/db.php';

$uid = (int)($_SESSION['user_id'] ?? 0);
$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (($_POST['form'] ?? '') === 'update_profile') {
        $full_name = trim($_POST['full_name'] ?? '');
        $phone = trim($_POST['phone'] ?? '');
        $address = trim($_POST['address'] ?? '');
        safe_query('UPDATE users SET full_name=?, phone=?, address=? WHERE id=? AND role_id=3', 'sssi', [$full_name,$phone,$address,$uid]);
        $_SESSION['full_name'] = $full_name;
        $message = 'Profile updated.';
    }
    if (($_POST['form'] ?? '') === 'change_password') {
        $pass = $_POST['password'] ?? '';
        $confirm = $_POST['confirm_password'] ?? '';
        if ($pass && $pass === $confirm) {
            $hash = password_hash($pass, PASSWORD_DEFAULT);
            safe_query('UPDATE users SET password_hash=? WHERE id=?', 'si', [$hash, $uid]);
            $message = 'Password changed.';
        }
    }
}

$user = safe_query('SELECT full_name, email, phone, address FROM users WHERE id=?', 'i', [$uid])->get_result()->fetch_assoc();

$page_title = 'Profile Settings • AppliancePro';
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/navbar.php';
?>
<main class="container" style="padding:32px 0 64px">
  <h2>Profile Settings</h2>
  <?php if ($message): ?><div class="card" style="padding:12px;margin-top:10px;border-left:3px solid #C6D870"><?php echo htmlspecialchars($message); ?></div><?php endif; ?>
  <div class="features-grid" style="margin-top:12px">
    <div class="card revealed">
      <h3>Update Contact Details</h3>
      <form method="post" class="grid-2">
        <input type="hidden" name="form" value="update_profile">
        <div>
          <label>Full Name</label>
          <input name="full_name" value="<?php echo htmlspecialchars($user['full_name'] ?? ''); ?>" required style="width:100%;padding:10px;border-radius:10px;border:1px solid rgba(255,255,255,.2);background:rgba(0,0,0,.15);color:#fff">
        </div>
        <div>
          <label>Email</label>
          <input value="<?php echo htmlspecialchars($user['email'] ?? ''); ?>" disabled style="width:100%;padding:10px;border-radius:10px;border:1px solid rgba(255,255,255,.2);background:rgba(0,0,0,.15);color:#fff">
        </div>
        <div>
          <label>Phone</label>
          <input name="phone" value="<?php echo htmlspecialchars((string)($user['phone'] ?? '')); ?>" style="width:100%;padding:10px;border-radius:10px;border:1px solid rgba(255,255,255,.2);background:rgba(0,0,0,.15);color:#fff">
        </div>
        <div>
          <label>Address</label>
          <input name="address" value="<?php echo htmlspecialchars((string)($user['address'] ?? '')); ?>" style="width:100%;padding:10px;border-radius:10px;border:1px solid rgba(255,255,255,.2);background:rgba(0,0,0,.15);color:#fff">
        </div>
        <div style="grid-column:1/-1;text-align:right">
          <button class="btn btn-primary" type="submit">Save</button>
        </div>
      </form>
    </div>
    <div class="card revealed">
      <h3>Change Password</h3>
      <form method="post" class="grid-2">
        <input type="hidden" name="form" value="change_password">
        <div>
          <label>New Password</label>
          <input name="password" type="password" required style="width:100%;padding:10px;border-radius:10px;border:1px solid rgba(255,255,255,.2);background:rgba(0,0,0,.15);color:#fff">
        </div>
        <div>
          <label>Confirm Password</label>
          <input name="confirm_password" type="password" required style="width:100%;padding:10px;border-radius:10px;border:1px solid rgba(255,255,255,.2);background:rgba(0,0,0,.15);color:#fff">
        </div>
        <div style="grid-column:1/-1;text-align:right">
          <button class="btn btn-primary" type="submit">Update</button>
        </div>
      </form>
    </div>
  </div>
</main>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>

