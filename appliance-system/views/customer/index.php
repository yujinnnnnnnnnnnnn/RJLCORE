<div class="grid-2">
    <div class="card">
        <h2>Welcome<?= isset($_SESSION['user']['name']) ? ', '.htmlspecialchars($_SESSION['user']['name']) : '' ?></h2>
        <p class="muted">Quick links to your purchases and installments</p>
        <div class="grid-2">
            <div class="card">
                <h3>Purchase History</h3>
                <p class="muted">View all your past orders</p>
            </div>
            <div class="card">
                <h3>Installments</h3>
                <p class="muted">Upcoming payments and balances</p>
            </div>
        </div>
    </div>
    <div class="card">
        <h3>Profile</h3>
        <p class="muted">Update contact details and change password</p>
        <a class="btn btn-primary" href="#">Manage Profile</a>
        <a class="btn" href="<?= base_url('logout') ?>">Logout</a>
    </div>
</div>

