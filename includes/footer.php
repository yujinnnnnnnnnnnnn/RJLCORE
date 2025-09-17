<?php require_once __DIR__ . '/../config/app.php'; ?>
<footer class="site-footer">
    <div class="container footer-grid">
        <div>
            <div class="brand-row">
                <span class="brand-icon">⚡</span>
                <span class="brand-text">AppliancePro</span>
            </div>
            <p>Manage inventory, sales, and customers efficiently.</p>
        </div>
        <div>
            <h4>Portals</h4>
            <ul>
                <li><a href="<?php echo url('admin/'); ?>">Admin / Staff</a></li>
                <li><a href="<?php echo url('customer/'); ?>">Customer</a></li>
            </ul>
        </div>
        <div>
            <h4>Company</h4>
            <ul>
                <li><a href="<?php echo url('about.php'); ?>">About</a></li>
                <li><a href="mailto:support@example.com">support@example.com</a></li>
            </ul>
        </div>
    </div>
    <div class="footer-bottom">© <?php echo date('Y'); ?> AppliancePro</div>
</footer>
<script src="<?php echo asset('assets/js/main.js'); ?>"></script>
</body>
</html>

