<?php
/**
 * Admin/Staff Dashboard for Appliances Management System
 */

require_once '../config/config.php';

// Check if user is logged in and has admin/staff role
if (!isLoggedIn() || !hasAnyRole(['admin', 'staff'])) {
    redirectTo('../login.php');
}

$user = getCurrentUser();

// Get dashboard statistics
$total_products = fetchOne("SELECT COUNT(*) as count FROM products WHERE status = 'active'")['count'] ?? 0;
$low_stock_products = fetchOne("SELECT COUNT(*) as count FROM products WHERE stock_quantity <= min_stock_level AND status = 'active'")['count'] ?? 0;
$total_customers = fetchOne("SELECT COUNT(*) as count FROM users WHERE role = 'customer' AND status = 'active'")['count'] ?? 0;
$pending_payments = fetchOne("SELECT COUNT(*) as count FROM installment_payments WHERE status = 'pending' AND due_date <= DATE_ADD(NOW(), INTERVAL 7 DAY)")['count'] ?? 0;

// Get recent sales
$recent_sales = fetchAll("
    SELECT s.*, u.first_name, u.last_name, u.email 
    FROM sales s 
    JOIN users u ON s.customer_id = u.user_id 
    ORDER BY s.sale_date DESC 
    LIMIT 10
");

// Get low stock products
$low_stock_items = fetchAll("
    SELECT p.*, c.category_name 
    FROM products p 
    LEFT JOIN categories c ON p.category_id = c.category_id 
    WHERE p.stock_quantity <= p.min_stock_level AND p.status = 'active' 
    ORDER BY p.stock_quantity ASC 
    LIMIT 10
");

// Get recent notifications
$recent_notifications = fetchAll("
    SELECT n.*, u.first_name, u.last_name 
    FROM notifications n 
    JOIN users u ON n.user_id = u.user_id 
    ORDER BY n.created_at DESC 
    LIMIT 5
");

// Calculate monthly sales
$monthly_sales = fetchOne("
    SELECT COALESCE(SUM(total_amount), 0) as total 
    FROM sales 
    WHERE MONTH(sale_date) = MONTH(NOW()) 
    AND YEAR(sale_date) = YEAR(NOW())
")['total'] ?? 0;

$page_title = 'Dashboard';
include 'includes/header.php';
?>

<div class="dashboard-container">
    <!-- Stats Cards -->
    <div class="stats-row">
        <div class="stat-card">
            <div class="stat-icon">
                <i class="fas fa-box"></i>
            </div>
            <div class="stat-content">
                <div class="stat-number"><?php echo number_format($total_products); ?></div>
                <div class="stat-label">Active Products</div>
            </div>
            <div class="stat-trend positive">
                <i class="fas fa-arrow-up"></i>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon warning">
                <i class="fas fa-exclamation-triangle"></i>
            </div>
            <div class="stat-content">
                <div class="stat-number"><?php echo number_format($low_stock_products); ?></div>
                <div class="stat-label">Low Stock Items</div>
            </div>
            <div class="stat-action">
                <a href="inventory.php?filter=low_stock" class="btn btn-sm btn-warning">View</a>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon">
                <i class="fas fa-users"></i>
            </div>
            <div class="stat-content">
                <div class="stat-number"><?php echo number_format($total_customers); ?></div>
                <div class="stat-label">Total Customers</div>
            </div>
            <div class="stat-trend positive">
                <i class="fas fa-arrow-up"></i>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon primary">
                <i class="fas fa-credit-card"></i>
            </div>
            <div class="stat-content">
                <div class="stat-number"><?php echo number_format($pending_payments); ?></div>
                <div class="stat-label">Pending Payments</div>
            </div>
            <div class="stat-action">
                <a href="payments.php?filter=pending" class="btn btn-sm btn-primary">Manage</a>
            </div>
        </div>
    </div>

    <!-- Monthly Sales Card -->
    <div class="monthly-sales-card">
        <div class="card-header">
            <h3>Monthly Sales</h3>
            <div class="card-actions">
                <a href="reports.php" class="btn btn-outline btn-sm">View Reports</a>
            </div>
        </div>
        <div class="card-body">
            <div class="sales-amount">
                <?php echo formatCurrency($monthly_sales); ?>
            </div>
            <div class="sales-period">
                <?php echo date('F Y'); ?>
            </div>
        </div>
    </div>

    <!-- Dashboard Grid -->
    <div class="dashboard-grid">
        <!-- Recent Sales -->
        <div class="dashboard-widget">
            <div class="widget-header">
                <h3>Recent Sales</h3>
                <a href="sales.php" class="btn btn-sm btn-outline">View All</a>
            </div>
            <div class="widget-content">
                <?php if (empty($recent_sales)): ?>
                    <div class="empty-state">
                        <i class="fas fa-shopping-cart"></i>
                        <p>No recent sales</p>
                    </div>
                <?php else: ?>
                    <div class="sales-list">
                        <?php foreach ($recent_sales as $sale): ?>
                            <div class="sale-item">
                                <div class="sale-customer">
                                    <strong><?php echo htmlspecialchars($sale['first_name'] . ' ' . $sale['last_name']); ?></strong>
                                    <small><?php echo htmlspecialchars($sale['email']); ?></small>
                                </div>
                                <div class="sale-details">
                                    <div class="sale-amount"><?php echo formatCurrency($sale['total_amount']); ?></div>
                                    <div class="sale-date"><?php echo formatDate($sale['sale_date']); ?></div>
                                </div>
                                <div class="sale-status">
                                    <span class="badge badge-<?php echo $sale['payment_status'] === 'completed' ? 'success' : ($sale['payment_status'] === 'partial' ? 'warning' : 'secondary'); ?>">
                                        <?php echo ucfirst($sale['payment_status']); ?>
                                    </span>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Low Stock Alert -->
        <div class="dashboard-widget">
            <div class="widget-header">
                <h3>Low Stock Alert</h3>
                <a href="inventory.php" class="btn btn-sm btn-outline">Manage Inventory</a>
            </div>
            <div class="widget-content">
                <?php if (empty($low_stock_items)): ?>
                    <div class="empty-state success">
                        <i class="fas fa-check-circle"></i>
                        <p>All items are well stocked</p>
                    </div>
                <?php else: ?>
                    <div class="stock-list">
                        <?php foreach ($low_stock_items as $item): ?>
                            <div class="stock-item">
                                <div class="item-info">
                                    <strong><?php echo htmlspecialchars($item['product_name']); ?></strong>
                                    <small><?php echo htmlspecialchars($item['category_name']); ?></small>
                                </div>
                                <div class="stock-level">
                                    <span class="stock-number <?php echo $item['stock_quantity'] == 0 ? 'out-of-stock' : 'low-stock'; ?>">
                                        <?php echo $item['stock_quantity']; ?>
                                    </span>
                                    <small>/ <?php echo $item['min_stock_level']; ?> min</small>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="dashboard-widget">
            <div class="widget-header">
                <h3>Quick Actions</h3>
            </div>
            <div class="widget-content">
                <div class="quick-actions">
                    <a href="inventory.php?action=add" class="quick-action-btn">
                        <i class="fas fa-plus-circle"></i>
                        <span>Add Product</span>
                    </a>
                    <a href="sales.php?action=new" class="quick-action-btn">
                        <i class="fas fa-shopping-cart"></i>
                        <span>New Sale</span>
                    </a>
                    <a href="customers.php?action=add" class="quick-action-btn">
                        <i class="fas fa-user-plus"></i>
                        <span>Add Customer</span>
                    </a>
                    <a href="reports.php" class="quick-action-btn">
                        <i class="fas fa-chart-bar"></i>
                        <span>View Reports</span>
                    </a>
                    <?php if ($user['role'] === 'admin'): ?>
                        <a href="users.php?action=add" class="quick-action-btn">
                            <i class="fas fa-user-cog"></i>
                            <span>Add User</span>
                        </a>
                        <a href="settings.php" class="quick-action-btn">
                            <i class="fas fa-cogs"></i>
                            <span>Settings</span>
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Recent Notifications -->
        <div class="dashboard-widget">
            <div class="widget-header">
                <h3>Recent Activity</h3>
                <a href="notifications.php" class="btn btn-sm btn-outline">View All</a>
            </div>
            <div class="widget-content">
                <?php if (empty($recent_notifications)): ?>
                    <div class="empty-state">
                        <i class="fas fa-bell"></i>
                        <p>No recent notifications</p>
                    </div>
                <?php else: ?>
                    <div class="notifications-list">
                        <?php foreach ($recent_notifications as $notification): ?>
                            <div class="notification-item">
                                <div class="notification-icon">
                                    <i class="fas fa-<?php echo $notification['type'] === 'payment_reminder' ? 'credit-card' : 'info-circle'; ?>"></i>
                                </div>
                                <div class="notification-content">
                                    <div class="notification-title"><?php echo htmlspecialchars($notification['title']); ?></div>
                                    <div class="notification-user">
                                        <?php echo htmlspecialchars($notification['first_name'] . ' ' . $notification['last_name']); ?>
                                    </div>
                                    <div class="notification-time"><?php echo formatDateTime($notification['created_at']); ?></div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<style>
/* Dashboard Specific Styles */
.dashboard-container {
    padding: 2rem;
}

.stats-row {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 1.5rem;
    margin-bottom: 2rem;
}

.stat-card {
    background: var(--white);
    border-radius: 10px;
    padding: 1.5rem;
    box-shadow: var(--shadow-sm);
    display: flex;
    align-items: center;
    gap: 1rem;
    transition: all var(--transition-normal);
}

.stat-card:hover {
    transform: translateY(-3px);
    box-shadow: var(--shadow-md);
}

.stat-icon {
    width: 60px;
    height: 60px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.5rem;
    background: var(--primary-green);
    color: var(--navy-blue);
}

.stat-icon.warning {
    background: var(--warning);
    color: var(--white);
}

.stat-icon.primary {
    background: var(--navy-blue);
    color: var(--white);
}

.stat-content {
    flex: 1;
}

.stat-number {
    font-size: 2rem;
    font-weight: bold;
    color: var(--navy-blue);
    margin-bottom: 0.25rem;
}

.stat-label {
    color: var(--medium-gray);
    font-size: 0.9rem;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.stat-trend {
    color: var(--success);
    font-size: 1.2rem;
}

.stat-action {
    margin-left: auto;
}

.monthly-sales-card {
    background: var(--gradient-navy);
    color: var(--white);
    border-radius: 15px;
    padding: 2rem;
    margin-bottom: 2rem;
    text-align: center;
}

.monthly-sales-card .card-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 1.5rem;
}

.monthly-sales-card h3 {
    color: var(--white);
    margin: 0;
}

.sales-amount {
    font-size: 3rem;
    font-weight: bold;
    margin-bottom: 0.5rem;
    color: var(--primary-green);
}

.sales-period {
    opacity: 0.8;
    font-size: 1.1rem;
}

.dashboard-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(400px, 1fr));
    gap: 2rem;
}

.dashboard-widget {
    background: var(--white);
    border-radius: 10px;
    box-shadow: var(--shadow-sm);
    overflow: hidden;
}

.widget-header {
    padding: 1.5rem;
    border-bottom: 1px solid #e9ecef;
    display: flex;
    justify-content: space-between;
    align-items: center;
    background: var(--secondary-beige);
}

.widget-header h3 {
    margin: 0;
    color: var(--navy-blue);
    font-size: 1.2rem;
}

.widget-content {
    padding: 1.5rem;
}

.empty-state {
    text-align: center;
    padding: 2rem;
    color: var(--medium-gray);
}

.empty-state i {
    font-size: 3rem;
    margin-bottom: 1rem;
    opacity: 0.5;
}

.empty-state.success {
    color: var(--success);
}

.sales-list,
.stock-list,
.notifications-list {
    display: flex;
    flex-direction: column;
    gap: 1rem;
}

.sale-item,
.stock-item,
.notification-item {
    display: flex;
    align-items: center;
    gap: 1rem;
    padding: 1rem;
    background: var(--light-gray);
    border-radius: 8px;
    transition: all var(--transition-fast);
}

.sale-item:hover,
.stock-item:hover,
.notification-item:hover {
    background: #e9ecef;
    transform: translateX(5px);
}

.sale-customer,
.item-info,
.notification-content {
    flex: 1;
}

.sale-customer strong,
.item-info strong {
    display: block;
    color: var(--navy-blue);
    margin-bottom: 0.25rem;
}

.sale-customer small,
.item-info small {
    color: var(--medium-gray);
    font-size: 0.85rem;
}

.sale-details {
    text-align: right;
}

.sale-amount {
    font-weight: bold;
    color: var(--navy-blue);
    margin-bottom: 0.25rem;
}

.sale-date {
    color: var(--medium-gray);
    font-size: 0.85rem;
}

.stock-level {
    text-align: right;
}

.stock-number {
    font-size: 1.2rem;
    font-weight: bold;
    padding: 0.25rem 0.5rem;
    border-radius: 4px;
}

.stock-number.low-stock {
    background: var(--warning);
    color: var(--white);
}

.stock-number.out-of-stock {
    background: var(--danger);
    color: var(--white);
}

.quick-actions {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
    gap: 1rem;
}

.quick-action-btn {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 0.5rem;
    padding: 1.5rem;
    background: var(--light-gray);
    color: var(--navy-blue);
    text-decoration: none;
    border-radius: 8px;
    transition: all var(--transition-fast);
}

.quick-action-btn:hover {
    background: var(--primary-green);
    color: var(--navy-blue);
    transform: translateY(-3px);
}

.quick-action-btn i {
    font-size: 2rem;
}

.notification-icon {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    background: var(--primary-green);
    color: var(--navy-blue);
    display: flex;
    align-items: center;
    justify-content: center;
}

.notification-title {
    font-weight: 600;
    color: var(--navy-blue);
    margin-bottom: 0.25rem;
}

.notification-user {
    color: var(--medium-gray);
    font-size: 0.9rem;
    margin-bottom: 0.25rem;
}

.notification-time {
    color: var(--medium-gray);
    font-size: 0.8rem;
}

/* Responsive Design */
@media (max-width: 768px) {
    .dashboard-container {
        padding: 1rem;
    }
    
    .stats-row {
        grid-template-columns: 1fr;
        gap: 1rem;
    }
    
    .dashboard-grid {
        grid-template-columns: 1fr;
        gap: 1rem;
    }
    
    .monthly-sales-card .card-header {
        flex-direction: column;
        gap: 1rem;
        text-align: center;
    }
    
    .sales-amount {
        font-size: 2rem;
    }
    
    .quick-actions {
        grid-template-columns: repeat(2, 1fr);
    }
}
</style>

<?php include 'includes/footer.php'; ?>