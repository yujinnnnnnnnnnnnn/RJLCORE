<?php
require_once __DIR__ . '/../includes/auth.php';
require_role([1,2]);
require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../config/db.php';

// Simple POS: create sale (full or installment) with single product for demo
$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $customer_email = trim($_POST['customer_email'] ?? '');
    $product_id = (int)($_POST['product_id'] ?? 0);
    $quantity = max(1, (int)($_POST['quantity'] ?? 1));
    $payment_type = in_array(($_POST['payment_type'] ?? 'full'), ['full','installment'], true) ? $_POST['payment_type'] : 'full';
    if ($customer_email && $product_id > 0) {
        $cust_stmt = safe_query('SELECT id FROM users WHERE email = ? AND role_id = 3', 's', [$customer_email]);
        $cust = $cust_stmt->get_result()->fetch_assoc();
        $prod_stmt = safe_query('SELECT id, price, stock FROM products WHERE id = ?', 'i', [$product_id]);
        $prod = $prod_stmt->get_result()->fetch_assoc();
        if ($cust && $prod && (int)$prod['stock'] >= $quantity) {
            $total = (float)$prod['price'] * $quantity;
            $paid = $payment_type === 'full' ? $total : 0.00;
            safe_query('INSERT INTO sales (customer_id, staff_id, total_amount, payment_type, amount_paid) VALUES (?,?,?,?,?)', 'iidsd', [
                (int)$cust['id'], (int)($_SESSION['user_id'] ?? null), $total, $payment_type, $paid
            ]);
            $sale_id = db()->insert_id;
            safe_query('INSERT INTO sale_items (sale_id, product_id, quantity, unit_price) VALUES (?,?,?,?)', 'iiid', [$sale_id, $product_id, $quantity, (float)$prod['price']]);
            safe_query('UPDATE products SET stock = stock - ? WHERE id = ?', 'ii', [$quantity, $product_id]);
            if ($payment_type === 'installment') {
                // Create 3 installments monthly as demo
                $per = round($total/3, 2);
                for ($i=1;$i<=3;$i++) {
                    $due = (new DateTime("+{$i} month"))->format('Y-m-d');
                    safe_query('INSERT INTO installments (sale_id, due_date, amount_due) VALUES (?,?,?)', 'isd', [$sale_id, $due, $per]);
                }
            }
            $message = 'Sale created successfully. Sale #' . $sale_id . ' — ' . '<a href="receipt.php?id=' . (int)$sale_id . '" target="_blank">View Receipt</a>';
        } else {
            $message = 'Invalid customer/product or insufficient stock.';
        }
    }
}

$page_title = 'Sales & POS • AppliancePro';
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/navbar.php';

$products = safe_query('SELECT id, name, price, stock FROM products WHERE is_active = 1 ORDER BY name ASC')->get_result()->fetch_all(MYSQLI_ASSOC);
$recent_sales = safe_query('SELECT s.id, u.full_name as customer, s.total_amount, s.payment_type, s.created_at FROM sales s JOIN users u ON u.id = s.customer_id ORDER BY s.created_at DESC LIMIT 10')->get_result()->fetch_all(MYSQLI_ASSOC);
?>
<main class="container" style="padding:32px 0 64px">
  <h2>Sales & POS</h2>
  <?php if ($message): ?><div class="card" style="padding:12px;margin-top:10px;border-left:3px solid #C6D870"><?php echo htmlspecialchars($message); ?></div><?php endif; ?>

  <div class="card revealed" style="margin-top:12px">
    <form method="post" class="grid-2">
      <div>
        <label>Customer Email</label>
        <input name="customer_email" type="email" required style="width:100%;padding:10px;border-radius:10px;border:1px solid rgba(255,255,255,.2);background:rgba(0,0,0,.15);color:#fff">
      </div>
      <div>
        <label>Product</label>
        <select name="product_id" required style="width:100%;padding:10px;border-radius:10px;border:1px solid rgba(255,255,255,.2);background:rgba(0,0,0,.15);color:#fff">
          <option value="">Select product</option>
          <?php foreach ($products as $p): ?>
          <option value="<?php echo (int)$p['id']; ?>"><?php echo htmlspecialchars($p['name']); ?> — <?php echo number_format((float)$p['price'],2); ?> (Stock: <?php echo (int)$p['stock']; ?>)</option>
          <?php endforeach; ?>
        </select>
      </div>
      <div>
        <label>Quantity</label>
        <input name="quantity" type="number" min="1" value="1" required style="width:100%;padding:10px;border-radius:10px;border:1px solid rgba(255,255,255,.2);background:rgba(0,0,0,.15);color:#fff">
      </div>
      <div>
        <label>Payment Type</label>
        <select name="payment_type" style="width:100%;padding:10px;border-radius:10px;border:1px solid rgba(255,255,255,.2);background:rgba(0,0,0,.15);color:#fff">
          <option value="full">Full Payment</option>
          <option value="installment">Installment</option>
        </select>
      </div>
      <div style="grid-column:1/-1;text-align:right">
        <button class="btn btn-primary" type="submit">Process Sale</button>
      </div>
    </form>
  </div>

  <div class="card revealed" style="margin-top:12px;overflow:auto">
    <h3>Recent Sales</h3>
    <table style="width:100%;border-collapse:collapse">
      <thead>
        <tr>
          <th style="text-align:left;padding:8px;border-bottom:1px solid rgba(255,255,255,.15)">Sale #</th>
          <th style="text-align:left;padding:8px;border-bottom:1px solid rgba(255,255,255,.15)">Customer</th>
          <th style="text-align:left;padding:8px;border-bottom:1px solid rgba(255,255,255,.15)">Type</th>
          <th style="text-align:left;padding:8px;border-bottom:1px solid rgba(255,255,255,.15)">Total</th>
          <th style="text-align:left;padding:8px;border-bottom:1px solid rgba(255,255,255,.15)">Date</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($recent_sales as $s): ?>
        <tr>
          <td style="padding:8px">#<?php echo (int)$s['id']; ?></td>
          <td style="padding:8px"><?php echo htmlspecialchars($s['customer']); ?></td>
          <td style="padding:8px"><?php echo htmlspecialchars($s['payment_type']); ?></td>
          <td style="padding:8px"><?php echo number_format((float)$s['total_amount'],2); ?></td>
          <td style="padding:8px"><?php echo htmlspecialchars($s['created_at']); ?></td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</main>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>

