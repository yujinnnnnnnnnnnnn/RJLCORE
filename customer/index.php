<?php
require_once __DIR__ . '/../app/bootstrap.php';
use App\Auth;
$user = Auth::user();
if (!$user || (int)$user['role_id'] !== 3) {
  header('Location: /public/login.php');
  exit;
}
$pageTitle = 'Customer Dashboard';
include __DIR__ . '/../views/templates/header.php';
?>
<h1>Welcome, <?= htmlspecialchars($user['full_name'] ?? 'Customer') ?></h1>
<div class="grid" style="grid-template-columns:repeat(2,1fr)">
  <div class="card">
    <h3>Purchase History</h3>
    <p class="muted">View your past purchases with product images.</p>
  </div>
  <div class="card">
    <h3>Installments</h3>
    <p class="muted">Track due dates, balances, and payments.</p>
  </div>
  <div class="card">
    <h3>Notifications</h3>
    <p class="muted">View payment reminders and updates.</p>
  </div>
  <div class="card">
    <h3>Profile</h3>
    <p class="muted">Update your contact details and change password.</p>
  </div>
</div>
<?php include __DIR__ . '/../views/templates/footer.php';
