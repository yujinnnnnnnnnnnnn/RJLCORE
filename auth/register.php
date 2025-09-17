<?php
session_start();
require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../config/db.php';

$error = '';
$success = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $full_name = trim($_POST['full_name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm = $_POST['confirm_password'] ?? '';
    if ($full_name !== '' && $email !== '' && $password !== '' && $confirm !== '') {
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error = 'Please enter a valid email.';
        } elseif ($password !== $confirm) {
            $error = 'Passwords do not match.';
        } else {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            // role_id 3 is customer
            $stmt = safe_query('INSERT INTO users (role_id, email, password_hash, full_name) VALUES (3, ?, ?, ?)', 'sss', [$email, $hash, $full_name]);
            if ($stmt->affected_rows > 0) {
                $success = 'Registration successful. You can now log in.';
            } else {
                $error = 'Registration failed.';
            }
        }
    } else {
        $error = 'Please fill all fields.';
    }
}

$page_title = 'Sign Up • AppliancePro';
require_once __DIR__ . '/../includes/header.php';
?>
<style>
.auth-wrapper{min-height:calc(100vh - 64px - 120px);display:grid;place-items:center;padding:40px 16px}
.card-auth{width:100%;max-width:520px;background:rgba(255,255,255,.06);border:1px solid rgba(255,255,255,.12);border-radius:16px;padding:20px}
.field{display:flex;flex-direction:column;gap:6px;margin-bottom:12px}
.field input{padding:12px 12px;border-radius:10px;border:1px solid rgba(255,255,255,.2);background:rgba(0,0,0,.15);color:#fff}
.error{background:rgba(255,90,95,.12);border:1px solid rgba(255,90,95,.25);color:#ffdada;padding:10px;border-radius:10px;margin-bottom:10px}
.success{background:rgba(198,216,112,.12);border:1px solid rgba(198,216,112,.25);color:#eaffba;padding:10px;border-radius:10px;margin-bottom:10px}
label{font-weight:600}
</style>
<?php require_once __DIR__ . '/../includes/navbar.php'; ?>
<main class="auth-wrapper">
  <form class="card-auth" method="post" action="">
    <h2>Create your account</h2>
    <?php if ($error): ?><div class="error"><?php echo htmlspecialchars($error); ?></div><?php endif; ?>
    <?php if ($success): ?><div class="success"><?php echo htmlspecialchars($success); ?></div><?php endif; ?>
    <div class="field">
      <label for="full_name">Full name</label>
      <input id="full_name" name="full_name" required>
    </div>
    <div class="field">
      <label for="email">Email</label>
      <input id="email" name="email" type="email" required>
    </div>
    <div class="field">
      <label for="password">Password</label>
      <input id="password" name="password" type="password" required>
    </div>
    <div class="field">
      <label for="confirm_password">Confirm Password</label>
      <input id="confirm_password" name="confirm_password" type="password" required>
    </div>
    <div style="display:flex;gap:10px;align-items:center;justify-content:space-between;margin-top:6px">
      <button class="btn btn-primary" type="submit">Sign Up</button>
      <a href="<?php echo url('auth/login.php'); ?>" class="muted">Have an account? Sign in</a>
    </div>
  </form>
  
</main>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>

