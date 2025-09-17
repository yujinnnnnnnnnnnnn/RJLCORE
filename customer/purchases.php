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
    
    // Get customer purchases with details
    $stmt = $db->prepare("
        SELECT s.*, 
               CONCAT(staff.first_name, ' ', staff.last_name) as staff_name,
               GROUP_CONCAT(
                   CONCAT(si.quantity, 'x ', p.name, ' (', p.brand, ' ', COALESCE(p.model, ''), ')') 
                   SEPARATOR '||'
               ) as items,
               GROUP_CONCAT(
                   CONCAT(p.name, '|', si.quantity, '|', si.unit_price, '|', p.image_path, '|', p.warranty_months)
                   SEPARATOR '||'
               ) as item_details
        FROM sales s
        JOIN users staff ON s.staff_id = staff.id
        JOIN sale_items si ON s.id = si.sale_id
        JOIN products p ON si.product_id = p.id
        WHERE s.customer_id = :customer_id
        GROUP BY s.id
        ORDER BY s.sale_date DESC
    ");
    $stmt->bindParam(':customer_id', $customer_id);
    $stmt->execute();
    $purchases = $stmt->fetchAll();

    // Get total statistics
    $stmt = $db->prepare("
        SELECT 
            COUNT(*) as total_orders,
            COALESCE(SUM(total_amount), 0) as total_spent,
            COALESCE(SUM(CASE WHEN payment_status = 'paid' THEN total_amount ELSE 0 END), 0) as total_paid
        FROM sales 
        WHERE customer_id = :customer_id
    ");
    $stmt->bindParam(':customer_id', $customer_id);
    $stmt->execute();
    $stats = $stmt->fetch();

} catch (PDOException $e) {
    $error_message = "Database error: " . $e->getMessage();
    $purchases = [];
    $stats = ['total_orders' => 0, 'total_spent' => 0, 'total_paid' => 0];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Purchases - <?php echo APP_NAME; ?></title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
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
                    <li><a href="dashboard.php">Dashboard</a></li>
                    <li><a href="purchases.php" class="active">My Purchases</a></li>
                    <li><a href="installments.php">Installments</a></li>
                    <li><a href="notifications.php">Notifications</a></li>
                    <li><a href="profile.php">Profile</a></li>
                    <li><a href="../logout.php" class="btn btn-secondary">Logout</a></li>
                </ul>
            </nav>
        </div>
    </header>

    <!-- Main Content -->
    <main style="padding: 2rem 0; min-height: calc(100vh - 80px);">
        <div class="container">
            <!-- Page Header -->
            <div class="content-header">
                <div>
                    <h1><i class="fas fa-shopping-bag"></i> My Purchases</h1>
                    <p>View your purchase history and order details</p>
                </div>
            </div>

            <!-- Purchase Statistics -->
            <div class="stats-grid" style="grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); margin-bottom: 2rem;">
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-shopping-cart"></i>
                    </div>
                    <div class="stat-number"><?php echo number_format($stats['total_orders']); ?></div>
                    <div class="stat-label">Total Orders</div>
                </div>

                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-dollar-sign"></i>
                    </div>
                    <div class="stat-number"><?php echo format_currency($stats['total_spent']); ?></div>
                    <div class="stat-label">Total Spent</div>
                </div>

                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-check-circle"></i>
                    </div>
                    <div class="stat-number"><?php echo format_currency($stats['total_paid']); ?></div>
                    <div class="stat-label">Amount Paid</div>
                </div>

                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-clock"></i>
                    </div>
                    <div class="stat-number"><?php echo format_currency($stats['total_spent'] - $stats['total_paid']); ?></div>
                    <div class="stat-label">Remaining Balance</div>
                </div>
            </div>

            <!-- Purchases List -->
            <?php if (empty($purchases)): ?>
                <div class="card">
                    <div class="text-center p-5">
                        <i class="fas fa-shopping-bag" style="font-size: 4rem; color: var(--gray); opacity: 0.5;"></i>
                        <h3 class="mt-3">No Purchases Yet</h3>
                        <p>You haven't made any purchases yet. Visit our store to start shopping!</p>
                        <a href="../index.php" class="btn btn-primary">
                            <i class="fas fa-store"></i> Browse Products
                        </a>
                    </div>
                </div>
            <?php else: ?>
                <?php foreach ($purchases as $purchase): ?>
                    <?php
                    $item_details = explode('||', $purchase['item_details']);
                    $items_display = explode('||', $purchase['items']);
                    ?>
                    <div class="card mb-4">
                        <div class="card-header">
                            <div style="display: flex; justify-content: space-between; align-items: center;">
                                <div>
                                    <h3>Order #<?php echo str_pad($purchase['id'], 6, '0', STR_PAD_LEFT); ?></h3>
                                    <p style="margin: 0; color: var(--gray);">
                                        Purchased on <?php echo format_date($purchase['sale_date']); ?> 
                                        • Served by <?php echo htmlspecialchars($purchase['staff_name']); ?>
                                    </p>
                                </div>
                                <div style="text-align: right;">
                                    <div style="font-size: 1.5rem; font-weight: bold; color: var(--navy);">
                                        <?php echo format_currency($purchase['total_amount']); ?>
                                    </div>
                                    <div>
                                        <span class="badge <?php echo $purchase['payment_type'] === 'full' ? 'badge-success' : 'badge-warning'; ?>">
                                            <?php echo ucfirst($purchase['payment_type']); ?>
                                        </span>
                                        <span class="badge badge-<?php 
                                            echo $purchase['payment_status'] === 'paid' ? 'success' : 
                                                ($purchase['payment_status'] === 'partial' ? 'warning' : 'danger'); 
                                        ?>">
                                            <?php echo ucfirst($purchase['payment_status']); ?>
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Order Items -->
                        <div style="padding: 1.5rem;">
                            <h4 style="margin-bottom: 1rem; color: var(--navy);">
                                <i class="fas fa-box"></i> Items Purchased
                            </h4>
                            
                            <div class="row">
                                <?php foreach ($item_details as $item_detail): ?>
                                    <?php
                                    $parts = explode('|', $item_detail);
                                    if (count($parts) >= 5) {
                                        $name = $parts[0];
                                        $quantity = $parts[1];
                                        $price = $parts[2];
                                        $image_path = $parts[3];
                                        $warranty = $parts[4];
                                    }
                                    ?>
                                    <div class="col-6 mb-3">
                                        <div style="display: flex; gap: 1rem; padding: 1rem; border: 1px solid #eee; border-radius: 8px;">
                                            <div style="width: 80px; height: 80px; background: linear-gradient(135deg, var(--beige), var(--green)); border-radius: 8px; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                                                <?php if ($image_path && file_exists('../' . $image_path)): ?>
                                                    <img src="../<?php echo htmlspecialchars($image_path); ?>" alt="Product" style="width: 100%; height: 100%; object-fit: cover; border-radius: 8px;">
                                                <?php else: ?>
                                                    <i class="fas fa-image" style="color: var(--navy); opacity: 0.6; font-size: 1.5rem;"></i>
                                                <?php endif; ?>
                                            </div>
                                            <div style="flex: 1;">
                                                <h5 style="margin-bottom: 0.5rem;"><?php echo htmlspecialchars($name); ?></h5>
                                                <div style="color: var(--gray); font-size: 0.9rem; margin-bottom: 0.5rem;">
                                                    <i class="fas fa-cubes"></i> Quantity: <strong><?php echo $quantity; ?></strong>
                                                </div>
                                                <div style="color: var(--gray); font-size: 0.9rem; margin-bottom: 0.5rem;">
                                                    <i class="fas fa-tag"></i> Unit Price: <strong><?php echo format_currency($price); ?></strong>
                                                </div>
                                                <div style="color: var(--gray); font-size: 0.9rem;">
                                                    <i class="fas fa-shield-alt"></i> Warranty: <strong><?php echo $warranty; ?> months</strong>
                                                </div>
                                                <div style="margin-top: 0.5rem; font-weight: bold; color: var(--navy);">
                                                    Total: <?php echo format_currency($price * $quantity); ?>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>

                        <!-- Order Summary -->
                        <div style="background: var(--light-gray); padding: 1.5rem; border-top: 1px solid #eee;">
                            <div class="row">
                                <div class="col-6">
                                    <h5>Payment Information</h5>
                                    <div style="margin-bottom: 0.5rem;">
                                        <strong>Payment Type:</strong> 
                                        <span class="badge <?php echo $purchase['payment_type'] === 'full' ? 'badge-success' : 'badge-warning'; ?>">
                                            <?php echo ucfirst($purchase['payment_type']); ?>
                                        </span>
                                    </div>
                                    <div style="margin-bottom: 0.5rem;">
                                        <strong>Payment Status:</strong> 
                                        <span class="badge badge-<?php 
                                            echo $purchase['payment_status'] === 'paid' ? 'success' : 
                                                ($purchase['payment_status'] === 'partial' ? 'warning' : 'danger'); 
                                        ?>">
                                            <?php echo ucfirst($purchase['payment_status']); ?>
                                        </span>
                                    </div>
                                    <?php if ($purchase['discount'] > 0): ?>
                                    <div style="margin-bottom: 0.5rem;">
                                        <strong>Discount:</strong> <?php echo format_currency($purchase['discount']); ?>
                                    </div>
                                    <?php endif; ?>
                                    <?php if ($purchase['tax_amount'] > 0): ?>
                                    <div style="margin-bottom: 0.5rem;">
                                        <strong>Tax:</strong> <?php echo format_currency($purchase['tax_amount']); ?>
                                    </div>
                                    <?php endif; ?>
                                </div>
                                <div class="col-6">
                                    <div style="text-align: right;">
                                        <div style="font-size: 1.2rem; margin-bottom: 0.5rem;">
                                            <strong>Total Amount: <?php echo format_currency($purchase['total_amount']); ?></strong>
                                        </div>
                                        
                                        <?php if ($purchase['payment_type'] === 'installment'): ?>
                                            <a href="installments.php?sale_id=<?php echo $purchase['id']; ?>" class="btn btn-warning btn-sm">
                                                <i class="fas fa-calendar-alt"></i> View Payment Schedule
                                            </a>
                                        <?php endif; ?>
                                        
                                        <button class="btn btn-info btn-sm" onclick="printReceipt(<?php echo $purchase['id']; ?>)">
                                            <i class="fas fa-print"></i> Print Receipt
                                        </button>
                                        
                                        <?php if ($purchase['notes']): ?>
                                        <div style="margin-top: 1rem; padding: 1rem; background: white; border-radius: 8px; text-align: left;">
                                            <strong>Notes:</strong><br>
                                            <?php echo nl2br(htmlspecialchars($purchase['notes'])); ?>
                                        </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>

            <!-- Back to Dashboard -->
            <div class="text-center mt-4">
                <a href="dashboard.php" class="btn btn-primary">
                    <i class="fas fa-arrow-left"></i> Back to Dashboard
                </a>
            </div>
        </div>
    </main>

    <script src="../assets/js/main.js"></script>
    <script>
        function printReceipt(saleId) {
            // In a real implementation, this would generate and print a receipt
            showNotification('Receipt printing feature coming soon!', 'info');
        }

        // Add animation to cards
        document.addEventListener('DOMContentLoaded', function() {
            const cards = document.querySelectorAll('.card');
            cards.forEach((card, index) => {
                card.style.opacity = '0';
                card.style.transform = 'translateY(20px)';
                card.style.transition = 'all 0.5s ease';
                
                setTimeout(() => {
                    card.style.opacity = '1';
                    card.style.transform = 'translateY(0)';
                }, index * 100);
            });
        });
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

        .text-gray { color: var(--gray) !important; }
        
        @media (max-width: 768px) {
            .col-6 {
                flex: 0 0 100%;
            }
            
            .stats-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</body>
</html>