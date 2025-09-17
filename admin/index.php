<?php
require_once __DIR__ . '/../includes/auth.php';
require_role([1,2]); // admin or staff
require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../config/db.php';

$page_title = 'Admin Dashboard • AppliancePro';
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/navbar.php';
?>
<main class="container" style="padding:32px 0 64px">
  <h2>Welcome, <?php echo htmlspecialchars($_SESSION['full_name'] ?? ''); ?></h2>
  <div class="features-grid" style="margin-top:12px">
    <a class="card revealed" href="inventory.php">
      <div class="badge">Inventory</div>
      <h3>Manage Products</h3>
      <p class="muted">Add, edit, remove products, and track stock levels.</p>
    </a>
    <a class="card revealed" href="sales.php">
      <div class="badge">Sales</div>
      <h3>Transactions & POS</h3>
      <p class="muted">Process full or installment payments and print receipts.</p>
    </a>
    <a class="card revealed" href="installments.php">
      <div class="badge">Installments</div>
      <h3>Schedules & Payments</h3>
      <p class="muted">Track schedules, post payments, and mark as paid.</p>
    </a>
    <a class="card revealed" href="customers.php">
      <div class="badge">Customers</div>
      <h3>Customer Records</h3>
      <p class="muted">View histories, due dates, and send reminders.</p>
    </a>
    <a class="card revealed" href="reports.php">
      <div class="badge">Reports</div>
      <h3>Analytics Dashboard</h3>
      <p class="muted">Charts for sales and inventory performance.</p>
    </a>
    <a class="card revealed" href="settings.php">
      <div class="badge">Settings</div>
      <h3>User Management</h3>
      <p class="muted">Manage accounts, roles, and change passwords.</p>
    </a>
  </div>
</main>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>

