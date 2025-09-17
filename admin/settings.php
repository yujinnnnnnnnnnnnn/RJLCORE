<?php
require_once __DIR__ . '/../includes/auth.php';
require_role([1,2]);
require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../config/db.php';

$message = '';
// Admin creates staff/admin users
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['form'] ?? '') === 'create_user') {
    if ((int)($_SESSION['role_id'] ?? 0) === 1) {
        $role_id = (int)($_POST['role_id'] ?? 2);
        if (!in_array($role_id, [1,2], true)) { $role_id = 2; }
        $email = trim($_POST['email'] ?? '');
        $full_name = trim($_POST['full_name'] ?? '');
        $password = $_POST['password'] ?? '';
        if ($email && $full_name && $password) {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            safe_query('INSERT INTO users (role_id, email, password_hash, full_name) VALUES (?,?,?,?)', 'isss', [$role_id,$email,$hash,$full_name]);
            $message = 'User created.';
        }
    }
}

// Staff/Admin change own password
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['form'] ?? '') === 'change_password') {
    $pass = $_POST['password'] ?? '';
    $confirm = $_POST['confirm_password'] ?? '';
    if ($pass && $pass === $confirm) {
        $hash = password_hash($pass, PASSWORD_DEFAULT);
        safe_query('UPDATE users SET password_hash=? WHERE id=?', 'si', [$hash, (int)$_SESSION['user_id']]);
        $message = 'Password updated.';
    }
}

$page_title = 'Settings • AppliancePro';
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/navbar.php';
?>
<main class="container" style="padding:32px 0 64px">
  <h2>Settings</h2>
  <?php if ($message): ?><div class="card" style="padding:12px;margin-top:10px;border-left:3px solid #C6D870"><?php echo htmlspecialchars($message); ?></div><?php endif; ?>

  <?php if ((int)($_SESSION['role_id'] ?? 0) === 1): ?>
  <div class="card revealed" style="margin-top:12px">
    <h3>Create User (Admin/Staff)</h3>
    <form method="post" class="grid-2">
      <input type="hidden" name="form" value="create_user">
      <div>
        <label>Role</label>
        <select name="role_id" style="width:100%;padding:10px;border-radius:10px;border:1px solid rgba(255,255,255,.2);background:rgba(0,0,0,.15);color:#fff">
          <option value="2">Staff</option>
          <option value="1">Admin</option>
        </select>
      </div>
      <div>
        <label>Full Name</label>
        <input name="full_name" required style="width:100%;padding:10px;border-radius:10px;border:1px solid rgba(255,255,255,.2);background:rgba(0,0,0,.15);color:#fff">
      </div>
      <div>
        <label>Email</label>
        <input name="email" type="email" required style="width:100%;padding:10px;border-radius:10px;border:1px solid rgba(255,255,255,.2);background:rgba(0,0,0,.15);color:#fff">
      </div>
      <div>
        <label>Password</label>
        <input name="password" type="password" required style="width:100%;padding:10px;border-radius:10px;border:1px solid rgba(255,255,255,.2);background:rgba(0,0,0,.15);color:#fff">
      </div>
      <div style="grid-column:1/-1;text-align:right">
        <button class="btn btn-primary" type="submit">Create</button>
      </div>
    </form>
  </div>
  <?php endif; ?>

  <div class="card revealed" style="margin-top:12px">
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
</main>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>

