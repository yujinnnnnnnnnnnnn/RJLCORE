<?php
require_once __DIR__ . '/../includes/auth.php';
require_role([3]);
require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../config/db.php';

$page_title = 'Purchase History • AppliancePro';
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/navbar.php';

$uid = (int)($_SESSION['user_id'] ?? 0);
$stmt = safe_query('SELECT id, total_amount, payment_type, amount_paid, created_at FROM sales WHERE customer_id = ? ORDER BY created_at DESC', 'i', [$uid]);
$rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
?>
<main class="container" style="padding:32px 0 64px">
  <h2>Purchase History</h2>
  <div class="card revealed" style="overflow:auto">
    <table style="width:100%;border-collapse:collapse">
      <thead>
        <tr>
          <th style="text-align:left;padding:8px;border-bottom:1px solid rgba(255,255,255,.15)">Sale #</th>
          <th style="text-align:left;padding:8px;border-bottom:1px solid rgba(255,255,255,.15)">Type</th>
          <th style="text-align:left;padding:8px;border-bottom:1px solid rgba(255,255,255,.15)">Total</th>
          <th style="text-align:left;padding:8px;border-bottom:1px solid rgba(255,255,255,.15)">Paid</th>
          <th style="text-align:left;padding:8px;border-bottom:1px solid rgba(255,255,255,.15)">Date</th>
        </tr>
      </thead>
      <tbody>
        <?php if (!$rows): ?>
        <tr><td colspan="5" style="padding:10px" class="muted">No records found.</td></tr>
        <?php else: foreach ($rows as $r): ?>
        <tr>
          <td style="padding:8px">#<?php echo (int)$r['id']; ?></td>
          <td style="padding:8px"><?php echo htmlspecialchars($r['payment_type']); ?></td>
          <td style="padding:8px"><?php echo number_format((float)$r['total_amount'],2); ?></td>
          <td style="padding:8px"><?php echo number_format((float)$r['amount_paid'],2); ?></td>
          <td style="padding:8px"><?php echo htmlspecialchars($r['created_at']); ?></td>
        </tr>
        <?php endforeach; endif; ?>
      </tbody>
    </table>
  </div>
</main>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>

