<?php
/**
 * Admin/Staff Header Include
 */

// Ensure user is authenticated and authorized
if (!isLoggedIn() || !hasAnyRole(['admin', 'staff'])) {
    redirectTo('../login.php');
}

$current_user = getCurrentUser();
$current_page = basename($_SERVER['PHP_SELF'], '.php');

// Get unread notifications count
$unread_notifications = fetchOne("SELECT COUNT(*) as count FROM notifications WHERE is_read = 0")['count'] ?? 0;

// Get pending payments count
$pending_payments_count = fetchOne("SELECT COUNT(*) as count FROM installment_payments WHERE status = 'pending' AND due_date <= DATE_ADD(NOW(), INTERVAL 7 DAY)")['count'] ?? 0;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($page_title) ? $page_title . ' - ' : ''; ?>Admin Panel - <?php echo APP_NAME; ?></title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.css">
</head>
<body class="admin-body">
    <!-- Admin Header -->
    <header class="admin-header">
        <div class="admin-nav">
            <div class="nav-left">
                <button class="sidebar-toggle" data-sidebar-toggle>
                    <i class="fas fa-bars"></i>
                </button>
                <div class="admin-logo">
                    <a href="dashboard.php">
                        <i class="fas fa-cogs"></i>
                        <span>Admin Panel</span>
                    </a>
                </div>
            </div>
            
            <div class="nav-center">
                <div class="search-bar">
                    <input type="text" placeholder="Search products, customers..." class="search-input">
                    <button class="search-btn">
                        <i class="fas fa-search"></i>
                    </button>
                </div>
            </div>
            
            <div class="nav-right">
                <!-- Notifications -->
                <div class="nav-item dropdown">
                    <button class="nav-btn" data-dropdown-toggle>
                        <i class="fas fa-bell"></i>
                        <?php if ($unread_notifications > 0): ?>
                            <span class="badge"><?php echo $unread_notifications; ?></span>
                        <?php endif; ?>
                    </button>
                    <div class="dropdown-menu notifications-dropdown">
                        <div class="dropdown-header">
                            <h6>Notifications</h6>
                            <?php if ($unread_notifications > 0): ?>
                                <a href="notifications.php?action=mark_all_read" class="mark-all-read">Mark all read</a>
                            <?php endif; ?>
                        </div>
                        <div class="dropdown-body">
                            <!-- Notifications will be loaded via AJAX -->
                            <div class="notification-placeholder">
                                <i class="fas fa-spinner fa-spin"></i>
                                <span>Loading notifications...</span>
                            </div>
                        </div>
                        <div class="dropdown-footer">
                            <a href="notifications.php">View all notifications</a>
                        </div>
                    </div>
                </div>
                
                <!-- Quick Actions -->
                <div class="nav-item dropdown">
                    <button class="nav-btn" data-dropdown-toggle>
                        <i class="fas fa-plus"></i>
                    </button>
                    <div class="dropdown-menu quick-actions-dropdown">
                        <div class="dropdown-header">
                            <h6>Quick Actions</h6>
                        </div>
                        <div class="dropdown-body">
                            <a href="inventory.php?action=add" class="dropdown-item">
                                <i class="fas fa-box"></i>
                                <span>Add Product</span>
                            </a>
                            <a href="sales.php?action=new" class="dropdown-item">
                                <i class="fas fa-shopping-cart"></i>
                                <span>New Sale</span>
                            </a>
                            <a href="customers.php?action=add" class="dropdown-item">
                                <i class="fas fa-user-plus"></i>
                                <span>Add Customer</span>
                            </a>
                            <?php if ($current_user['role'] === 'admin'): ?>
                                <a href="users.php?action=add" class="dropdown-item">
                                    <i class="fas fa-user-cog"></i>
                                    <span>Add Staff User</span>
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                
                <!-- User Menu -->
                <div class="nav-item dropdown">
                    <button class="nav-btn user-btn" data-dropdown-toggle>
                        <div class="user-avatar">
                            <i class="fas fa-user"></i>
                        </div>
                        <span class="user-name"><?php echo htmlspecialchars($current_user['first_name']); ?></span>
                        <i class="fas fa-chevron-down"></i>
                    </button>
                    <div class="dropdown-menu user-dropdown">
                        <div class="dropdown-header">
                            <div class="user-info">
                                <strong><?php echo htmlspecialchars($current_user['first_name'] . ' ' . $current_user['last_name']); ?></strong>
                                <small><?php echo ucfirst($current_user['role']); ?></small>
                            </div>
                        </div>
                        <div class="dropdown-body">
                            <a href="profile.php" class="dropdown-item">
                                <i class="fas fa-user-circle"></i>
                                <span>My Profile</span>
                            </a>
                            <a href="../index.php" class="dropdown-item">
                                <i class="fas fa-home"></i>
                                <span>View Website</span>
                            </a>
                            <?php if ($current_user['role'] === 'admin'): ?>
                                <a href="settings.php" class="dropdown-item">
                                    <i class="fas fa-cogs"></i>
                                    <span>Settings</span>
                                </a>
                            <?php endif; ?>
                            <div class="dropdown-divider"></div>
                            <a href="../logout.php" class="dropdown-item text-danger">
                                <i class="fas fa-sign-out-alt"></i>
                                <span>Logout</span>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </header>

    <!-- Sidebar -->
    <aside class="admin-sidebar">
        <nav class="sidebar-nav">
            <ul class="nav-menu">
                <li class="nav-item">
                    <a href="dashboard.php" class="nav-link <?php echo $current_page === 'dashboard' ? 'active' : ''; ?>">
                        <i class="fas fa-tachometer-alt"></i>
                        <span>Dashboard</span>
                    </a>
                </li>
                
                <li class="nav-section">
                    <span class="section-title">Inventory</span>
                </li>
                <li class="nav-item">
                    <a href="inventory.php" class="nav-link <?php echo $current_page === 'inventory' ? 'active' : ''; ?>">
                        <i class="fas fa-boxes"></i>
                        <span>Products</span>
                        <?php if ($pending_payments_count > 0): ?>
                            <span class="nav-badge"><?php echo $pending_payments_count; ?></span>
                        <?php endif; ?>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="categories.php" class="nav-link <?php echo $current_page === 'categories' ? 'active' : ''; ?>">
                        <i class="fas fa-tags"></i>
                        <span>Categories</span>
                    </a>
                </li>
                
                <li class="nav-section">
                    <span class="section-title">Sales</span>
                </li>
                <li class="nav-item">
                    <a href="sales.php" class="nav-link <?php echo $current_page === 'sales' ? 'active' : ''; ?>">
                        <i class="fas fa-shopping-cart"></i>
                        <span>Sales</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="payments.php" class="nav-link <?php echo $current_page === 'payments' ? 'active' : ''; ?>">
                        <i class="fas fa-credit-card"></i>
                        <span>Payments</span>
                        <?php if ($pending_payments_count > 0): ?>
                            <span class="nav-badge warning"><?php echo $pending_payments_count; ?></span>
                        <?php endif; ?>
                    </a>
                </li>
                
                <li class="nav-section">
                    <span class="section-title">Customers</span>
                </li>
                <li class="nav-item">
                    <a href="customers.php" class="nav-link <?php echo $current_page === 'customers' ? 'active' : ''; ?>">
                        <i class="fas fa-users"></i>
                        <span>Customers</span>
                    </a>
                </li>
                
                <li class="nav-section">
                    <span class="section-title">Reports</span>
                </li>
                <li class="nav-item">
                    <a href="reports.php" class="nav-link <?php echo $current_page === 'reports' ? 'active' : ''; ?>">
                        <i class="fas fa-chart-bar"></i>
                        <span>Analytics</span>
                    </a>
                </li>
                
                <?php if ($current_user['role'] === 'admin'): ?>
                    <li class="nav-section">
                        <span class="section-title">Administration</span>
                    </li>
                    <li class="nav-item">
                        <a href="users.php" class="nav-link <?php echo $current_page === 'users' ? 'active' : ''; ?>">
                            <i class="fas fa-user-cog"></i>
                            <span>Users</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="settings.php" class="nav-link <?php echo $current_page === 'settings' ? 'active' : ''; ?>">
                            <i class="fas fa-cogs"></i>
                            <span>Settings</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="audit.php" class="nav-link <?php echo $current_page === 'audit' ? 'active' : ''; ?>">
                            <i class="fas fa-history"></i>
                            <span>Audit Log</span>
                        </a>
                    </li>
                <?php endif; ?>
            </ul>
        </nav>
    </aside>

    <!-- Main Content -->
    <main class="admin-main">
        <!-- Page Header -->
        <?php if (isset($page_title)): ?>
            <div class="page-header">
                <div class="page-header-content">
                    <h1 class="page-title"><?php echo htmlspecialchars($page_title); ?></h1>
                    <?php if (isset($page_subtitle)): ?>
                        <p class="page-subtitle"><?php echo htmlspecialchars($page_subtitle); ?></p>
                    <?php endif; ?>
                </div>
                <?php if (isset($page_actions)): ?>
                    <div class="page-actions">
                        <?php echo $page_actions; ?>
                    </div>
                <?php endif; ?>
            </div>
        <?php endif; ?>

        <!-- Flash Messages -->
        <?php $flash_messages = getFlashMessages(); ?>
        <?php if (!empty($flash_messages)): ?>
            <div class="flash-messages">
                <?php foreach ($flash_messages as $message): ?>
                    <div class="alert alert-<?php echo $message['type']; ?> alert-dismissible">
                        <button type="button" class="alert-close">&times;</button>
                        <?php echo htmlspecialchars($message['message']); ?>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <!-- Page Content -->
        <div class="page-content">

<style>
/* Admin Layout Styles */
.admin-body {
    background: var(--light-gray);
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
}

.admin-header {
    background: var(--white);
    border-bottom: 1px solid #e9ecef;
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    z-index: 1000;
    height: 70px;
}

.admin-nav {
    display: flex;
    align-items: center;
    height: 100%;
    padding: 0 1rem;
}

.nav-left {
    display: flex;
    align-items: center;
    gap: 1rem;
}

.sidebar-toggle {
    background: none;
    border: none;
    font-size: 1.2rem;
    color: var(--medium-gray);
    cursor: pointer;
    padding: 0.5rem;
    border-radius: 4px;
    transition: all var(--transition-fast);
}

.sidebar-toggle:hover {
    background: var(--light-gray);
    color: var(--navy-blue);
}

.admin-logo a {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    text-decoration: none;
    color: var(--navy-blue);
    font-weight: bold;
    font-size: 1.1rem;
}

.nav-center {
    flex: 1;
    display: flex;
    justify-content: center;
    max-width: 500px;
    margin: 0 2rem;
}

.search-bar {
    display: flex;
    width: 100%;
    max-width: 400px;
    position: relative;
}

.search-input {
    flex: 1;
    padding: 0.5rem 1rem;
    border: 2px solid #e9ecef;
    border-radius: 25px;
    outline: none;
    transition: border-color var(--transition-fast);
}

.search-input:focus {
    border-color: var(--primary-green);
}

.search-btn {
    position: absolute;
    right: 5px;
    top: 50%;
    transform: translateY(-50%);
    background: var(--primary-green);
    border: none;
    border-radius: 50%;
    width: 35px;
    height: 35px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: var(--navy-blue);
    cursor: pointer;
    transition: all var(--transition-fast);
}

.search-btn:hover {
    background: var(--navy-blue);
    color: var(--white);
}

.nav-right {
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.nav-item {
    position: relative;
}

.nav-btn {
    background: none;
    border: none;
    padding: 0.75rem;
    border-radius: 8px;
    color: var(--medium-gray);
    cursor: pointer;
    transition: all var(--transition-fast);
    position: relative;
}

.nav-btn:hover {
    background: var(--light-gray);
    color: var(--navy-blue);
}

.nav-btn .badge {
    position: absolute;
    top: 5px;
    right: 5px;
    background: var(--danger);
    color: var(--white);
    border-radius: 50%;
    width: 20px;
    height: 20px;
    font-size: 0.7rem;
    display: flex;
    align-items: center;
    justify-content: center;
}

.user-btn {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.5rem 1rem;
}

.user-avatar {
    width: 35px;
    height: 35px;
    border-radius: 50%;
    background: var(--primary-green);
    color: var(--navy-blue);
    display: flex;
    align-items: center;
    justify-content: center;
}

.user-name {
    font-weight: 500;
    color: var(--navy-blue);
}

/* Dropdown Styles */
.dropdown-menu {
    position: absolute;
    top: 100%;
    right: 0;
    background: var(--white);
    border-radius: 8px;
    box-shadow: var(--shadow-lg);
    min-width: 250px;
    opacity: 0;
    visibility: hidden;
    transform: translateY(-10px);
    transition: all var(--transition-fast);
    z-index: 1001;
}

.dropdown-menu.show {
    opacity: 1;
    visibility: visible;
    transform: translateY(0);
}

.dropdown-header {
    padding: 1rem;
    border-bottom: 1px solid #e9ecef;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.dropdown-header h6 {
    margin: 0;
    color: var(--navy-blue);
    font-weight: 600;
}

.dropdown-body {
    padding: 0.5rem 0;
}

.dropdown-item {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    padding: 0.75rem 1rem;
    color: var(--dark-gray);
    text-decoration: none;
    transition: all var(--transition-fast);
}

.dropdown-item:hover {
    background: var(--light-gray);
    color: var(--navy-blue);
}

.dropdown-item.text-danger {
    color: var(--danger);
}

.dropdown-divider {
    height: 1px;
    background: #e9ecef;
    margin: 0.5rem 0;
}

.dropdown-footer {
    padding: 0.5rem 1rem;
    border-top: 1px solid #e9ecef;
    text-align: center;
}

.dropdown-footer a {
    color: var(--navy-blue);
    text-decoration: none;
    font-weight: 500;
    font-size: 0.9rem;
}

/* Sidebar Styles */
.admin-sidebar {
    position: fixed;
    left: 0;
    top: 70px;
    width: 260px;
    height: calc(100vh - 70px);
    background: var(--white);
    border-right: 1px solid #e9ecef;
    overflow-y: auto;
    z-index: 999;
    transition: transform var(--transition-normal);
}

.admin-sidebar.collapsed {
    transform: translateX(-100%);
}

.sidebar-nav {
    padding: 1rem 0;
}

.nav-menu {
    list-style: none;
    padding: 0;
    margin: 0;
}

.nav-section {
    padding: 1rem 1.5rem 0.5rem;
}

.section-title {
    font-size: 0.8rem;
    font-weight: 600;
    text-transform: uppercase;
    color: var(--medium-gray);
    letter-spacing: 0.5px;
}

.nav-item {
    margin: 0.25rem 0;
}

.nav-link {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    padding: 0.75rem 1.5rem;
    color: var(--dark-gray);
    text-decoration: none;
    transition: all var(--transition-fast);
    position: relative;
}

.nav-link:hover {
    background: var(--light-gray);
    color: var(--navy-blue);
}

.nav-link.active {
    background: var(--primary-green);
    color: var(--navy-blue);
    font-weight: 600;
}

.nav-link.active::before {
    content: '';
    position: absolute;
    left: 0;
    top: 0;
    bottom: 0;
    width: 4px;
    background: var(--navy-blue);
}

.nav-badge {
    margin-left: auto;
    background: var(--primary-green);
    color: var(--navy-blue);
    border-radius: 12px;
    padding: 0.2rem 0.5rem;
    font-size: 0.7rem;
    font-weight: 600;
}

.nav-badge.warning {
    background: var(--warning);
    color: var(--white);
}

/* Main Content */
.admin-main {
    margin-left: 260px;
    margin-top: 70px;
    min-height: calc(100vh - 70px);
    transition: margin-left var(--transition-normal);
}

.admin-main.expanded {
    margin-left: 0;
}

.page-header {
    background: var(--white);
    padding: 2rem;
    border-bottom: 1px solid #e9ecef;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.page-title {
    font-size: 2rem;
    color: var(--navy-blue);
    margin: 0;
}

.page-subtitle {
    color: var(--medium-gray);
    margin: 0.5rem 0 0;
}

.page-actions {
    display: flex;
    gap: 1rem;
}

.page-content {
    flex: 1;
}

.flash-messages {
    padding: 1rem 2rem 0;
}

/* User Info in Dropdown */
.user-info {
    text-align: center;
}

.user-info strong {
    display: block;
    color: var(--navy-blue);
    margin-bottom: 0.25rem;
}

.user-info small {
    color: var(--medium-gray);
    text-transform: capitalize;
}

/* Responsive Design */
@media (max-width: 1024px) {
    .nav-center {
        display: none;
    }
    
    .admin-sidebar {
        transform: translateX(-100%);
    }
    
    .admin-sidebar.show {
        transform: translateX(0);
    }
    
    .admin-main {
        margin-left: 0;
    }
}

@media (max-width: 768px) {
    .nav-right {
        gap: 0.25rem;
    }
    
    .user-name {
        display: none;
    }
    
    .page-header {
        flex-direction: column;
        gap: 1rem;
        align-items: flex-start;
    }
    
    .page-actions {
        width: 100%;
        justify-content: flex-end;
    }
}
</style>

<script>
// Admin Panel JavaScript
document.addEventListener('DOMContentLoaded', function() {
    // Sidebar toggle functionality
    const sidebarToggle = document.querySelector('[data-sidebar-toggle]');
    const sidebar = document.querySelector('.admin-sidebar');
    const main = document.querySelector('.admin-main');
    
    if (sidebarToggle && sidebar) {
        sidebarToggle.addEventListener('click', function() {
            sidebar.classList.toggle('show');
        });
        
        // Close sidebar when clicking outside on mobile
        document.addEventListener('click', function(e) {
            if (window.innerWidth <= 1024 && 
                sidebar.classList.contains('show') && 
                !sidebar.contains(e.target) && 
                !sidebarToggle.contains(e.target)) {
                sidebar.classList.remove('show');
            }
        });
    }
    
    // Dropdown functionality
    document.addEventListener('click', function(e) {
        const dropdownToggle = e.target.closest('[data-dropdown-toggle]');
        
        if (dropdownToggle) {
            e.stopPropagation();
            const dropdown = dropdownToggle.nextElementSibling;
            
            // Close other dropdowns
            document.querySelectorAll('.dropdown-menu.show').forEach(menu => {
                if (menu !== dropdown) {
                    menu.classList.remove('show');
                }
            });
            
            // Toggle current dropdown
            if (dropdown) {
                dropdown.classList.toggle('show');
            }
        } else {
            // Close all dropdowns when clicking outside
            document.querySelectorAll('.dropdown-menu.show').forEach(menu => {
                menu.classList.remove('show');
            });
        }
    });
    
    // Load notifications
    loadNotifications();
    
    // Auto-refresh notifications every 5 minutes
    setInterval(loadNotifications, 300000);
});

function loadNotifications() {
    // This would normally load notifications via AJAX
    // For now, we'll just update the placeholder
    const placeholder = document.querySelector('.notification-placeholder');
    if (placeholder) {
        placeholder.innerHTML = '<div class="empty-state"><i class="fas fa-bell"></i><p>No new notifications</p></div>';
    }
}
</script>