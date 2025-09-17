<?php
require_once __DIR__ . '/../../app/bootstrap.php';
use App\Auth; use App\Database; use App\Response; use App\Security;

// Admin or Staff only
if (!Auth::check() || !(Auth::hasRole('Admin') || Auth::hasRole('Staff'))) {
  Response::json(['error'=>'Unauthorized'], 401);
}

$method = $_SERVER['REQUEST_METHOD'];
switch ($method) {
  case 'GET':
    $stmt = Database::pdo()->query('SELECT * FROM products WHERE status = "active" ORDER BY created_at DESC');
    $rows = $stmt->fetchAll();
    Response::json(['data'=>$rows]);
    break;
  case 'POST':
    if (!Security::validateCsrf($_POST['csrf'] ?? '')) { Response::json(['error'=>'Bad CSRF'], 400);}    
    $name = trim($_POST['name'] ?? '');
    $sku = trim($_POST['sku'] ?? '');
    $price = (float)($_POST['price'] ?? 0);
    $stock = (int)($_POST['stock'] ?? 0);
    $desc = trim($_POST['description'] ?? '');
    $image = null;
    if (!empty($_FILES['image']['name'])) {
      $image = App\Util::uploadImage($_FILES['image'], __DIR__ . '/../../public/assets/img');
    }
    $stmt = Database::pdo()->prepare('INSERT INTO products (name, sku, description, price, stock, image_path) VALUES (?,?,?,?,?,?)');
    $stmt->execute([$name, $sku, $desc, $price, $stock, $image]);
    Response::json(['success'=>true]);
    break;
  case 'PUT':
    parse_str($_SERVER['QUERY_STRING'] ?? '', $qs);
    $id = (int)($qs['id'] ?? 0);
    if (!Security::validateCsrf($_GET['csrf'] ?? '')) { Response::json(['error'=>'Bad CSRF'], 400);}    
    $raw = file_get_contents('php://input');
    $data = json_decode($raw, true) ?: [];
    $stmt = Database::pdo()->prepare('UPDATE products SET name=?, sku=?, description=?, price=?, stock=? WHERE id=?');
    $stmt->execute([trim($data['name'] ?? ''), trim($data['sku'] ?? ''), trim($data['description'] ?? ''), (float)($data['price'] ?? 0), (int)($data['stock'] ?? 0), $id]);
    Response::json(['success'=>true]);
    break;
  case 'DELETE':
    parse_str($_SERVER['QUERY_STRING'] ?? '', $qs);
    $id = (int)($qs['id'] ?? 0);
    if (!Security::validateCsrf($_GET['csrf'] ?? '')) { Response::json(['error'=>'Bad CSRF'], 400);}    
    $stmt = Database::pdo()->prepare('UPDATE products SET status = "archived" WHERE id = ?');
    $stmt->execute([$id]);
    Response::json(['success'=>true]);
    break;
  default:
    Response::json(['error'=>'Method not allowed'], 405);
}
