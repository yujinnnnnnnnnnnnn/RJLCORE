<?php
$page_title = 'AppliancePro • Home';
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/navbar.php';
?>

<main>
  <section class="hero">
    <div class="container hero-grid">
      <div>
        <span class="kicker">Smart. Reliable. Efficient.</span>
        <h1>Web-Based Appliances Management System</h1>
        <p>Manage inventory, sales, installments, and customers in one modern, secure platform. Built for appliance stores to streamline daily operations.</p>
        <div class="hero-cta">
          <a href="<?php echo url('admin/'); ?>" class="btn btn-primary">Admin / Staff Portal</a>
          <a href="<?php echo url('customer/'); ?>" class="btn btn-outline">Customer Portal</a>
        </div>
      </div>
      <div class="hero-art">
        <div class="hero-card">
          <div class="grid-2">
            <div>
              <div class="badge">Inventory</div>
              <h3>Real-time Stock</h3>
              <p class="muted">Track product availability, variants, and low-stock alerts.</p>
            </div>
            <div>
              <div class="badge">Sales</div>
              <h3>POS & Installments</h3>
              <p class="muted">Process full or installment payments with receipts and schedules.</p>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>

  <section class="features">
    <div class="container">
      <div class="features-grid">
        <div class="card">
          <div class="badge">Authentication</div>
          <h3>Role-Based Access</h3>
          <p class="muted">Secure login for Admin, Staff, and Customers with password hashing.</p>
        </div>
        <div class="card">
          <div class="badge">Analytics</div>
          <h3>Reports & Charts</h3>
          <p class="muted">Visualize sales performance and inventory metrics.</p>
        </div>
        <div class="card">
          <div class="badge">Messaging</div>
          <h3>Email Reminders</h3>
          <p class="muted">Automated notifications for installment due dates and updates.</p>
        </div>
      </div>
    </div>
  </section>
</main>

<?php require_once __DIR__ . '/includes/footer.php'; ?>

