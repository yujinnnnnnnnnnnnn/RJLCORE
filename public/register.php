<?php
require_once __DIR__ . '/../app/bootstrap.php';
use App\Security;
use App\Models\User;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $csrf = $_POST['csrf'] ?? '';
    if (!Security::validateCsrf($csrf)) {
        $error = 'Invalid CSRF token.';
    } else {
        $name = trim($_POST['full_name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $phone = trim($_POST['phone'] ?? '');
        $pass = $_POST['password'] ?? '';
        $pass2 = $_POST['password_confirm'] ?? '';
        if ($pass !== $pass2) {
            $error = 'Passwords do not match.';
        } else if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error = 'Invalid email address.';
        } else if (strlen($pass) < 6) {
            $error = 'Password must be at least 6 characters.';
        } else {
            try {
                $id = User::createCustomer($name, $email, $pass, $phone);
                $success = 'Account created. You can now log in.';
            } catch (\Throwable $e) {
                $error = 'Could not create account. Email may already be taken.';
            }
        }
    }
}

$pageTitle = 'Register — Appliances Store';
include __DIR__ . '/../views/templates/header.php';
?>
<div class="form-card">
  <h2 style="margin-top:0;color:#113F67">Create Customer Account</h2>
  <?php if (!empty($error)): ?>
  <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
  <?php endif; ?>
  <?php if (!empty($success)): ?>
  <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
  <?php endif; ?>
  <form method="post" action="">
    <input type="hidden" name="csrf" value="<?= Security::csrfToken() ?>">
    <div class="form-group">
      <label>Full Name</label>
      <input type="text" name="full_name" required>
    </div>
    <div class="form-group">
      <label>Email</label>
      <input type="email" name="email" required>
    </div>
    <div class="form-group">
      <label>Phone</label>
      <input type="text" name="phone">
    </div>
    <div class="form-group">
      <label>Password</label>
      <input type="password" name="password" required>
    </div>
    <div class="form-group">
      <label>Confirm Password</label>
      <input type="password" name="password_confirm" required>
    </div>
    <button class="btn btn-primary" type="submit">Sign Up</button>
  </form>
</div>
<?php include __DIR__ . '/../views/templates/footer.php';
