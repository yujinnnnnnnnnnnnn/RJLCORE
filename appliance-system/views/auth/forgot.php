<div class="card">
    <h2>Forgot Password</h2>
    <?php if (!empty($error)): ?>
        <div class="card" style="background:#fff0f0;border-color:#fecaca;color:#991b1b"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>
    <?php if (!empty($message)): ?>
        <div class="card" style="background:#ecfdf5;border-color:#bbf7d0;color:#065f46"><?= htmlspecialchars($message) ?></div>
    <?php endif; ?>
    <form method="post" action="<?= base_url('forgot') ?>">
        <?= csrf_field() ?>
        <label>Email<br>
            <input type="email" name="email" required style="width:100%;padding:10px;border:1px solid #e2e8f0;border-radius:10px">
        </label>
        <br>
        <button class="btn btn-primary" type="submit">Send Reset Link</button>
        <a class="btn" href="<?= base_url('login') ?>">Back to Login</a>
    </form>
</div>

