<?php
/**
 * Customer Dashboard for Appliances Management System
 */

require_once '../config/config.php';

// Check if user is logged in and is a customer
if (!isLoggedIn() || !hasRole('customer')) {
    redirectTo('../login.php');
}

$user = getCurrentUser();

// Get customer statistics
$total_purchases = fetchOne("SELECT COUNT(*) as count FROM sales WHERE customer_id = ?", [$user['user_id']])['count'] ?? 0;
$total_spent = fetchOne("SELECT COALESCE(SUM(total_amount), 0) as total FROM sales WHERE customer_id = ?", [$user['user_id']])['total'] ?? 0;
$pending_payments = fetchOne("
    SELECT COUNT(*) as count 
    FROM installment_payments ip 
    JOIN installment_plans ipl ON ip.plan_id = ipl.plan_id 
    JOIN sales s ON ipl.sale_id = s.sale_id 
    WHERE s.customer_id = ? AND ip.status = 'pending'
", [$user['user_id']])['count'] ?? 0;

// Get recent purchases
$recent_purchases = fetchAll("
    SELECT s.*, 
           GROUP_CONCAT(CONCAT(si.quantity, 'x ', p.product_name) SEPARATOR ', ') as items
    FROM sales s
    LEFT JOIN sale_items si ON s.sale_id = si.sale_id
    LEFT JOIN products p ON si.product_id = p.product_id
    WHERE s.customer_id = ?
    GROUP BY s.sale_id
    ORDER BY s.sale_date DESC
    LIMIT 5
", [$user['user_id']]);

// Get upcoming payments
$upcoming_payments = fetchAll("
    SELECT ip.*, ipl.*, s.sale_id, s.total_amount
    FROM installment_payments ip
    JOIN installment_plans ipl ON ip.plan_id = ipl.plan_id
    JOIN sales s ON ipl.sale_id = s.sale_id
    WHERE s.customer_id = ? AND ip.status = 'pending'
    ORDER BY ip.due_date ASC
    LIMIT 5
", [$user['user_id']]);

$page_title = 'My Dashboard';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title; ?> - <?php echo APP_NAME; ?></title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body class="customer-body">
    <!-- Customer Header -->
    <header class="customer-header">
        <div class="container">
            <nav class="navbar">
                <a href="../index.php" class="logo">
                    <i class="fas fa-plug"></i>
                    <span><?php echo APP_NAME; ?></span>
                </a>
                
                <ul class="nav-menu">
                    <li class="nav-item">
                        <a href="dashboard.php" class="active">Dashboard</a>
                    </li>
                    <li class="nav-item">
                        <a href="purchases.php">My Purchases</a>
                    </li>
                    <li class="nav-item">
                        <a href="payments.php">Payments</a>
                    </li>
                    <li class="nav-item">
                        <a href="profile.php">Profile</a>
                    </li>
                    <li class="nav-item">
                        <a href="../index.php">Browse Products</a>
                    </li>
                    <li class="nav-item dropdown">
                        <a href="#" class="dropdown-toggle">
                            <i class="fas fa-user-circle"></i>
                            <?php echo htmlspecialchars($user['first_name']); ?>
                        </a>
                        <div class="dropdown-menu">
                            <a href="profile.php">My Profile</a>
                            <a href="../logout.php">Logout</a>
                        </div>
                    </li>
                </ul>
                
                <div class="menu-toggle">
                    <span></span>
                    <span></span>
                    <span></span>
                </div>
            </nav>
        </div>
    </header>

    <!-- Main Content -->
    <main class="customer-main">
        <div class="container">
            <!-- Welcome Section -->
            <div class="welcome-section">
                <h1>Welcome back, <?php echo htmlspecialchars($user['first_name']); ?>!</h1>
                <p>Here's an overview of your account and recent activity.</p>
            </div>

            <!-- Stats Cards -->
            <div class="stats-row">
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-shopping-bag"></i>
                    </div>
                    <div class="stat-content">
                        <div class="stat-number"><?php echo number_format($total_purchases); ?></div>
                        <div class="stat-label">Total Purchases</div>
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-dollar-sign"></i>
                    </div>
                    <div class="stat-content">
                        <div class="stat-number"><?php echo formatCurrency($total_spent); ?></div>
                        <div class="stat-label">Total Spent</div>
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-icon warning">
                        <i class="fas fa-credit-card"></i>
                    </div>
                    <div class="stat-content">
                        <div class="stat-number"><?php echo number_format($pending_payments); ?></div>
                        <div class="stat-label">Pending Payments</div>
                    </div>
                    <?php if ($pending_payments > 0): ?>
                        <div class="stat-action">
                            <a href="payments.php" class="btn btn-sm btn-warning">View</a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Dashboard Grid -->
            <div class="dashboard-grid">
                <!-- Recent Purchases -->
                <div class="dashboard-widget">
                    <div class="widget-header">
                        <h3>Recent Purchases</h3>
                        <a href="purchases.php" class="btn btn-sm btn-outline">View All</a>
                    </div>
                    <div class="widget-content">
                        <?php if (empty($recent_purchases)): ?>
                            <div class="empty-state">
                                <i class="fas fa-shopping-cart"></i>
                                <p>No purchases yet</p>
                                <a href="../index.php" class="btn btn-primary">Start Shopping</a>
                            </div>
                        <?php else: ?>
                            <div class="purchases-list">
                                <?php foreach ($recent_purchases as $purchase): ?>
                                    <div class="purchase-item">
                                        <div class="purchase-details">
                                            <div class="purchase-items">
                                                <strong><?php echo htmlspecialchars($purchase['items']); ?></strong>
                                            </div>
                                            <div class="purchase-date">
                                                <?php echo formatDate($purchase['sale_date']); ?>
                                            </div>
                                        </div>
                                        <div class="purchase-amount">
                                            <?php echo formatCurrency($purchase['total_amount']); ?>
                                        </div>
                                        <div class="purchase-status">
                                            <span class="badge badge-<?php echo $purchase['payment_status'] === 'completed' ? 'success' : ($purchase['payment_status'] === 'partial' ? 'warning' : 'secondary'); ?>">
                                                <?php echo ucfirst($purchase['payment_status']); ?>
                                            </span>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Upcoming Payments -->
                <div class="dashboard-widget">
                    <div class="widget-header">
                        <h3>Upcoming Payments</h3>
                        <a href="payments.php" class="btn btn-sm btn-outline">View All</a>
                    </div>
                    <div class="widget-content">
                        <?php if (empty($upcoming_payments)): ?>
                            <div class="empty-state success">
                                <i class="fas fa-check-circle"></i>
                                <p>No pending payments</p>
                            </div>
                        <?php else: ?>
                            <div class="payments-list">
                                <?php foreach ($upcoming_payments as $payment): ?>
                                    <div class="payment-item">
                                        <div class="payment-details">
                                            <div class="payment-info">
                                                <strong>Payment #<?php echo $payment['payment_number']; ?></strong>
                                                <small>Sale #<?php echo $payment['sale_id']; ?></small>
                                            </div>
                                            <div class="payment-due">
                                                Due: <?php echo formatDate($payment['due_date']); ?>
                                            </div>
                                        </div>
                                        <div class="payment-amount">
                                            <?php echo formatCurrency($payment['amount_due']); ?>
                                        </div>
                                        <div class="payment-status">
                                            <?php
                                            $due_date = strtotime($payment['due_date']);
                                            $today = strtotime('today');
                                            $status_class = 'secondary';
                                            
                                            if ($due_date < $today) {
                                                $status_class = 'danger';
                                                $status_text = 'Overdue';
                                            } elseif ($due_date <= strtotime('+7 days')) {
                                                $status_class = 'warning';
                                                $status_text = 'Due Soon';
                                            } else {
                                                $status_text = 'Pending';
                                            }
                                            ?>
                                            <span class="badge badge-<?php echo $status_class; ?>">
                                                <?php echo $status_text; ?>
                                            </span>
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
                            <a href="../index.php" class="quick-action-btn">
                                <i class="fas fa-shopping-bag"></i>
                                <span>Browse Products</span>
                            </a>
                            <a href="purchases.php" class="quick-action-btn">
                                <i class="fas fa-receipt"></i>
                                <span>View Purchases</span>
                            </a>
                            <a href="payments.php" class="quick-action-btn">
                                <i class="fas fa-credit-card"></i>
                                <span>Manage Payments</span>
                            </a>
                            <a href="profile.php" class="quick-action-btn">
                                <i class="fas fa-user-cog"></i>
                                <span>Update Profile</span>
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Account Information -->
                <div class="dashboard-widget">
                    <div class="widget-header">
                        <h3>Account Information</h3>
                    </div>
                    <div class="widget-content">
                        <div class="account-info">
                            <div class="info-item">
                                <label>Name</label>
                                <span><?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?></span>
                            </div>
                            <div class="info-item">
                                <label>Email</label>
                                <span><?php echo htmlspecialchars($user['email']); ?></span>
                            </div>
                            <div class="info-item">
                                <label>Phone</label>
                                <span><?php echo htmlspecialchars($user['phone'] ?: 'Not provided'); ?></span>
                            </div>
                            <div class="info-item">
                                <label>Member Since</label>
                                <span><?php echo formatDate($user['created_at']); ?></span>
                            </div>
                        </div>
                        <div class="account-actions">
                            <a href="profile.php" class="btn btn-primary btn-sm">
                                <i class="fas fa-edit"></i> Update Profile
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <!-- Footer -->
    <footer class="customer-footer">
        <div class="container">
            <div class="footer-content">
                <div class="footer-section">
                    <div class="footer-logo">
                        <i class="fas fa-plug"></i>
                        <span><?php echo APP_NAME; ?></span>
                    </div>
                    <p>Your trusted partner for premium home appliances.</p>
                </div>
                
                <div class="footer-links">
                    <h4>Customer Support</h4>
                    <ul>
                        <li><a href="../index.php#contact">Contact Us</a></li>
                        <li><a href="../about.php">About Us</a></li>
                        <li><a href="#">Help Center</a></li>
                    </ul>
                </div>
            </div>
            
            <div class="footer-bottom">
                <p>&copy; 2024 <?php echo APP_NAME; ?>. All rights reserved.</p>
            </div>
        </div>
    </footer>

    <script src="../assets/js/main.js"></script>
    <script>
        // Customer dashboard specific styles
        document.head.insertAdjacentHTML('beforeend', `
            <style>
                .customer-body {
                    background: var(--light-gray);
                }
                
                .customer-header {
                    background: var(--gradient-navy);
                    color: var(--white);
                    padding: 1rem 0;
                    box-shadow: var(--shadow-md);
                }
                
                .customer-header .navbar {
                    display: flex;
                    justify-content: space-between;
                    align-items: center;
                }
                
                .customer-header .logo {
                    color: var(--primary-green);
                    text-decoration: none;
                    font-size: 1.5rem;
                    font-weight: bold;
                    display: flex;
                    align-items: center;
                    gap: 10px;
                }
                
                .customer-header .nav-menu {
                    display: flex;
                    list-style: none;
                    gap: 2rem;
                    align-items: center;
                }
                
                .customer-header .nav-item a {
                    color: var(--white);
                    text-decoration: none;
                    padding: 0.5rem 1rem;
                    border-radius: 5px;
                    transition: all var(--transition-fast);
                }
                
                .customer-header .nav-item a:hover,
                .customer-header .nav-item a.active {
                    background-color: rgba(198, 216, 112, 0.2);
                    color: var(--primary-green);
                }
                
                .customer-main {
                    margin-top: 2rem;
                    min-height: calc(100vh - 200px);
                }
                
                .welcome-section {
                    text-align: center;
                    margin-bottom: 3rem;
                }
                
                .welcome-section h1 {
                    color: var(--navy-blue);
                    font-size: 2.5rem;
                    margin-bottom: 0.5rem;
                }
                
                .welcome-section p {
                    color: var(--medium-gray);
                    font-size: 1.1rem;
                }
                
                .stats-row {
                    display: grid;
                    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
                    gap: 2rem;
                    margin-bottom: 3rem;
                }
                
                .stat-card {
                    background: var(--white);
                    border-radius: 15px;
                    padding: 2rem;
                    box-shadow: var(--shadow-sm);
                    display: flex;
                    align-items: center;
                    gap: 1.5rem;
                    transition: all var(--transition-normal);
                    position: relative;
                    overflow: hidden;
                }
                
                .stat-card:before {
                    content: '';
                    position: absolute;
                    top: 0;
                    left: 0;
                    right: 0;
                    height: 4px;
                    background: var(--gradient-primary);
                }
                
                .stat-card:hover {
                    transform: translateY(-5px);
                    box-shadow: var(--shadow-lg);
                }
                
                .stat-icon {
                    width: 70px;
                    height: 70px;
                    border-radius: 50%;
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    font-size: 2rem;
                    background: var(--primary-green);
                    color: var(--navy-blue);
                }
                
                .stat-icon.warning {
                    background: var(--warning);
                    color: var(--white);
                }
                
                .stat-content {
                    flex: 1;
                }
                
                .stat-number {
                    font-size: 2.5rem;
                    font-weight: bold;
                    color: var(--navy-blue);
                    margin-bottom: 0.5rem;
                }
                
                .stat-label {
                    color: var(--medium-gray);
                    font-size: 1rem;
                    text-transform: uppercase;
                    letter-spacing: 0.5px;
                }
                
                .stat-action {
                    margin-left: auto;
                }
                
                .dashboard-grid {
                    display: grid;
                    grid-template-columns: repeat(auto-fit, minmax(400px, 1fr));
                    gap: 2rem;
                    margin-bottom: 3rem;
                }
                
                .dashboard-widget {
                    background: var(--white);
                    border-radius: 15px;
                    box-shadow: var(--shadow-sm);
                    overflow: hidden;
                    transition: all var(--transition-normal);
                }
                
                .dashboard-widget:hover {
                    transform: translateY(-3px);
                    box-shadow: var(--shadow-md);
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
                    font-size: 1.3rem;
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
                
                .purchases-list,
                .payments-list {
                    display: flex;
                    flex-direction: column;
                    gap: 1rem;
                }
                
                .purchase-item,
                .payment-item {
                    display: flex;
                    align-items: center;
                    gap: 1rem;
                    padding: 1rem;
                    background: var(--light-gray);
                    border-radius: 10px;
                    transition: all var(--transition-fast);
                }
                
                .purchase-item:hover,
                .payment-item:hover {
                    background: #e9ecef;
                    transform: translateX(5px);
                }
                
                .purchase-details,
                .payment-details {
                    flex: 1;
                }
                
                .purchase-items strong,
                .payment-info strong {
                    display: block;
                    color: var(--navy-blue);
                    margin-bottom: 0.25rem;
                }
                
                .purchase-date,
                .payment-due,
                .payment-info small {
                    color: var(--medium-gray);
                    font-size: 0.9rem;
                }
                
                .purchase-amount,
                .payment-amount {
                    font-weight: bold;
                    color: var(--navy-blue);
                    font-size: 1.1rem;
                }
                
                .quick-actions {
                    display: grid;
                    grid-template-columns: repeat(2, 1fr);
                    gap: 1rem;
                }
                
                .quick-action-btn {
                    display: flex;
                    flex-direction: column;
                    align-items: center;
                    gap: 0.75rem;
                    padding: 1.5rem;
                    background: var(--light-gray);
                    color: var(--navy-blue);
                    text-decoration: none;
                    border-radius: 10px;
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
                
                .account-info {
                    display: flex;
                    flex-direction: column;
                    gap: 1rem;
                    margin-bottom: 1.5rem;
                }
                
                .info-item {
                    display: flex;
                    justify-content: space-between;
                    align-items: center;
                    padding: 0.75rem 0;
                    border-bottom: 1px solid #e9ecef;
                }
                
                .info-item:last-child {
                    border-bottom: none;
                }
                
                .info-item label {
                    font-weight: 600;
                    color: var(--medium-gray);
                    font-size: 0.9rem;
                    text-transform: uppercase;
                }
                
                .info-item span {
                    color: var(--navy-blue);
                }
                
                .account-actions {
                    text-align: center;
                }
                
                .customer-footer {
                    background: var(--navy-blue);
                    color: var(--white);
                    padding: 2rem 0 1rem;
                    margin-top: 3rem;
                }
                
                .customer-footer .footer-content {
                    display: grid;
                    grid-template-columns: 2fr 1fr;
                    gap: 2rem;
                    margin-bottom: 2rem;
                }
                
                .customer-footer .footer-logo {
                    display: flex;
                    align-items: center;
                    gap: 10px;
                    font-size: 1.5rem;
                    font-weight: bold;
                    margin-bottom: 1rem;
                    color: var(--primary-green);
                }
                
                .customer-footer .footer-links h4 {
                    color: var(--primary-green);
                    margin-bottom: 1rem;
                }
                
                .customer-footer .footer-links ul {
                    list-style: none;
                    padding: 0;
                }
                
                .customer-footer .footer-links ul li {
                    margin-bottom: 0.5rem;
                }
                
                .customer-footer .footer-links ul li a {
                    color: var(--white);
                    text-decoration: none;
                    opacity: 0.8;
                    transition: opacity var(--transition-fast);
                }
                
                .customer-footer .footer-links ul li a:hover {
                    opacity: 1;
                    color: var(--primary-green);
                }
                
                .customer-footer .footer-bottom {
                    border-top: 1px solid rgba(255, 255, 255, 0.1);
                    padding-top: 1rem;
                    text-align: center;
                    opacity: 0.6;
                }
                
                /* Responsive Design */
                @media (max-width: 768px) {
                    .customer-header .nav-menu {
                        position: fixed;
                        left: -100%;
                        top: 70px;
                        flex-direction: column;
                        background: var(--navy-blue);
                        width: 100%;
                        text-align: center;
                        transition: left var(--transition-normal);
                        box-shadow: var(--shadow-md);
                        padding: 2rem 0;
                    }
                    
                    .customer-header .nav-menu.active {
                        left: 0;
                    }
                    
                    .customer-header .menu-toggle {
                        display: flex;
                        flex-direction: column;
                        cursor: pointer;
                        padding: 5px;
                    }
                    
                    .customer-header .menu-toggle span {
                        width: 25px;
                        height: 3px;
                        background: var(--white);
                        margin: 3px 0;
                        transition: var(--transition-fast);
                    }
                    
                    .welcome-section h1 {
                        font-size: 2rem;
                    }
                    
                    .stats-row {
                        grid-template-columns: 1fr;
                        gap: 1rem;
                    }
                    
                    .dashboard-grid {
                        grid-template-columns: 1fr;
                        gap: 1rem;
                    }
                    
                    .quick-actions {
                        grid-template-columns: 1fr;
                    }
                    
                    .customer-footer .footer-content {
                        grid-template-columns: 1fr;
                        text-align: center;
                    }
                }
            </style>
        `);
    </script>
</body>
</html>