<?php
require_once __DIR__ . '/../includes/auth.php';
require_role([1,2]);
require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../config/db.php';

$page_title = 'Reports & Analytics • AppliancePro';
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/navbar.php';

$sales_by_day = safe_query('SELECT DATE(created_at) as day, SUM(total_amount) as total, COUNT(*) as orders FROM sales GROUP BY DATE(created_at) ORDER BY day DESC LIMIT 14')->get_result()->fetch_all(MYSQLI_ASSOC);
$stock_low = safe_query('SELECT name, stock FROM products WHERE stock < 5 ORDER BY stock ASC LIMIT 10')->get_result()->fetch_all(MYSQLI_ASSOC);
?>
<main class="container" style="padding:32px 0 64px">
  <h2>Reports & Analytics</h2>
  <div class="features-grid" style="margin-top:12px">
    <div class="card revealed">
      <div class="badge">Sales (14 days)</div>
      <canvas id="salesChart" height="140"></canvas>
    </div>
    <div class="card revealed">
      <div class="badge">Low Stock</div>
      <?php if (!$stock_low): ?><p class="muted">No low-stock products.</p><?php else: ?>
      <ul>
        <?php foreach ($stock_low as $s): ?>
          <li><?php echo htmlspecialchars($s['name']); ?> — Stock: <?php echo (int)$s['stock']; ?></li>
        <?php endforeach; ?>
      </ul>
      <?php endif; ?>
    </div>
  </div>
</main>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
const ctx = document.getElementById('salesChart').getContext('2d');
const chart = new Chart(ctx, {
  type: 'line',
  data: {
    labels: <?php echo json_encode(array_reverse(array_column($sales_by_day, 'day'))); ?>,
    datasets: [{
      label: 'Sales',
      data: <?php echo json_encode(array_reverse(array_map('floatval', array_column($sales_by_day, 'total')))); ?>,
      borderColor: '#C6D870',
      backgroundColor: 'rgba(198,216,112,.2)'
    }]
  },
  options: { plugins: { legend: { labels: { color: '#fff' } } }, scales: { x: { ticks: { color: '#fff' } }, y: { ticks: { color: '#fff' } } } }
});
</script>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>

