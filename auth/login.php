<?php
session_start();
require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../config/db.php';

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    if ($email !== '' && $password !== '') {
        $stmt = safe_query('SELECT id, role_id, password_hash, full_name FROM users WHERE email = ? AND is_active = 1', 's', [$email]);
        $res = $stmt->get_result();
        if ($row = $res->fetch_assoc()) {
            if (password_verify($password, $row['password_hash'])) {
                $_SESSION['user_id'] = (int)$row['id'];
                $_SESSION['role_id'] = (int)$row['role_id'];
                $_SESSION['full_name'] = $row['full_name'];
                // Redirect based on role
                if ((int)$row['role_id'] === 1 || (int)$row['role_id'] === 2) {
                    header('Location: ' . url('admin/'));
                } else {
                    header('Location: ' . url('customer/'));
                }
                exit;
            }
        }
        $error = 'Invalid credentials.';
    } else {
        $error = 'Please fill all fields.';
    }
}

$page_title = 'Login • AppliancePro';
require_once __DIR__ . '/../includes/header.php';
?>
<style>
.auth-wrapper{min-height:calc(100vh - 64px - 120px);display:grid;place-items:center;padding:40px 16px}
.card-auth{width:100%;max-width:420px;background:rgba(255,255,255,.06);border:1px solid rgba(255,255,255,.12);border-radius:16px;padding:20px}
.field{display:flex;flex-direction:column;gap:6px;margin-bottom:12px}
.field input{padding:12px 12px;border-radius:10px;border:1px solid rgba(255,255,255,.2);background:rgba(0,0,0,.15);color:#fff}
.error{background:rgba(255,90,95,.12);border:1px solid rgba(255,90,95,.25);color:#ffdada;padding:10px;border-radius:10px;margin-bottom:10px}
label{font-weight:600}
</style>
<?php require_once __DIR__ . '/../includes/navbar.php'; ?>
<main class="auth-wrapper">
  <form class="card-auth" method="post" action="">
    <h2>Sign in</h2>
    <?php if ($error): ?><div class="error"><?php echo htmlspecialchars($error); ?></div><?php endif; ?>
    <div class="field">
      <label for="email">Email</label>
      <input id="email" name="email" type="email" required>
    </div>
    <div class="field">
      <label for="password">Password</label>
      <input id="password" name="password" type="password" required>
    </div>
    <div style="display:flex;gap:10px;align-items:center;justify-content:space-between;margin-top:6px">
      <button class="btn btn-primary" type="submit">Login</button>
      <a href="<?php echo url('auth/forgot.php'); ?>" class="muted">Forgot password?</a>
    </div>
    <div class="muted" style="margin-top:10px">No account? <a href="<?php echo url('auth/register.php'); ?>">Sign up</a></div>
  </form>
</main>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>

