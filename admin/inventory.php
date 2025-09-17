<?php
require_once __DIR__ . '/../app/bootstrap.php';
use App\Auth; use App\Security; use App\Database;
Auth::requireRole(['Admin','Staff']);
$pageTitle = 'Inventory — Admin/Staff';
include __DIR__ . '/../views/templates/header.php';
?>

<div style="display:flex;gap:20px">
  <aside class="sidebar">
    <nav>
      <a href="/admin/index.php">Overview</a>
      <a href="/admin/inventory.php" style="background:#0b2e49">Inventory</a>
      <a href="/admin/sales.php">Sales & POS</a>
      <a href="/admin/customers.php">Customers</a>
      <a href="/admin/reports.php">Reports</a>
      <a href="/admin/settings.php">Settings</a>
      <a href="/public/logout.php">Logout</a>
    </nav>
  </aside>
  <section class="content" style="flex:1">
    <h1>Inventory</h1>
    <div class="card" style="margin-bottom:16px">
      <h3 style="margin-top:0">Add Product</h3>
      <form id="productForm" enctype="multipart/form-data">
        <input type="hidden" name="csrf" value="<?= Security::csrfToken() ?>">
        <div class="grid" style="grid-template-columns:repeat(2,1fr)">
          <div class="form-group"><label>Name</label><input name="name" required></div>
          <div class="form-group"><label>SKU</label><input name="sku" required></div>
          <div class="form-group"><label>Price</label><input type="number" step="0.01" name="price" required></div>
          <div class="form-group"><label>Stock</label><input type="number" name="stock" required></div>
          <div class="form-group" style="grid-column:1/-1"><label>Description</label><textarea name="description"></textarea></div>
          <div class="form-group"><label>Image</label><input type="file" name="image" accept="image/*"></div>
        </div>
        <button class="btn btn-primary" type="submit">Save Product</button>
      </form>
    </div>

    <div class="card">
      <h3 style="margin-top:0">Products</h3>
      <table style="width:100%;border-collapse:collapse">
        <thead>
          <tr style="text-align:left">
            <th>Image</th><th>Name</th><th>SKU</th><th>Price</th><th>Stock</th><th>Actions</th>
          </tr>
        </thead>
        <tbody id="productsBody"></tbody>
      </table>
    </div>
  </section>
</div>

<script>
const bodyEl = document.getElementById('productsBody');
const csrf = '<?= Security::csrfToken() ?>';
async function loadProducts(){
  const res = await fetch('/api/products/index.php');
  const json = await res.json();
  const rows = (json.data||[]).map(p=>`
    <tr>
      <td style="padding:8px"><img src="/public/assets/img/${p.image_path||'placeholder1.png'}" alt="" style="width:64px;height:48px;object-fit:cover;border-radius:8px"></td>
      <td style="padding:8px">${p.name}</td>
      <td style="padding:8px">${p.sku}</td>
      <td style="padding:8px">$${Number(p.price).toFixed(2)}</td>
      <td style="padding:8px">${p.stock}</td>
      <td style="padding:8px">
        <button class="btn btn-outline" onclick="archive(${p.id})">Delete</button>
      </td>
    </tr>`).join('');
  bodyEl.innerHTML = rows || '<tr><td colspan="6" class="muted">No products yet.</td></tr>';
}
loadProducts();

document.getElementById('productForm').addEventListener('submit', async (e)=>{
  e.preventDefault();
  const fd = new FormData(e.target);
  const res = await fetch('/api/products/index.php', {method:'POST', body:fd});
  if (res.ok) {
    e.target.reset();
    loadProducts();
  }
});

async function archive(id){
  const res = await fetch('/api/products/index.php?id='+id+'&csrf='+encodeURIComponent(csrf), {method:'DELETE'});
  if (res.ok) loadProducts();
}
</script>

<?php include __DIR__ . '/../views/templates/footer.php';
