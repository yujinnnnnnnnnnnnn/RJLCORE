<?php
require_once __DIR__ . '/../app/bootstrap.php';
use App\Security;
use App\Auth;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $csrf = $_POST['csrf'] ?? '';
    if (!Security::validateCsrf($csrf)) {
        $error = 'Invalid CSRF token.';
    } else if (Auth::attempt($email, $password)) {
        $user = Auth::user();
        if ($user && ((int)$user['role_id'] === 1 || (int)$user['role_id'] === 2)) {
            header('Location: /admin/index.php');
            exit;
        }
        header('Location: /customer/index.php');
        exit;
    } else {
        $error = 'Invalid credentials.';
    }
}

$pageTitle = 'Login — Appliances Store';
include __DIR__ . '/../views/templates/header.php';
?>
<div class="form-card">
  <h2 style="margin-top:0;color:#113F67">Sign In</h2>
  <?php if (!empty($error)): ?>
  <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
  <?php endif; ?>
  <form method="post" action="">
    <input type="hidden" name="csrf" value="<?= Security::csrfToken() ?>">
    <div class="form-group">
      <label>Email</label>
      <input type="email" name="email" required>
    </div>
    <div class="form-group">
      <label>Password</label>
      <input type="password" name="password" required>
    </div>
    <button class="btn btn-primary" type="submit">Login</button>
    <a class="btn btn-outline" href="/public/register.php" style="margin-left:8px">Create Account</a>
  </form>
  <p class="muted" style="margin-top:10px"><a href="/public/reset.php">Forgot password?</a></p>
  <p class="muted">Customer? Use the same login form and you will be redirected to your portal.</p>
</div>
<?php include __DIR__ . '/../views/templates/footer.php';
