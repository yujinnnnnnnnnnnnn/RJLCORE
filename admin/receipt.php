<?php
require_once __DIR__ . '/../includes/auth.php';
require_role([1,2]);
require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../config/db.php';

$id = (int)($_GET['id'] ?? 0);
$sale = null;
if ($id) {
  $sale = safe_query('SELECT s.*, u.full_name as customer_name, u.email FROM sales s JOIN users u ON u.id = s.customer_id WHERE s.id = ?', 'i', [$id])->get_result()->fetch_assoc();
}
$items = $id ? safe_query('SELECT si.quantity, si.unit_price, p.name FROM sale_items si JOIN products p ON p.id = si.product_id WHERE si.sale_id = ?', 'i', [$id])->get_result()->fetch_all(MYSQLI_ASSOC) : [];

$page_title = 'Receipt • AppliancePro';
require_once __DIR__ . '/../includes/header.php';
?>
<style>
.receipt{max-width:720px;margin:24px auto;background:#fff;color:#000;border-radius:16px;padding:24px}
.receipt h2{margin-top:0}
.receipt table{width:100%;border-collapse:collapse}
.receipt th,.receipt td{padding:8px;border-bottom:1px solid #e5e7eb}
.print-btn{position:fixed;right:24px;top:90px}
</style>
<button class="btn btn-primary print-btn" onclick="window.print()">Print</button>
<div class="receipt">
  <h2>AppliancePro Receipt</h2>
  <?php if (!$sale): ?>
    <p>Sale not found.</p>
  <?php else: ?>
    <p>Sale #: <?php echo (int)$sale['id']; ?> — Date: <?php echo htmlspecialchars($sale['created_at']); ?></p>
    <p>Customer: <?php echo htmlspecialchars($sale['customer_name']); ?> (<?php echo htmlspecialchars($sale['email']); ?>)</p>
    <table>
      <thead>
        <tr><th>Item</th><th>Qty</th><th>Price</th><th>Subtotal</th></tr>
      </thead>
      <tbody>
        <?php $sum = 0; foreach ($items as $it): $sub = (float)$it['unit_price'] * (int)$it['quantity']; $sum += $sub; ?>
        <tr>
          <td><?php echo htmlspecialchars($it['name']); ?></td>
          <td><?php echo (int)$it['quantity']; ?></td>
          <td><?php echo number_format((float)$it['unit_price'],2); ?></td>
          <td><?php echo number_format($sub,2); ?></td>
        </tr>
        <?php endforeach; ?>
      </tbody>
      <tfoot>
        <tr><td colspan="3" style="text-align:right;font-weight:700">Total</td><td><?php echo number_format($sum,2); ?></td></tr>
        <tr><td colspan="3" style="text-align:right">Amount Paid</td><td><?php echo number_format((float)$sale['amount_paid'],2); ?></td></tr>
        <tr><td colspan="3" style="text-align:right">Payment Type</td><td><?php echo htmlspecialchars($sale['payment_type']); ?></td></tr>
      </tfoot>
    </table>
  <?php endif; ?>
</div>
<?php /* Using minimal layout for print; no navbar/footer */ ?>

