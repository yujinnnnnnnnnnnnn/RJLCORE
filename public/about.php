<?php
require_once __DIR__ . '/../app/bootstrap.php';
$pageTitle = 'About Us — Appliances Store';
include __DIR__ . '/../views/templates/header.php';
?>

<section class="hero">
  <div>
    <h1>About Our Company</h1>
    <p>We provide reliable appliances with flexible payment options and exceptional customer service. This system empowers our team and our customers to manage purchases, installments, and service with ease.</p>
  </div>
  <div class="card">
    <img src="/public/assets/img/placeholder-about.png" alt="Company" style="width:100%;height:auto;border-radius:12px">
    <p class="muted" style="margin-top:8px">Insert your company/store image here.</p>
  </div>
</section>

<h2 class="section-title">Mission</h2>
<p>Deliver quality appliances and simplified purchasing experiences to every home and office.</p>

<h2 class="section-title">Vision</h2>
<p>Be the most trusted appliance partner in our region.</p>

<h2 class="section-title">Contact</h2>
<ul>
  <li><strong>Email:</strong> support@example.com</li>
  <li><strong>Phone:</strong> +1 (555) 123-4567</li>
  <li><strong>Address:</strong> 123 Appliance Ave, City</li>
  <li><strong>Hours:</strong> Mon–Sat 9:00–18:00</li>
  </ul>

<?php include __DIR__ . '/../views/templates/footer.php';
