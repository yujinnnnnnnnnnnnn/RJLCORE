<?php require_once __DIR__ . '/../config/app.php'; if (session_status() === PHP_SESSION_NONE) { session_start(); } ?>
<header class="site-header">
    <div class="container nav-container">
        <a class="brand" href="<?php echo url('index.php'); ?>">
            <span class="brand-icon">⚡</span>
            <span class="brand-text">AppliancePro</span>
        </a>
        <nav class="nav">
            <button class="nav-toggle" aria-label="Toggle navigation" aria-expanded="false">
                <span></span><span></span><span></span>
            </button>
            <ul class="nav-links">
                <li><a href="<?php echo url('index.php'); ?>">Home</a></li>
                <li><a href="<?php echo url('about.php'); ?>">About</a></li>
                <li class="divider"></li>
                <?php if (!empty($_SESSION['role_id'])): ?>
                    <?php if ((int)$_SESSION['role_id'] === 1 || (int)$_SESSION['role_id'] === 2): ?>
                        <li><a href="<?php echo url('admin/'); ?>" class="btn btn-outline">Admin / Staff</a></li>
                    <?php else: ?>
                        <li><a href="<?php echo url('customer/'); ?>" class="btn btn-outline">Customer</a></li>
                    <?php endif; ?>
                    <li><a href="<?php echo url('auth/logout.php'); ?>" class="btn btn-primary">Logout</a></li>
                <?php else: ?>
                    <li><a href="<?php echo url('auth/login.php'); ?>" class="btn btn-outline">Login</a></li>
                    <li><a href="<?php echo url('auth/register.php'); ?>" class="btn btn-primary">Sign Up</a></li>
                <?php endif; ?>
            </ul>
        </nav>
    </div>
</header>

