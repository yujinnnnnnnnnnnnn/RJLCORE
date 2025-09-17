<?php
require_once __DIR__ . '/../includes/auth.php';
require_role([1,2]);
require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../config/db.php';

$action = $_GET['action'] ?? 'list';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (($_POST['form'] ?? '') === 'create') {
        $sku = trim($_POST['sku'] ?? '');
        $name = trim($_POST['name'] ?? '');
        $brand = trim($_POST['brand'] ?? '');
        $category = trim($_POST['category'] ?? '');
        $price = (float)($_POST['price'] ?? 0);
        $stock = (int)($_POST['stock'] ?? 0);
        safe_query('INSERT INTO products (sku, name, brand, category, price, stock) VALUES (?,?,?,?,?,?)', 'ssssdi', [$sku,$name,$brand,$category,$price,$stock]);
        header('Location: inventory.php');
        exit;
    }
    if (($_POST['form'] ?? '') === 'update') {
        $id = (int)($_POST['id'] ?? 0);
        $sku = trim($_POST['sku'] ?? '');
        $name = trim($_POST['name'] ?? '');
        $brand = trim($_POST['brand'] ?? '');
        $category = trim($_POST['category'] ?? '');
        $price = (float)($_POST['price'] ?? 0);
        $stock = (int)($_POST['stock'] ?? 0);
        safe_query('UPDATE products SET sku=?, name=?, brand=?, category=?, price=?, stock=? WHERE id=?', 'ssssdii', [$sku,$name,$brand,$category,$price,$stock,$id]);
        header('Location: inventory.php');
        exit;
    }
}

if ($action === 'delete') {
    $id = (int)($_GET['id'] ?? 0);
    if ($id) {
        safe_query('DELETE FROM products WHERE id = ?', 'i', [$id]);
    }
    header('Location: inventory.php');
    exit;
}

$page_title = 'Inventory • AppliancePro';
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/navbar.php';

// Fetch products
$stmt = safe_query('SELECT id, sku, name, brand, category, price, stock, is_active FROM products ORDER BY created_at DESC');
$products = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
?>
<main class="container" style="padding:32px 0 64px">
  <h2>Inventory</h2>
  <div class="card revealed" style="margin-top:12px">
    <form method="post" action="" class="grid-2">
      <input type="hidden" name="form" value="create">
      <div>
        <label>SKU</label>
        <input name="sku" required style="width:100%;padding:10px;border-radius:10px;border:1px solid rgba(255,255,255,.2);background:rgba(0,0,0,.15);color:#fff">
      </div>
      <div>
        <label>Name</label>
        <input name="name" required style="width:100%;padding:10px;border-radius:10px;border:1px solid rgba(255,255,255,.2);background:rgba(0,0,0,.15);color:#fff">
      </div>
      <div>
        <label>Brand</label>
        <input name="brand" style="width:100%;padding:10px;border-radius:10px;border:1px solid rgba(255,255,255,.2);background:rgba(0,0,0,.15);color:#fff">
      </div>
      <div>
        <label>Category</label>
        <input name="category" style="width:100%;padding:10px;border-radius:10px;border:1px solid rgba(255,255,255,.2);background:rgba(0,0,0,.15);color:#fff">
      </div>
      <div>
        <label>Price</label>
        <input name="price" type="number" step="0.01" min="0" required style="width:100%;padding:10px;border-radius:10px;border:1px solid rgba(255,255,255,.2);background:rgba(0,0,0,.15);color:#fff">
      </div>
      <div>
        <label>Stock</label>
        <input name="stock" type="number" step="1" min="0" required style="width:100%;padding:10px;border-radius:10px;border:1px solid rgba(255,255,255,.2);background:rgba(0,0,0,.15);color:#fff">
      </div>
      <div style="grid-column:1/-1;text-align:right">
        <button class="btn btn-primary" type="submit">Add Product</button>
      </div>
    </form>
  </div>

  <div class="card revealed" style="margin-top:12px;overflow:auto">
    <table style="width:100%;border-collapse:collapse">
      <thead>
        <tr>
          <th style="text-align:left;padding:8px;border-bottom:1px solid rgba(255,255,255,.15)">#</th>
          <th style="text-align:left;padding:8px;border-bottom:1px solid rgba(255,255,255,.15)">SKU</th>
          <th style="text-align:left;padding:8px;border-bottom:1px solid rgba(255,255,255,.15)">Name</th>
          <th style="text-align:left;padding:8px;border-bottom:1px solid rgba(255,255,255,.15)">Brand</th>
          <th style="text-align:left;padding:8px;border-bottom:1px solid rgba(255,255,255,.15)">Category</th>
          <th style="text-align:left;padding:8px;border-bottom:1px solid rgba(255,255,255,.15)">Price</th>
          <th style="text-align:left;padding:8px;border-bottom:1px solid rgba(255,255,255,.15)">Stock</th>
          <th style="text-align:left;padding:8px;border-bottom:1px solid rgba(255,255,255,.15)">Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($products as $p): ?>
        <tr>
          <td style="padding:8px">#<?php echo (int)$p['id']; ?></td>
          <td style="padding:8px"><?php echo htmlspecialchars($p['sku']); ?></td>
          <td style="padding:8px"><?php echo htmlspecialchars($p['name']); ?></td>
          <td style="padding:8px"><?php echo htmlspecialchars($p['brand']); ?></td>
          <td style="padding:8px"><?php echo htmlspecialchars($p['category']); ?></td>
          <td style="padding:8px"><?php echo number_format((float)$p['price'],2); ?></td>
          <td style="padding:8px"><?php echo (int)$p['stock']; ?></td>
          <td style="padding:8px;display:flex;gap:8px">
            <form method="post" action="" style="display:inline-flex;gap:8px">
              <input type="hidden" name="form" value="update">
              <input type="hidden" name="id" value="<?php echo (int)$p['id']; ?>">
              <input name="sku" value="<?php echo htmlspecialchars($p['sku']); ?>" style="width:120px;padding:6px;border-radius:8px;border:1px solid rgba(255,255,255,.2);background:rgba(0,0,0,.15);color:#fff">
              <input name="name" value="<?php echo htmlspecialchars($p['name']); ?>" style="width:160px;padding:6px;border-radius:8px;border:1px solid rgba(255,255,255,.2);background:rgba(0,0,0,.15);color:#fff">
              <input name="brand" value="<?php echo htmlspecialchars($p['brand']); ?>" style="width:120px;padding:6px;border-radius:8px;border:1px solid rgba(255,255,255,.2);background:rgba(0,0,0,.15);color:#fff">
              <input name="category" value="<?php echo htmlspecialchars($p['category']); ?>" style="width:120px;padding:6px;border-radius:8px;border:1px solid rgba(255,255,255,.2);background:rgba(0,0,0,.15);color:#fff">
              <input name="price" type="number" step="0.01" min="0" value="<?php echo number_format((float)$p['price'],2,'.',''); ?>" style="width:110px;padding:6px;border-radius:8px;border:1px solid rgba(255,255,255,.2);background:rgba(0,0,0,.15);color:#fff">
              <input name="stock" type="number" step="1" min="0" value="<?php echo (int)$p['stock']; ?>" style="width:90px;padding:6px;border-radius:8px;border:1px solid rgba(255,255,255,.2);background:rgba(0,0,0,.15);color:#fff">
              <button class="btn btn-outline" type="submit">Save</button>
            </form>
            <a class="btn btn-primary" href="inventory.php?action=delete&id=<?php echo (int)$p['id']; ?>" onclick="return confirm('Delete this product?')">Delete</a>
          </td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</main>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>

