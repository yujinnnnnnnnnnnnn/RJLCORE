<?php
require_once __DIR__ . '/../app/bootstrap.php';
$pageTitle = 'Appliances Store — Home';
include __DIR__ . '/../views/templates/header.php';
?>

<section class="hero">
  <div>
    <h1>Your Trusted Appliances Partner</h1>
    <p>Discover premium home and office appliances. Manage inventory, track sales, and monitor installments with our modern system.</p>
    <div style="margin-top:16px">
      <a class="btn btn-primary" href="/public/login.php">Admin/Staff Portal</a>
      <a class="btn btn-outline" href="/public/login.php">Customer Portal</a>
    </div>
  </div>
  <div class="card">
    <img src="/public/assets/img/placeholder-hero.png" alt="Appliances" style="width:100%;height:auto;border-radius:12px;opacity:.95">
    <p class="muted" style="margin-top:8px">Insert your banner image here.</p>
  </div>
</section>

<h2 class="section-title">Featured Appliances</h2>
<div class="grid">
  <div class="card">
    <img src="/public/assets/img/placeholder1.png" style="width:100%;border-radius:10px" alt="Product 1">
    <h3>Product Name</h3>
    <p class="muted">Short highlight. Replace with real items.</p>
  </div>
  <div class="card">
    <img src="/public/assets/img/placeholder2.png" style="width:100%;border-radius:10px" alt="Product 2">
    <h3>Product Name</h3>
    <p class="muted">Short highlight. Replace with real items.</p>
  </div>
  <div class="card">
    <img src="/public/assets/img/placeholder3.png" style="width:100%;border-radius:10px" alt="Product 3">
    <h3>Product Name</h3>
    <p class="muted">Short highlight. Replace with real items.</p>
  </div>
</div>

<?php include __DIR__ . '/../views/templates/footer.php';
