<div class="card">
    <h2>Reset Password</h2>
    <?php if (!empty($error)): ?>
        <div class="card" style="background:#fff0f0;border-color:#fecaca;color:#991b1b"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>
    <?php if (!empty($message)): ?>
        <div class="card" style="background:#ecfdf5;border-color:#bbf7d0;color:#065f46"><?= htmlspecialchars($message) ?></div>
    <?php endif; ?>
    <form method="post" action="<?= base_url('reset') ?>">
        <?= csrf_field() ?>
        <input type="hidden" name="token" value="<?= htmlspecialchars($token ?? '') ?>">
        <label>New Password<br>
            <input type="password" name="password" required minlength="6" style="width:100%;padding:10px;border:1px solid #e2e8f0;border-radius:10px">
        </label>
        <br>
        <button class="btn btn-primary" type="submit">Reset</button>
        <a class="btn" href="<?= base_url('login') ?>">Back to Login</a>
    </form>
</div>

