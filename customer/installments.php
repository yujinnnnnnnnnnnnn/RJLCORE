<?php
require_once __DIR__ . '/../includes/auth.php';
require_role([3]);
require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../config/db.php';

$uid = (int)($_SESSION['user_id'] ?? 0);

$rows = safe_query('SELECT i.id, s.id as sale_id, i.due_date, i.amount_due, i.amount_paid, i.status FROM installments i JOIN sales s ON s.id = i.sale_id WHERE s.customer_id = ? ORDER BY i.due_date ASC', 'i', [$uid])->get_result()->fetch_all(MYSQLI_ASSOC);

$page_title = 'Installments • AppliancePro';
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/navbar.php';
?>
<main class="container" style="padding:32px 0 64px">
  <h2>Installments</h2>
  <div class="card revealed" style="overflow:auto">
    <table style="width:100%;border-collapse:collapse">
      <thead>
        <tr>
          <th style="text-align:left;padding:8px;border-bottom:1px solid rgba(255,255,255,.15)">Sale #</th>
          <th style="text-align:left;padding:8px;border-bottom:1px solid rgba(255,255,255,.15)">Due Date</th>
          <th style="text-align:left;padding:8px;border-bottom:1px solid rgba(255,255,255,.15)">Amount Due</th>
          <th style="text-align:left;padding:8px;border-bottom:1px solid rgba(255,255,255,.15)">Amount Paid</th>
          <th style="text-align:left;padding:8px;border-bottom:1px solid rgba(255,255,255,.15)">Status</th>
        </tr>
      </thead>
      <tbody>
        <?php if (!$rows): ?>
        <tr><td colspan="5" class="muted" style="padding:8px">No installment records.</td></tr>
        <?php else: foreach ($rows as $r): ?>
        <tr>
          <td style="padding:8px">#<?php echo (int)$r['sale_id']; ?></td>
          <td style="padding:8px"><?php echo htmlspecialchars($r['due_date']); ?></td>
          <td style="padding:8px"><?php echo number_format((float)$r['amount_due'],2); ?></td>
          <td style="padding:8px"><?php echo number_format((float)$r['amount_paid'],2); ?></td>
          <td style="padding:8px"><?php echo htmlspecialchars($r['status']); ?></td>
        </tr>
        <?php endforeach; endif; ?>
      </tbody>
    </table>
  </div>
</main>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>

