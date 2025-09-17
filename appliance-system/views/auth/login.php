<div class="grid-2">
    <div class="card">
        <h2>Sign in</h2>
        <p class="muted">Admin, Staff, and Customer accounts</p>
        <?php if (!empty($error)): ?>
            <div class="card" style="background:#fff0f0;border-color:#fecaca;color:#991b1b"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        <form method="post" action="<?= base_url('login') ?>" class="form">
            <?= csrf_field() ?>
            <label>Email<br>
                <input type="email" name="email" required style="width:100%;padding:10px;border:1px solid #e2e8f0;border-radius:10px">
            </label>
            <br>
            <label>Password<br>
                <input type="password" name="password" required style="width:100%;padding:10px;border:1px solid #e2e8f0;border-radius:10px">
            </label>
            <br>
            <button class="btn btn-primary" type="submit">Login</button>
            <a class="btn" href="<?= base_url('register') ?>">Create Customer Account</a>
        </form>
        <p class="muted" style="margin-top:12px"><a href="<?= base_url('forgot') ?>">Forgot password?</a></p>
    </div>
    <div class="card">
        <h3>Access Portals</h3>
        <ul class="features">
            <li>Admin/Staff: Inventory, Sales, Customers, Reports</li>
            <li>Customer: Dashboard, Purchases, Installments</li>
        </ul>
    </div>
</div>

