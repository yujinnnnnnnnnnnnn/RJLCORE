<?php
require_once '../config/config.php';
require_once '../classes/Auth.php';

// Check authentication and role
require_login();
require_role('customer');

$database = new Database();
$db = $database->getConnection();

try {
    $customer_id = $_SESSION['user_id'];
    
    // Get customer statistics
    $stmt = $db->prepare("
        SELECT 
            COUNT(*) as total_purchases,
            COALESCE(SUM(total_amount), 0) as total_spent
        FROM sales 
        WHERE customer_id = :customer_id
    ");
    $stmt->bindParam(':customer_id', $customer_id);
    $stmt->execute();
    $customer_stats = $stmt->fetch();

    // Get pending installments
    $stmt = $db->prepare("
        SELECT COUNT(*) as pending_count, 
               COALESCE(SUM(amount - paid_amount), 0) as pending_amount
        FROM installments i
        JOIN sales s ON i.sale_id = s.id
        WHERE s.customer_id = :customer_id AND i.status = 'pending'
    ");
    $stmt->bindParam(':customer_id', $customer_id);
    $stmt->execute();
    $pending_stats = $stmt->fetch();

    // Get overdue installments
    $stmt = $db->prepare("
        SELECT COUNT(*) as overdue_count,
               COALESCE(SUM(amount - paid_amount), 0) as overdue_amount
        FROM installments i
        JOIN sales s ON i.sale_id = s.id
        WHERE s.customer_id = :customer_id AND i.status = 'pending' AND i.due_date < CURDATE()
    ");
    $stmt->bindParam(':customer_id', $customer_id);
    $stmt->execute();
    $overdue_stats = $stmt->fetch();

    // Get recent purchases
    $stmt = $db->prepare("
        SELECT s.*, CONCAT(staff.first_name, ' ', staff.last_name) as staff_name,
               GROUP_CONCAT(CONCAT(si.quantity, 'x ', p.name) SEPARATOR ', ') as items
        FROM sales s
        JOIN users staff ON s.staff_id = staff.id
        JOIN sale_items si ON s.id = si.sale_id
        JOIN products p ON si.product_id = p.id
        WHERE s.customer_id = :customer_id
        GROUP BY s.id
        ORDER BY s.sale_date DESC
        LIMIT 5
    ");
    $stmt->bindParam(':customer_id', $customer_id);
    $stmt->execute();
    $recent_purchases = $stmt->fetchAll();

    // Get upcoming installments
    $stmt = $db->prepare("
        SELECT i.*, s.id as sale_id,
               GROUP_CONCAT(CONCAT(si.quantity, 'x ', p.name) SEPARATOR ', ') as items
        FROM installments i
        JOIN sales s ON i.sale_id = s.id
        JOIN sale_items si ON s.id = si.sale_id
        JOIN products p ON si.product_id = p.id
        WHERE s.customer_id = :customer_id AND i.status = 'pending'
        GROUP BY i.id
        ORDER BY i.due_date ASC
        LIMIT 5
    ");
    $stmt->bindParam(':customer_id', $customer_id);
    $stmt->execute();
    $upcoming_installments = $stmt->fetchAll();

    // Get recent notifications
    $stmt = $db->prepare("
        SELECT * FROM notifications 
        WHERE user_id = :user_id 
        ORDER BY created_at DESC 
        LIMIT 5
    ");
    $stmt->bindParam(':user_id', $customer_id);
    $stmt->execute();
    $notifications = $stmt->fetchAll();

} catch (PDOException $e) {
    $error_message = "Database error: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Customer Dashboard - <?php echo APP_NAME; ?></title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
    <!-- Header -->
    <header class="header">
        <div class="container">
            <a href="../index.php" class="logo">
                <i class="fas fa-home"></i> ApplianceStore
            </a>
            <nav>
                <ul class="nav-menu">
                    <li><a href="dashboard.php" class="active">Dashboard</a></li>
                    <li><a href="purchases.php">My Purchases</a></li>
                    <li><a href="installments.php">Installments</a></li>
                    <li><a href="notifications.php">
                        Notifications
                        <?php if (count(array_filter($notifications, fn($n) => !$n['is_read'])) > 0): ?>
                            <span class="badge badge-danger"><?php echo count(array_filter($notifications, fn($n) => !$n['is_read'])); ?></span>
                        <?php endif; ?>
                    </a></li>
                    <li><a href="profile.php">Profile</a></li>
                    <li><a href="../logout.php" class="btn btn-secondary">Logout</a></li>
                </ul>
            </nav>
        </div>
    </header>

    <!-- Main Content -->
    <main style="padding: 2rem 0; min-height: calc(100vh - 80px);">
        <div class="container">
            <!-- Welcome Section -->
            <div class="content-header">
                <div style="display: flex; justify-content: space-between; align-items: center;">
                    <div>
                        <h1><i class="fas fa-tachometer-alt"></i> Welcome, <?php echo $_SESSION['first_name']; ?>!</h1>
                        <p>Manage your purchases, payments, and account information</p>
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
                        <i class="fas fa-shopping-bag"></i>
                    </div>
                    <div class="stat-number"><?php echo number_format($customer_stats['total_purchases']); ?></div>
                    <div class="stat-label">Total Purchases</div>
                    <div class="mt-2">
                        <a href="purchases.php" class="btn btn-sm">View Purchases</a>
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-dollar-sign"></i>
                    </div>
                    <div class="stat-number"><?php echo format_currency($customer_stats['total_spent']); ?></div>
                    <div class="stat-label">Total Spent</div>
                    <div class="mt-2">
                        <a href="purchases.php" class="btn btn-success btn-sm">View Details</a>
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-calendar-check"></i>
                    </div>
                    <div class="stat-number"><?php echo number_format($pending_stats['pending_count']); ?></div>
                    <div class="stat-label">Pending Payments</div>
                    <div class="mt-2">
                        <a href="installments.php" class="btn btn-warning btn-sm">View Payments</a>
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-exclamation-triangle" style="color: var(--danger);"></i>
                    </div>
                    <div class="stat-number"><?php echo number_format($overdue_stats['overdue_count']); ?></div>
                    <div class="stat-label">Overdue Payments</div>
                    <div class="mt-2">
                        <?php if ($overdue_stats['overdue_count'] > 0): ?>
                            <a href="installments.php?filter=overdue" class="btn btn-danger btn-sm">Pay Now</a>
                        <?php else: ?>
                            <span class="text-success">All up to date!</span>
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
                            <a href="purchases.php" class="btn btn-primary" style="padding: 1.5rem; text-align: center;">
                                <i class="fas fa-shopping-bag" style="display: block; font-size: 2rem; margin-bottom: 0.5rem;"></i>
                                View Purchases
                            </a>
                            <a href="installments.php" class="btn btn-warning" style="padding: 1.5rem; text-align: center;">
                                <i class="fas fa-calendar-alt" style="display: block; font-size: 2rem; margin-bottom: 0.5rem;"></i>
                                Payment Schedule
                            </a>
                            <a href="profile.php" class="btn btn-info" style="padding: 1.5rem; text-align: center;">
                                <i class="fas fa-user-edit" style="display: block; font-size: 2rem; margin-bottom: 0.5rem;"></i>
                                Update Profile
                            </a>
                            <a href="notifications.php" class="btn btn-secondary" style="padding: 1.5rem; text-align: center;">
                                <i class="fas fa-bell" style="display: block; font-size: 2rem; margin-bottom: 0.5rem;"></i>
                                Notifications
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Recent Activity -->
            <div class="row">
                <div class="col-8">
                    <div class="card">
                        <div class="card-header">
                            <div style="display: flex; justify-content: space-between; align-items: center;">
                                <h3><i class="fas fa-history"></i> Recent Purchases</h3>
                                <a href="purchases.php" class="btn btn-sm">View All</a>
                            </div>
                        </div>

                        <?php if (empty($recent_purchases)): ?>
                            <div class="text-center p-5">
                                <i class="fas fa-shopping-bag" style="font-size: 4rem; color: var(--gray); opacity: 0.5;"></i>
                                <h4 class="mt-3">No Purchases Yet</h4>
                                <p>Visit our store to make your first purchase!</p>
                            </div>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table">
                                    <thead>
                                        <tr>
                                            <th>Order #</th>
                                            <th>Items</th>
                                            <th>Amount</th>
                                            <th>Payment</th>
                                            <th>Date</th>
                                            <th>Status</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($recent_purchases as $purchase): ?>
                                        <tr>
                                            <td><strong>#<?php echo str_pad($purchase['id'], 6, '0', STR_PAD_LEFT); ?></strong></td>
                                            <td>
                                                <div style="max-width: 200px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;" title="<?php echo htmlspecialchars($purchase['items']); ?>">
                                                    <?php echo htmlspecialchars($purchase['items']); ?>
                                                </div>
                                            </td>
                                            <td><strong><?php echo format_currency($purchase['total_amount']); ?></strong></td>
                                            <td>
                                                <span class="badge <?php echo $purchase['payment_type'] === 'full' ? 'badge-success' : 'badge-warning'; ?>">
                                                    <?php echo ucfirst($purchase['payment_type']); ?>
                                                </span>
                                            </td>
                                            <td><?php echo format_date($purchase['sale_date']); ?></td>
                                            <td>
                                                <span class="badge badge-<?php 
                                                    echo $purchase['payment_status'] === 'paid' ? 'success' : 
                                                        ($purchase['payment_status'] === 'partial' ? 'warning' : 'danger'); 
                                                ?>">
                                                    <?php echo ucfirst($purchase['payment_status']); ?>
                                                </span>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="col-4">
                    <div class="card">
                        <div class="card-header">
                            <div style="display: flex; justify-content: space-between; align-items: center;">
                                <h3><i class="fas fa-bell"></i> Notifications</h3>
                                <a href="notifications.php" class="btn btn-sm">View All</a>
                            </div>
                        </div>

                        <?php if (empty($notifications)): ?>
                            <div class="text-center p-4">
                                <i class="fas fa-bell-slash" style="font-size: 2rem; color: var(--gray); opacity: 0.5;"></i>
                                <p class="mt-2">No notifications</p>
                            </div>
                        <?php else: ?>
                            <div style="max-height: 300px; overflow-y: auto;">
                                <?php foreach ($notifications as $notification): ?>
                                <div class="notification-item" style="padding: 1rem; border-bottom: 1px solid #eee; <?php echo !$notification['is_read'] ? 'background: rgba(198, 216, 112, 0.1);' : ''; ?>">
                                    <div style="display: flex; justify-content: between; align-items: start; gap: 1rem;">
                                        <div style="flex: 1;">
                                            <h5 style="margin-bottom: 0.5rem; font-size: 0.9rem;">
                                                <?php echo htmlspecialchars($notification['title']); ?>
                                                <?php if (!$notification['is_read']): ?>
                                                    <span class="badge badge-primary" style="font-size: 0.7rem; margin-left: 0.5rem;">NEW</span>
                                                <?php endif; ?>
                                            </h5>
                                            <p style="margin-bottom: 0.5rem; font-size: 0.8rem; color: var(--gray);">
                                                <?php echo htmlspecialchars($notification['message']); ?>
                                            </p>
                                            <small style="color: var(--gray);">
                                                <?php echo format_datetime($notification['created_at']); ?>
                                            </small>
                                        </div>
                                        <div>
                                            <i class="fas fa-<?php 
                                                echo $notification['type'] === 'payment_reminder' ? 'clock' :
                                                    ($notification['type'] === 'payment_received' ? 'check-circle' :
                                                    ($notification['type'] === 'overdue' ? 'exclamation-triangle' : 'info-circle'));
                                            ?>" style="color: var(--<?php 
                                                echo $notification['type'] === 'payment_reminder' ? 'warning' :
                                                    ($notification['type'] === 'payment_received' ? 'success' :
                                                    ($notification['type'] === 'overdue' ? 'danger' : 'info'));
                                            ?>);"></i>
                                        </div>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Upcoming Payments -->
            <?php if (!empty($upcoming_installments)): ?>
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <div style="display: flex; justify-content: space-between; align-items: center;">
                                <h3><i class="fas fa-calendar-alt"></i> Upcoming Payments</h3>
                                <a href="installments.php" class="btn btn-sm">View All</a>
                            </div>
                        </div>

                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Payment #</th>
                                        <th>Items</th>
                                        <th>Amount Due</th>
                                        <th>Due Date</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($upcoming_installments as $installment): ?>
                                    <tr class="<?php echo strtotime($installment['due_date']) < time() ? 'table-danger' : ''; ?>">
                                        <td><strong>#<?php echo str_pad($installment['id'], 6, '0', STR_PAD_LEFT); ?></strong></td>
                                        <td>
                                            <div style="max-width: 200px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;" title="<?php echo htmlspecialchars($installment['items']); ?>">
                                                <?php echo htmlspecialchars($installment['items']); ?>
                                            </div>
                                        </td>
                                        <td><strong><?php echo format_currency($installment['amount'] - $installment['paid_amount']); ?></strong></td>
                                        <td>
                                            <?php echo format_date($installment['due_date']); ?>
                                            <?php if (strtotime($installment['due_date']) < time()): ?>
                                                <br><small class="text-danger">OVERDUE</small>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <span class="badge badge-<?php echo strtotime($installment['due_date']) < time() ? 'danger' : 'warning'; ?>">
                                                <?php echo strtotime($installment['due_date']) < time() ? 'Overdue' : 'Pending'; ?>
                                            </span>
                                        </td>
                                        <td>
                                            <button class="btn btn-success btn-sm" onclick="payInstallment(<?php echo $installment['id']; ?>)">
                                                <i class="fas fa-credit-card"></i> Pay Now
                                            </button>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </main>

    <script src="../assets/js/main.js"></script>
    <script>
        // Payment function (placeholder)
        function payInstallment(installmentId) {
            showNotification('Payment processing feature coming soon!', 'info');
            // In a real implementation, this would open a payment modal or redirect to payment processor
        }

        // Mark notifications as read when viewed
        function markNotificationRead(notificationId) {
            fetch('../api/notifications.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    action: 'mark_read',
                    notification_id: notificationId
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Update UI to show as read
                    location.reload();
                }
            })
            .catch(error => console.error('Error:', error));
        }

        // Auto-refresh dashboard every 10 minutes
        setInterval(() => {
            location.reload();
        }, 600000);
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
        .badge-primary { background-color: var(--navy); color: white; }

        .table-danger {
            background-color: rgba(220, 53, 69, 0.1);
        }

        .notification-item:hover {
            background-color: rgba(198, 216, 112, 0.1) !important;
        }

        .text-success { color: var(--success) !important; }
        .text-danger { color: var(--danger) !important; }
        .text-gray { color: var(--gray) !important; }

        .nav-menu .badge {
            position: absolute;
            top: -8px;
            right: -8px;
            min-width: 18px;
            height: 18px;
            padding: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.7rem;
        }

        .nav-menu li {
            position: relative;
        }
    </style>
</body>
</html>