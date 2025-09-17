<?php
require_once __DIR__ . '/../app/bootstrap.php';
use App\Auth;
Auth::requireRole(['Admin','Staff']);
$pageTitle = 'Dashboard — Admin/Staff';
include __DIR__ . '/../views/templates/header.php';
?>

<div style="display:flex;gap:20px">
  <aside class="sidebar">
    <nav>
      <a href="/admin/index.php">Overview</a>
      <a href="/admin/inventory.php">Inventory</a>
      <a href="/admin/sales.php">Sales & POS</a>
      <a href="/admin/customers.php">Customers</a>
      <a href="/admin/reports.php">Reports</a>
      <a href="/admin/settings.php">Settings</a>
      <a href="/public/logout.php">Logout</a>
    </nav>
  </aside>
  <section class="content" style="flex:1">
    <h1>Performance Dashboard</h1>
    <canvas id="salesChart" height="110"></canvas>
    <script>
      const ctx = document.getElementById('salesChart');
      if (window.Chart && ctx) {
        const chart = new Chart(ctx, {type:'line', data:{labels:['Jan','Feb','Mar','Apr','May','Jun'], datasets:[{label:'Sales', data:[12,19,7,11,15,21], borderColor:'#113F67', backgroundColor:'rgba(17,63,103,.1)', tension:.3}]}, options:{plugins:{legend:{display:true}}, scales:{y:{beginAtZero:true}}}});
      }
    </script>
  </section>
</div>

<?php include __DIR__ . '/../views/templates/footer.php';
