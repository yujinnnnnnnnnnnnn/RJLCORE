<?php
require_once __DIR__ . '/../includes/auth.php';
require_role([1,2]);
require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../config/db.php';

// Update basic customer info
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = (int)($_POST['id'] ?? 0);
    $full_name = trim($_POST['full_name'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $address = trim($_POST['address'] ?? '');
    if ($id && $full_name) {
        safe_query('UPDATE users SET full_name=?, phone=?, address=? WHERE id=? AND role_id=3', 'sssi', [$full_name,$phone,$address,$id]);
    }
}

$page_title = 'Customers • AppliancePro';
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/navbar.php';

$customers = safe_query('SELECT id, full_name, email, phone, address, created_at FROM users WHERE role_id=3 ORDER BY created_at DESC')->get_result()->fetch_all(MYSQLI_ASSOC);
?>
<main class="container" style="padding:32px 0 64px">
  <h2>Customers</h2>
  <div class="card revealed" style="overflow:auto">
    <table style="width:100%;border-collapse:collapse">
      <thead>
        <tr>
          <th style="text-align:left;padding:8px;border-bottom:1px solid rgba(255,255,255,.15)">#</th>
          <th style="text-align:left;padding:8px;border-bottom:1px solid rgba(255,255,255,.15)">Name</th>
          <th style="text-align:left;padding:8px;border-bottom:1px solid rgba(255,255,255,.15)">Email</th>
          <th style="text-align:left;padding:8px;border-bottom:1px solid rgba(255,255,255,.15)">Phone</th>
          <th style="text-align:left;padding:8px;border-bottom:1px solid rgba(255,255,255,.15)">Address</th>
          <th style="text-align:left;padding:8px;border-bottom:1px solid rgba(255,255,255,.15)">Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($customers as $c): ?>
        <tr>
          <td style="padding:8px">#<?php echo (int)$c['id']; ?></td>
          <td style="padding:8px"><?php echo htmlspecialchars($c['full_name']); ?></td>
          <td style="padding:8px"><?php echo htmlspecialchars($c['email']); ?></td>
          <td style="padding:8px"><?php echo htmlspecialchars((string)$c['phone']); ?></td>
          <td style="padding:8px"><?php echo htmlspecialchars((string)$c['address']); ?></td>
          <td style="padding:8px">
            <details>
              <summary class="btn btn-outline">Edit</summary>
              <form method="post" style="margin-top:8px;display:grid;grid-template-columns:repeat(4,1fr);gap:8px">
                <input type="hidden" name="id" value="<?php echo (int)$c['id']; ?>">
                <input name="full_name" value="<?php echo htmlspecialchars($c['full_name']); ?>" style="padding:8px;border-radius:8px;border:1px solid rgba(255,255,255,.2);background:rgba(0,0,0,.15);color:#fff">
                <input name="phone" value="<?php echo htmlspecialchars((string)$c['phone']); ?>" style="padding:8px;border-radius:8px;border:1px solid rgba(255,255,255,.2);background:rgba(0,0,0,.15);color:#fff">
                <input name="address" value="<?php echo htmlspecialchars((string)$c['address']); ?>" style="padding:8px;border-radius:8px;border:1px solid rgba(255,255,255,.2);background:rgba(0,0,0,.15);color:#fff">
                <button class="btn btn-primary" type="submit">Save</button>
              </form>
            </details>
          </td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</main>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>

