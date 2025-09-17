<div class="grid-2">
    <div class="card">
        <h2>Create Customer Account</h2>
        <?php if (!empty($error)): ?>
            <div class="card" style="background:#fff0f0;border-color:#fecaca;color:#991b1b"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        <?php if (!empty($success)): ?>
            <div class="card" style="background:#ecfdf5;border-color:#bbf7d0;color:#065f46"><?= htmlspecialchars($success) ?></div>
        <?php endif; ?>
        <form method="post" action="<?= base_url('register') ?>" class="form">
            <?= csrf_field() ?>
            <label>Full Name<br>
                <input type="text" name="name" required style="width:100%;padding:10px;border:1px solid #e2e8f0;border-radius:10px">
            </label>
            <br>
            <label>Email<br>
                <input type="email" name="email" required style="width:100%;padding:10px;border:1px solid #e2e8f0;border-radius:10px">
            </label>
            <br>
            <label>Password<br>
                <input type="password" name="password" required minlength="6" style="width:100%;padding:10px;border:1px solid #e2e8f0;border-radius:10px">
            </label>
            <br>
            <button class="btn btn-primary" type="submit">Sign Up</button>
            <a class="btn" href="<?= base_url('login') ?>">Back to Login</a>
        </form>
    </div>
    <div class="card">
        <h3>Why sign up?</h3>
        <ul class="features">
            <li>View purchases and installment schedules</li>
            <li>Receive email reminders and notifications</li>
            <li>Manage your profile and contact details</li>
        </ul>
    </div>
</div>

