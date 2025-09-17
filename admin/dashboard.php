<?php
require_once '../config/config.php';
require_once '../classes/Auth.php';

// Check authentication and role
require_login();
require_role(['admin', 'staff']);

// Get dashboard statistics
$database = new Database();
$db = $database->getConnection();

try {
    // Total products
    $stmt = $db->prepare("SELECT COUNT(*) as total FROM products WHERE is_active = 1");
    $stmt->execute();
    $total_products = $stmt->fetch()['total'];

    // Low stock products
    $stmt = $db->prepare("SELECT COUNT(*) as total FROM products WHERE stock_quantity <= min_stock_level AND is_active = 1");
    $stmt->execute();
    $low_stock = $stmt->fetch()['total'];

    // Total customers
    $stmt = $db->prepare("SELECT COUNT(*) as total FROM users WHERE role_id = 3 AND is_active = 1");
    $stmt->execute();
    $total_customers = $stmt->fetch()['total'];

    // Total sales this month
    $stmt = $db->prepare("SELECT COUNT(*) as total, COALESCE(SUM(total_amount), 0) as revenue FROM sales WHERE MONTH(sale_date) = MONTH(CURRENT_DATE()) AND YEAR(sale_date) = YEAR(CURRENT_DATE())");
    $stmt->execute();
    $monthly_stats = $stmt->fetch();
    $monthly_sales = $monthly_stats['total'];
    $monthly_revenue = $monthly_stats['revenue'];

    // Pending installments
    $stmt = $db->prepare("SELECT COUNT(*) as total, COALESCE(SUM(amount - paid_amount), 0) as pending_amount FROM installments WHERE status = 'pending' AND due_date <= CURDATE()");
    $stmt->execute();
    $pending_stats = $stmt->fetch();
    $pending_installments = $pending_stats['total'];
    $pending_amount = $pending_stats['pending_amount'];

    // Recent sales
    $stmt = $db->prepare("
        SELECT s.*, CONCAT(u.first_name, ' ', u.last_name) as customer_name,
               CONCAT(staff.first_name, ' ', staff.last_name) as staff_name
        FROM sales s 
        JOIN users u ON s.customer_id = u.id 
        JOIN users staff ON s.staff_id = staff.id
        ORDER BY s.sale_date DESC 
        LIMIT 5
    ");
    $stmt->execute();
    $recent_sales = $stmt->fetchAll();

} catch (PDOException $e) {
    $error_message = "Database error: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - <?php echo APP_NAME; ?></title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
    <!-- Sidebar -->
    <nav class="sidebar">
        <div style="padding: 2rem 1rem; border-bottom: 1px solid rgba(255,255,255,0.1);">
            <div style="text-align: center; color: white;">
                <div style="width: 60px; height: 60px; background: rgba(255,255,255,0.1); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 1rem; font-size: 1.5rem;">
                    <i class="fas fa-user-tie"></i>
                </div>
                <h4><?php echo $_SESSION['first_name'] . ' ' . $_SESSION['last_name']; ?></h4>
                <small><?php echo ucfirst($_SESSION['role_name']); ?></small>
            </div>
        </div>
        
        <ul class="sidebar-menu">
            <li><a href="dashboard.php" class="active"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
            <li><a href="inventory.php"><i class="fas fa-boxes"></i> Inventory</a></li>
            <li><a href="sales.php"><i class="fas fa-shopping-cart"></i> Sales</a></li>
            <li><a href="customers.php"><i class="fas fa-users"></i> Customers</a></li>
            <li><a href="installments.php"><i class="fas fa-calendar-alt"></i> Installments</a></li>
            <li><a href="transactions.php"><i class="fas fa-money-bill-wave"></i> Transactions</a></li>
            <li><a href="reports.php"><i class="fas fa-chart-bar"></i> Reports</a></li>
            <?php if ($_SESSION['role_name'] === 'admin'): ?>
            <li><a href="users.php"><i class="fas fa-user-cog"></i> User Management</a></li>
            <li><a href="settings.php"><i class="fas fa-cogs"></i> Settings</a></li>
            <?php endif; ?>
            <li><a href="profile.php"><i class="fas fa-user"></i> Profile</a></li>
            <li><a href="../logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
        </ul>
    </nav>

    <!-- Main Content -->
    <main class="main-content">
        <div class="content-header">
            <div style="display: flex; justify-content: space-between; align-items: center;">
                <div>
                    <h1><i class="fas fa-tachometer-alt"></i> Dashboard</h1>
                    <p>Welcome back, <?php echo $_SESSION['first_name']; ?>! Here's your business overview.</p>
                </div>
                <div>
                    <button class="btn btn-primary" onclick="location.reload()">
                        <i class="fas fa-sync-alt"></i> Refresh
                    </button>
                </div>
            </div>
        </div>

        <!-- Statistics Cards -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon">
                    <i class="fas fa-boxes"></i>
                </div>
                <div class="stat-number"><?php echo number_format($total_products); ?></div>
                <div class="stat-label">Total Products</div>
                <div class="mt-2">
                    <a href="inventory.php" class="btn btn-sm">View Inventory</a>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-icon">
                    <i class="fas fa-exclamation-triangle" style="color: var(--warning);"></i>
                </div>
                <div class="stat-number"><?php echo number_format($low_stock); ?></div>
                <div class="stat-label">Low Stock Items</div>
                <div class="mt-2">
                    <a href="inventory.php?filter=low_stock" class="btn btn-warning btn-sm">Check Stock</a>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-icon">
                    <i class="fas fa-users"></i>
                </div>
                <div class="stat-number"><?php echo number_format($total_customers); ?></div>
                <div class="stat-label">Total Customers</div>
                <div class="mt-2">
                    <a href="customers.php" class="btn btn-sm">View Customers</a>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-icon">
                    <i class="fas fa-shopping-cart"></i>
                </div>
                <div class="stat-number"><?php echo number_format($monthly_sales); ?></div>
                <div class="stat-label">Sales This Month</div>
                <div class="mt-2">
                    <a href="sales.php" class="btn btn-sm">View Sales</a>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-icon">
                    <i class="fas fa-dollar-sign"></i>
                </div>
                <div class="stat-number"><?php echo format_currency($monthly_revenue); ?></div>
                <div class="stat-label">Monthly Revenue</div>
                <div class="mt-2">
                    <a href="reports.php" class="btn btn-success btn-sm">View Reports</a>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-icon">
                    <i class="fas fa-clock" style="color: var(--danger);"></i>
                </div>
                <div class="stat-number"><?php echo number_format($pending_installments); ?></div>
                <div class="stat-label">Pending Payments</div>
                <div class="mt-2">
                    <a href="installments.php?filter=pending" class="btn btn-danger btn-sm">View Pending</a>
                </div>
            </div>
        </div>

        <!-- Charts Section -->
        <div class="row">
            <div class="col-8">
                <div class="card">
                    <div class="card-header">
                        <h3>Sales Overview</h3>
                    </div>
                    <canvas id="salesChart" height="100"></canvas>
                </div>
            </div>
            <div class="col-4">
                <div class="card">
                    <div class="card-header">
                        <h3>Payment Status</h3>
                    </div>
                    <canvas id="paymentChart"></canvas>
                </div>
            </div>
        </div>

        <!-- Recent Activity -->
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <div style="display: flex; justify-content: space-between; align-items: center;">
                            <h3><i class="fas fa-history"></i> Recent Sales</h3>
                            <a href="sales.php" class="btn btn-sm">View All</a>
                        </div>
                    </div>
                    
                    <?php if (empty($recent_sales)): ?>
                        <div class="text-center p-4">
                            <i class="fas fa-shopping-cart" style="font-size: 3rem; color: var(--gray); opacity: 0.5;"></i>
                            <p class="mt-3">No recent sales found.</p>
                            <a href="sales.php?action=new" class="btn btn-primary">Record New Sale</a>
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Sale ID</th>
                                        <th>Customer</th>
                                        <th>Staff</th>
                                        <th>Amount</th>
                                        <th>Payment Type</th>
                                        <th>Date</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($recent_sales as $sale): ?>
                                    <tr>
                                        <td><strong>#<?php echo str_pad($sale['id'], 6, '0', STR_PAD_LEFT); ?></strong></td>
                                        <td><?php echo htmlspecialchars($sale['customer_name']); ?></td>
                                        <td><?php echo htmlspecialchars($sale['staff_name']); ?></td>
                                        <td><strong><?php echo format_currency($sale['total_amount']); ?></strong></td>
                                        <td>
                                            <span class="badge <?php echo $sale['payment_type'] === 'full' ? 'badge-success' : 'badge-warning'; ?>">
                                                <?php echo ucfirst($sale['payment_type']); ?>
                                            </span>
                                        </td>
                                        <td><?php echo format_datetime($sale['sale_date']); ?></td>
                                        <td>
                                            <span class="badge badge-<?php 
                                                echo $sale['payment_status'] === 'paid' ? 'success' : 
                                                    ($sale['payment_status'] === 'partial' ? 'warning' : 'danger'); 
                                            ?>">
                                                <?php echo ucfirst($sale['payment_status']); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <a href="sales.php?action=view&id=<?php echo $sale['id']; ?>" class="btn btn-sm">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h3><i class="fas fa-bolt"></i> Quick Actions</h3>
                    </div>
                    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem; padding: 1rem;">
                        <a href="sales.php?action=new" class="btn btn-primary" style="padding: 1.5rem; text-align: center;">
                            <i class="fas fa-plus" style="display: block; font-size: 2rem; margin-bottom: 0.5rem;"></i>
                            Record New Sale
                        </a>
                        <a href="inventory.php?action=add" class="btn btn-success" style="padding: 1.5rem; text-align: center;">
                            <i class="fas fa-box" style="display: block; font-size: 2rem; margin-bottom: 0.5rem;"></i>
                            Add Product
                        </a>
                        <a href="customers.php?action=add" class="btn btn-info" style="padding: 1.5rem; text-align: center;">
                            <i class="fas fa-user-plus" style="display: block; font-size: 2rem; margin-bottom: 0.5rem;"></i>
                            Add Customer
                        </a>
                        <a href="reports.php" class="btn btn-warning" style="padding: 1.5rem; text-align: center;">
                            <i class="fas fa-chart-line" style="display: block; font-size: 2rem; margin-bottom: 0.5rem;"></i>
                            View Reports
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <script src="../assets/js/main.js"></script>
    <script>
        // Sales Chart
        const salesCtx = document.getElementById('salesChart').getContext('2d');
        const salesChart = new Chart(salesCtx, {
            type: 'line',
            data: {
                labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'],
                datasets: [{
                    label: 'Sales Amount',
                    data: [12000, 19000, 15000, 25000, 22000, 30000],
                    borderColor: '#C6D870',
                    backgroundColor: 'rgba(198, 216, 112, 0.1)',
                    tension: 0.4,
                    fill: true
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        display: false
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                return '$' + value.toLocaleString();
                            }
                        }
                    }
                }
            }
        });

        // Payment Status Chart
        const paymentCtx = document.getElementById('paymentChart').getContext('2d');
        const paymentChart = new Chart(paymentCtx, {
            type: 'doughnut',
            data: {
                labels: ['Paid', 'Partial', 'Pending'],
                datasets: [{
                    data: [65, 25, 10],
                    backgroundColor: ['#28a745', '#ffc107', '#dc3545'],
                    borderWidth: 2,
                    borderColor: '#fff'
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'bottom'
                    }
                }
            }
        });

        // Auto-refresh dashboard every 5 minutes
        setInterval(() => {
            location.reload();
        }, 300000);
    </script>

    <style>
        .badge {
            padding: 0.25rem 0.5rem;
            border-radius: 0.25rem;
            font-size: 0.75rem;
            font-weight: bold;
            text-transform: uppercase;
        }
        .badge-success { background-color: var(--success); color: white; }
        .badge-warning { background-color: var(--warning); color: var(--navy); }
        .badge-danger { background-color: var(--danger); color: white; }
        .badge-info { background-color: var(--info); color: white; }
    </style>
</body>
</html>