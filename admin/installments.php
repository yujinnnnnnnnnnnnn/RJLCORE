<?php
require_once __DIR__ . '/../includes/auth.php';
require_role([1,2]);
require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../config/db.php';

$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = (int)($_POST['id'] ?? 0);
    $amount = (float)($_POST['amount'] ?? 0);
    if ($id && $amount > 0) {
        safe_query('UPDATE installments SET amount_paid = LEAST(amount_due, amount_paid + ?), status = IF(amount_paid + ? >= amount_due, "paid", "partial") WHERE id = ?', 'ddi', [$amount, $amount, $id]);
        $message = 'Payment posted.';
    }
}

$rows = safe_query('SELECT i.id, u.full_name as customer, s.id as sale_id, i.due_date, i.amount_due, i.amount_paid, i.status FROM installments i JOIN sales s ON s.id = i.sale_id JOIN users u ON u.id = s.customer_id ORDER BY i.due_date ASC LIMIT 200')->get_result()->fetch_all(MYSQLI_ASSOC);

$page_title = 'Installments Management • AppliancePro';
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/navbar.php';
?>
<main class="container" style="padding:32px 0 64px">
  <h2>Installments Management</h2>
  <?php if ($message): ?><div class="card" style="padding:12px;margin-top:10px;border-left:3px solid #C6D870"><?php echo htmlspecialchars($message); ?></div><?php endif; ?>
  <div class="card revealed" style="overflow:auto">
    <table style="width:100%;border-collapse:collapse">
      <thead>
        <tr>
          <th style="text-align:left;padding:8px;border-bottom:1px solid rgba(255,255,255,.15)">Sale #</th>
          <th style="text-align:left;padding:8px;border-bottom:1px solid rgba(255,255,255,.15)">Customer</th>
          <th style="text-align:left;padding:8px;border-bottom:1px solid rgba(255,255,255,.15)">Due Date</th>
          <th style="text-align:left;padding:8px;border-bottom:1px solid rgba(255,255,255,.15)">Due</th>
          <th style="text-align:left;padding:8px;border-bottom:1px solid rgba(255,255,255,.15)">Paid</th>
          <th style="text-align:left;padding:8px;border-bottom:1px solid rgba(255,255,255,.15)">Status</th>
          <th style="text-align:left;padding:8px;border-bottom:1px solid rgba(255,255,255,.15)">Post Payment</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($rows as $r): ?>
        <tr>
          <td style="padding:8px">#<?php echo (int)$r['sale_id']; ?></td>
          <td style="padding:8px"><?php echo htmlspecialchars($r['customer']); ?></td>
          <td style="padding:8px"><?php echo htmlspecialchars($r['due_date']); ?></td>
          <td style="padding:8px"><?php echo number_format((float)$r['amount_due'],2); ?></td>
          <td style="padding:8px"><?php echo number_format((float)$r['amount_paid'],2); ?></td>
          <td style="padding:8px"><?php echo htmlspecialchars($r['status']); ?></td>
          <td style="padding:8px">
            <form method="post" style="display:flex;gap:8px">
              <input type="hidden" name="id" value="<?php echo (int)$r['id']; ?>">
              <input name="amount" type="number" step="0.01" min="0.01" placeholder="Amount" style="width:120px;padding:8px;border-radius:8px;border:1px solid rgba(255,255,255,.2);background:rgba(0,0,0,.15);color:#fff">
              <button class="btn btn-primary" type="submit">Apply</button>
            </form>
          </td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</main>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>

