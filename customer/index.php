<?php
require_once __DIR__ . '/../includes/auth.php';
require_role([3]); // customer
require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../config/db.php';

$page_title = 'Customer Dashboard • AppliancePro';
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/navbar.php';

$uid = (int)($_SESSION['user_id'] ?? 0);

// Fetch purchase history and installments overview (recent)
$recent_sales_stmt = safe_query('SELECT id, total_amount, payment_type, amount_paid, created_at FROM sales WHERE customer_id = ? ORDER BY created_at DESC LIMIT 5', 'i', [$uid]);
$recent_sales = $recent_sales_stmt->get_result()->fetch_all(MYSQLI_ASSOC);

$inst_stmt = safe_query('SELECT i.due_date, i.amount_due, i.amount_paid, i.status, s.id as sale_id FROM installments i JOIN sales s ON s.id = i.sale_id WHERE s.customer_id = ? ORDER BY i.due_date ASC LIMIT 5', 'i', [$uid]);
$upcoming = $inst_stmt->get_result()->fetch_all(MYSQLI_ASSOC);
?>
<main class="container" style="padding:32px 0 64px">
  <h2>Hello, <?php echo htmlspecialchars($_SESSION['full_name'] ?? ''); ?></h2>
  <div class="features-grid" style="margin-top:12px">
    <div class="card revealed">
      <div class="badge">Quick Links</div>
      <p style="display:flex;gap:10px;flex-wrap:wrap">
        <a class="btn btn-primary" href="<?php echo url('customer/history.php'); ?>">Purchase History</a>
        <a class="btn btn-outline" href="<?php echo url('customer/installments.php'); ?>">Installments</a>
        <a class="btn btn-outline" href="<?php echo url('customer/profile.php'); ?>">Profile</a>
      </p>
    </div>
    <div class="card revealed">
      <div class="badge">Upcoming Installments</div>
      <?php if (!$upcoming): ?>
        <p class="muted">No upcoming installments.</p>
      <?php else: ?>
        <ul>
        <?php foreach ($upcoming as $row): ?>
          <li>Sale #<?php echo (int)$row['sale_id']; ?> • Due <?php echo htmlspecialchars($row['due_date']); ?> • <?php echo htmlspecialchars($row['status']); ?> • Due: <?php echo number_format((float)$row['amount_due'],2); ?></li>
        <?php endforeach; ?>
        </ul>
      <?php endif; ?>
    </div>
    <div class="card revealed">
      <div class="badge">Recent Purchases</div>
      <?php if (!$recent_sales): ?>
        <p class="muted">No purchases yet.</p>
      <?php else: ?>
        <ul>
        <?php foreach ($recent_sales as $s): ?>
          <li>#<?php echo (int)$s['id']; ?> • <?php echo htmlspecialchars($s['payment_type']); ?> • <?php echo number_format((float)$s['total_amount'],2); ?> • <?php echo htmlspecialchars($s['created_at']); ?></li>
        <?php endforeach; ?>
        </ul>
      <?php endif; ?>
    </div>
  </div>
</main>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>

