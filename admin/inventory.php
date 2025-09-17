<?php
require_once '../config/config.php';
require_once '../classes/Auth.php';

// Check authentication and role
require_login();
require_role(['admin', 'staff']);

$database = new Database();
$db = $database->getConnection();

$success_message = '';
$error_message = '';

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'add_product':
                $result = addProduct($db, $_POST);
                break;
            case 'update_product':
                $result = updateProduct($db, $_POST);
                break;
            case 'delete_product':
                $result = deleteProduct($db, $_POST['product_id']);
                break;
            case 'adjust_stock':
                $result = adjustStock($db, $_POST);
                break;
        }
        
        if ($_POST['ajax'] ?? false) {
            header('Content-Type: application/json');
            echo json_encode($result);
            exit;
        }
        
        if ($result['success']) {
            $success_message = $result['message'];
        } else {
            $error_message = $result['message'];
        }
    }
}

// Get products with filtering
$filter = $_GET['filter'] ?? 'all';
$search = $_GET['search'] ?? '';
$page = (int)($_GET['page'] ?? 1);
$limit = RECORDS_PER_PAGE;
$offset = ($page - 1) * $limit;

$where_conditions = ["p.is_active = 1"];
$params = [];

if ($filter === 'low_stock') {
    $where_conditions[] = "p.stock_quantity <= p.min_stock_level";
} elseif ($filter === 'out_of_stock') {
    $where_conditions[] = "p.stock_quantity = 0";
}

if ($search) {
    $where_conditions[] = "(p.name LIKE :search OR p.brand LIKE :search OR p.category LIKE :search)";
    $params['search'] = '%' . $search . '%';
}

$where_clause = "WHERE " . implode(" AND ", $where_conditions);

try {
    // Get total count
    $count_query = "SELECT COUNT(*) as total FROM products p $where_clause";
    $stmt = $db->prepare($count_query);
    foreach ($params as $key => $value) {
        $stmt->bindValue(':' . $key, $value);
    }
    $stmt->execute();
    $total_records = $stmt->fetch()['total'];
    $total_pages = ceil($total_records / $limit);

    // Get products
    $query = "SELECT p.*, 
              (SELECT COUNT(*) FROM sale_items si WHERE si.product_id = p.id) as total_sold
              FROM products p 
              $where_clause 
              ORDER BY p.updated_at DESC 
              LIMIT :limit OFFSET :offset";
    
    $stmt = $db->prepare($query);
    foreach ($params as $key => $value) {
        $stmt->bindValue(':' . $key, $value);
    }
    $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    $stmt->execute();
    $products = $stmt->fetchAll();

    // Get categories for dropdown
    $stmt = $db->prepare("SELECT DISTINCT category FROM products WHERE is_active = 1 ORDER BY category");
    $stmt->execute();
    $categories = $stmt->fetchAll(PDO::FETCH_COLUMN);

} catch (PDOException $e) {
    $error_message = "Database error: " . $e->getMessage();
    $products = [];
    $categories = [];
}

// Product management functions
function addProduct($db, $data) {
    try {
        $query = "INSERT INTO products (name, brand, model, category, description, price, cost_price, stock_quantity, min_stock_level, warranty_months) 
                 VALUES (:name, :brand, :model, :category, :description, :price, :cost_price, :stock_quantity, :min_stock_level, :warranty_months)";
        
        $stmt = $db->prepare($query);
        $stmt->bindParam(':name', $data['name']);
        $stmt->bindParam(':brand', $data['brand']);
        $stmt->bindParam(':model', $data['model']);
        $stmt->bindParam(':category', $data['category']);
        $stmt->bindParam(':description', $data['description']);
        $stmt->bindParam(':price', $data['price']);
        $stmt->bindParam(':cost_price', $data['cost_price']);
        $stmt->bindParam(':stock_quantity', $data['stock_quantity']);
        $stmt->bindParam(':min_stock_level', $data['min_stock_level']);
        $stmt->bindParam(':warranty_months', $data['warranty_months']);
        
        if ($stmt->execute()) {
            $product_id = $db->lastInsertId();
            
            // Log inventory addition
            logInventoryChange($db, $product_id, $_SESSION['user_id'], 'add', $data['stock_quantity'], 0, $data['stock_quantity'], 'Initial stock');
            
            return [
                'success' => true,
                'message' => 'Product added successfully',
                'product_id' => $product_id
            ];
        } else {
            return ['success' => false, 'message' => 'Failed to add product'];
        }
    } catch (PDOException $e) {
        return ['success' => false, 'message' => 'Database error: ' . $e->getMessage()];
    }
}

function updateProduct($db, $data) {
    try {
        $query = "UPDATE products SET name = :name, brand = :brand, model = :model, category = :category, 
                 description = :description, price = :price, cost_price = :cost_price, 
                 min_stock_level = :min_stock_level, warranty_months = :warranty_months
                 WHERE id = :id";
        
        $stmt = $db->prepare($query);
        $stmt->bindParam(':name', $data['name']);
        $stmt->bindParam(':brand', $data['brand']);
        $stmt->bindParam(':model', $data['model']);
        $stmt->bindParam(':category', $data['category']);
        $stmt->bindParam(':description', $data['description']);
        $stmt->bindParam(':price', $data['price']);
        $stmt->bindParam(':cost_price', $data['cost_price']);
        $stmt->bindParam(':min_stock_level', $data['min_stock_level']);
        $stmt->bindParam(':warranty_months', $data['warranty_months']);
        $stmt->bindParam(':id', $data['product_id']);
        
        if ($stmt->execute()) {
            return ['success' => true, 'message' => 'Product updated successfully'];
        } else {
            return ['success' => false, 'message' => 'Failed to update product'];
        }
    } catch (PDOException $e) {
        return ['success' => false, 'message' => 'Database error: ' . $e->getMessage()];
    }
}

function deleteProduct($db, $product_id) {
    try {
        // Soft delete - set is_active to 0
        $query = "UPDATE products SET is_active = 0 WHERE id = :id";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':id', $product_id);
        
        if ($stmt->execute()) {
            return ['success' => true, 'message' => 'Product deleted successfully'];
        } else {
            return ['success' => false, 'message' => 'Failed to delete product'];
        }
    } catch (PDOException $e) {
        return ['success' => false, 'message' => 'Database error: ' . $e->getMessage()];
    }
}

function adjustStock($db, $data) {
    try {
        // Get current stock
        $stmt = $db->prepare("SELECT stock_quantity FROM products WHERE id = :id");
        $stmt->bindParam(':id', $data['product_id']);
        $stmt->execute();
        $current_stock = $stmt->fetch()['stock_quantity'];
        
        $new_stock = $current_stock + $data['quantity_change'];
        
        if ($new_stock < 0) {
            return ['success' => false, 'message' => 'Insufficient stock'];
        }
        
        // Update stock
        $stmt = $db->prepare("UPDATE products SET stock_quantity = :stock WHERE id = :id");
        $stmt->bindParam(':stock', $new_stock);
        $stmt->bindParam(':id', $data['product_id']);
        $stmt->execute();
        
        // Log the change
        $action = $data['quantity_change'] > 0 ? 'add' : 'remove';
        logInventoryChange($db, $data['product_id'], $_SESSION['user_id'], $action, abs($data['quantity_change']), $current_stock, $new_stock, $data['reason']);
        
        return ['success' => true, 'message' => 'Stock adjusted successfully'];
    } catch (PDOException $e) {
        return ['success' => false, 'message' => 'Database error: ' . $e->getMessage()];
    }
}

function logInventoryChange($db, $product_id, $user_id, $action, $quantity_change, $previous_stock, $new_stock, $reason) {
    try {
        $query = "INSERT INTO inventory_logs (product_id, user_id, action, quantity_change, previous_stock, new_stock, reason) 
                 VALUES (:product_id, :user_id, :action, :quantity_change, :previous_stock, :new_stock, :reason)";
        
        $stmt = $db->prepare($query);
        $stmt->bindParam(':product_id', $product_id);
        $stmt->bindParam(':user_id', $user_id);
        $stmt->bindParam(':action', $action);
        $stmt->bindParam(':quantity_change', $quantity_change);
        $stmt->bindParam(':previous_stock', $previous_stock);
        $stmt->bindParam(':new_stock', $new_stock);
        $stmt->bindParam(':reason', $reason);
        $stmt->execute();
    } catch (PDOException $e) {
        error_log("Failed to log inventory change: " . $e->getMessage());
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inventory Management - <?php echo APP_NAME; ?></title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <!-- Sidebar -->
    <nav class="sidebar">
        <div style="padding: 2rem 1rem; border-bottom: 1px solid rgba(255,255,255,0.1);">
            <div style="text-align: center; color: white;">
                <div style="width: 60px; height: 60px; background: rgba(255,255,255,0.1); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 1rem; font-size: 1.5rem;">
                    <i class="fas fa-user-tie"></i>
                </div>
                <h4><?php echo $_SESSION['first_name'] . ' ' . $_SESSION['last_name']; ?></h4>
                <small><?php echo ucfirst($_SESSION['role_name']); ?></small>
            </div>
        </div>
        
        <ul class="sidebar-menu">
            <li><a href="dashboard.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
            <li><a href="inventory.php" class="active"><i class="fas fa-boxes"></i> Inventory</a></li>
            <li><a href="sales.php"><i class="fas fa-shopping-cart"></i> Sales</a></li>
            <li><a href="customers.php"><i class="fas fa-users"></i> Customers</a></li>
            <li><a href="installments.php"><i class="fas fa-calendar-alt"></i> Installments</a></li>
            <li><a href="transactions.php"><i class="fas fa-money-bill-wave"></i> Transactions</a></li>
            <li><a href="reports.php"><i class="fas fa-chart-bar"></i> Reports</a></li>
            <?php if ($_SESSION['role_name'] === 'admin'): ?>
            <li><a href="users.php"><i class="fas fa-user-cog"></i> User Management</a></li>
            <li><a href="settings.php"><i class="fas fa-cogs"></i> Settings</a></li>
            <?php endif; ?>
            <li><a href="profile.php"><i class="fas fa-user"></i> Profile</a></li>
            <li><a href="../logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
        </ul>
    </nav>

    <!-- Main Content -->
    <main class="main-content">
        <div class="content-header">
            <div style="display: flex; justify-content: space-between; align-items: center;">
                <div>
                    <h1><i class="fas fa-boxes"></i> Inventory Management</h1>
                    <p>Manage your product inventory and stock levels</p>
                </div>
                <div>
                    <button class="btn btn-primary" onclick="openModal('addProductModal')">
                        <i class="fas fa-plus"></i> Add Product
                    </button>
                </div>
            </div>
        </div>

        <?php if ($success_message): ?>
            <div class="notification success">
                <i class="fas fa-check-circle"></i>
                <?php echo $success_message; ?>
            </div>
        <?php endif; ?>

        <?php if ($error_message): ?>
            <div class="notification error">
                <i class="fas fa-exclamation-circle"></i>
                <?php echo $error_message; ?>
            </div>
        <?php endif; ?>

        <!-- Filters and Search -->
        <div class="card">
            <div style="display: flex; justify-content: between; align-items: center; gap: 1rem; flex-wrap: wrap;">
                <div style="display: flex; gap: 1rem; align-items: center;">
                    <label>Filter:</label>
                    <select id="filterSelect" class="form-control" style="width: auto;">
                        <option value="all" <?php echo $filter === 'all' ? 'selected' : ''; ?>>All Products</option>
                        <option value="low_stock" <?php echo $filter === 'low_stock' ? 'selected' : ''; ?>>Low Stock</option>
                        <option value="out_of_stock" <?php echo $filter === 'out_of_stock' ? 'selected' : ''; ?>>Out of Stock</option>
                    </select>
                </div>
                <div style="flex: 1; max-width: 400px;">
                    <div style="position: relative;">
                        <input type="text" id="searchInput" class="form-control" placeholder="Search products..." value="<?php echo htmlspecialchars($search); ?>">
                        <i class="fas fa-search" style="position: absolute; right: 15px; top: 50%; transform: translateY(-50%); color: var(--gray);"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Products Table -->
        <div class="card">
            <div class="card-header">
                <div style="display: flex; justify-content: space-between; align-items: center;">
                    <h3>Products (<?php echo number_format($total_records); ?> items)</h3>
                    <div class="btn-group">
                        <button class="btn btn-secondary btn-sm" onclick="exportInventory()">
                            <i class="fas fa-download"></i> Export
                        </button>
                        <button class="btn btn-info btn-sm" onclick="location.reload()">
                            <i class="fas fa-sync-alt"></i> Refresh
                        </button>
                    </div>
                </div>
            </div>

            <?php if (empty($products)): ?>
                <div class="text-center p-5">
                    <i class="fas fa-boxes" style="font-size: 4rem; color: var(--gray); opacity: 0.5;"></i>
                    <h4 class="mt-3">No Products Found</h4>
                    <p>Start by adding your first product to the inventory.</p>
                    <button class="btn btn-primary" onclick="openModal('addProductModal')">
                        <i class="fas fa-plus"></i> Add First Product
                    </button>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table data-table" id="productsTable">
                        <thead>
                            <tr>
                                <th data-sortable data-column="image">Image</th>
                                <th data-sortable data-column="name">Product</th>
                                <th data-sortable data-column="category">Category</th>
                                <th data-sortable data-column="price">Price</th>
                                <th data-sortable data-column="stock">Stock</th>
                                <th data-sortable data-column="status">Status</th>
                                <th data-sortable data-column="sold">Sold</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($products as $product): ?>
                            <tr>
                                <td data-column="image">
                                    <div style="width: 50px; height: 50px; background: linear-gradient(135deg, var(--beige), var(--green)); border-radius: 8px; display: flex; align-items: center; justify-content: center;">
                                        <?php if ($product['image_path']): ?>
                                            <img src="../<?php echo htmlspecialchars($product['image_path']); ?>" alt="Product" style="width: 100%; height: 100%; object-fit: cover; border-radius: 8px;">
                                        <?php else: ?>
                                            <i class="fas fa-image" style="color: var(--navy); opacity: 0.6;"></i>
                                        <?php endif; ?>
                                    </div>
                                </td>
                                <td data-column="name">
                                    <div>
                                        <strong><?php echo htmlspecialchars($product['name']); ?></strong><br>
                                        <small class="text-gray"><?php echo htmlspecialchars($product['brand'] . ' ' . $product['model']); ?></small>
                                    </div>
                                </td>
                                <td data-column="category">
                                    <span class="badge badge-info"><?php echo htmlspecialchars($product['category']); ?></span>
                                </td>
                                <td data-column="price">
                                    <strong><?php echo format_currency($product['price']); ?></strong><br>
                                    <small class="text-gray">Cost: <?php echo format_currency($product['cost_price']); ?></small>
                                </td>
                                <td data-column="stock">
                                    <div style="display: flex; align-items: center; gap: 0.5rem;">
                                        <span class="<?php echo $product['stock_quantity'] <= $product['min_stock_level'] ? 'text-danger' : ''; ?>">
                                            <strong><?php echo $product['stock_quantity']; ?></strong>
                                        </span>
                                        <button class="btn btn-sm" onclick="openStockModal(<?php echo $product['id']; ?>, '<?php echo htmlspecialchars($product['name']); ?>', <?php echo $product['stock_quantity']; ?>)" title="Adjust Stock">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                    </div>
                                    <small class="text-gray">Min: <?php echo $product['min_stock_level']; ?></small>
                                </td>
                                <td data-column="status">
                                    <?php if ($product['stock_quantity'] == 0): ?>
                                        <span class="badge badge-danger">Out of Stock</span>
                                    <?php elseif ($product['stock_quantity'] <= $product['min_stock_level']): ?>
                                        <span class="badge badge-warning">Low Stock</span>
                                    <?php else: ?>
                                        <span class="badge badge-success">In Stock</span>
                                    <?php endif; ?>
                                </td>
                                <td data-column="sold"><?php echo number_format($product['total_sold']); ?></td>
                                <td>
                                    <div class="btn-group">
                                        <button class="btn btn-sm" onclick="viewProduct(<?php echo $product['id']; ?>)" title="View Details">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                        <button class="btn btn-sm" onclick="editProduct(<?php echo htmlspecialchars(json_encode($product)); ?>)" title="Edit Product">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <button class="btn btn-danger btn-sm" onclick="deleteProduct(<?php echo $product['id']; ?>, '<?php echo htmlspecialchars($product['name']); ?>')" title="Delete Product">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <?php if ($total_pages > 1): ?>
                    <div class="pagination-container" style="display: flex; justify-content: center; padding: 1rem;">
                        <div class="pagination">
                            <?php if ($page > 1): ?>
                                <a href="?page=<?php echo $page - 1; ?>&filter=<?php echo $filter; ?>&search=<?php echo urlencode($search); ?>" class="btn btn-sm">&laquo; Previous</a>
                            <?php endif; ?>
                            
                            <?php for ($i = max(1, $page - 2); $i <= min($total_pages, $page + 2); $i++): ?>
                                <a href="?page=<?php echo $i; ?>&filter=<?php echo $filter; ?>&search=<?php echo urlencode($search); ?>" 
                                   class="btn btn-sm <?php echo $i === $page ? 'btn-primary' : ''; ?>">
                                    <?php echo $i; ?>
                                </a>
                            <?php endfor; ?>
                            
                            <?php if ($page < $total_pages): ?>
                                <a href="?page=<?php echo $page + 1; ?>&filter=<?php echo $filter; ?>&search=<?php echo urlencode($search); ?>" class="btn btn-sm">Next &raquo;</a>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </main>

    <!-- Add Product Modal -->
    <div id="addProductModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3><i class="fas fa-plus"></i> Add New Product</h3>
                <button type="button" class="close" onclick="closeModal('addProductModal')">&times;</button>
            </div>
            <form class="ajax-form" action="inventory.php" method="POST">
                <input type="hidden" name="action" value="add_product">
                <input type="hidden" name="ajax" value="1">
                
                <div class="row">
                    <div class="col-6">
                        <div class="form-group">
                            <label class="form-label">Product Name *</label>
                            <input type="text" name="name" class="form-control" required>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="form-group">
                            <label class="form-label">Brand *</label>
                            <input type="text" name="brand" class="form-control" required>
                        </div>
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-6">
                        <div class="form-group">
                            <label class="form-label">Model</label>
                            <input type="text" name="model" class="form-control">
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="form-group">
                            <label class="form-label">Category *</label>
                            <select name="category" class="form-control" required>
                                <option value="">Select Category</option>
                                <?php foreach ($categories as $category): ?>
                                    <option value="<?php echo htmlspecialchars($category); ?>"><?php echo htmlspecialchars($category); ?></option>
                                <?php endforeach; ?>
                                <option value="other">Other (specify in description)</option>
                            </select>
                        </div>
                    </div>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Description</label>
                    <textarea name="description" class="form-control" rows="3"></textarea>
                </div>
                
                <div class="row">
                    <div class="col-4">
                        <div class="form-group">
                            <label class="form-label">Selling Price *</label>
                            <input type="number" name="price" class="form-control" step="0.01" min="0" required>
                        </div>
                    </div>
                    <div class="col-4">
                        <div class="form-group">
                            <label class="form-label">Cost Price *</label>
                            <input type="number" name="cost_price" class="form-control" step="0.01" min="0" required>
                        </div>
                    </div>
                    <div class="col-4">
                        <div class="form-group">
                            <label class="form-label">Warranty (months)</label>
                            <input type="number" name="warranty_months" class="form-control" min="0" value="12">
                        </div>
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-6">
                        <div class="form-group">
                            <label class="form-label">Initial Stock *</label>
                            <input type="number" name="stock_quantity" class="form-control" min="0" required>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="form-group">
                            <label class="form-label">Minimum Stock Level *</label>
                            <input type="number" name="min_stock_level" class="form-control" min="1" value="5" required>
                        </div>
                    </div>
                </div>
                
                <div class="form-group">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Add Product
                    </button>
                    <button type="button" class="btn btn-secondary" onclick="closeModal('addProductModal')">
                        Cancel
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Edit Product Modal -->
    <div id="editProductModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3><i class="fas fa-edit"></i> Edit Product</h3>
                <button type="button" class="close" onclick="closeModal('editProductModal')">&times;</button>
            </div>
            <form class="ajax-form" action="inventory.php" method="POST" id="editProductForm">
                <input type="hidden" name="action" value="update_product">
                <input type="hidden" name="ajax" value="1">
                <input type="hidden" name="product_id" id="edit_product_id">
                
                <!-- Form fields will be populated by JavaScript -->
                <div id="editFormFields"></div>
                
                <div class="form-group">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Update Product
                    </button>
                    <button type="button" class="btn btn-secondary" onclick="closeModal('editProductModal')">
                        Cancel
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Stock Adjustment Modal -->
    <div id="stockModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3><i class="fas fa-boxes"></i> Adjust Stock</h3>
                <button type="button" class="close" onclick="closeModal('stockModal')">&times;</button>
            </div>
            <form class="ajax-form" action="inventory.php" method="POST">
                <input type="hidden" name="action" value="adjust_stock">
                <input type="hidden" name="ajax" value="1">
                <input type="hidden" name="product_id" id="stock_product_id">
                
                <div class="form-group">
                    <label class="form-label">Product</label>
                    <input type="text" id="stock_product_name" class="form-control" readonly>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Current Stock</label>
                    <input type="number" id="current_stock" class="form-control" readonly>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Quantity Change *</label>
                    <input type="number" name="quantity_change" class="form-control" required placeholder="Enter positive to add, negative to remove">
                    <small class="text-gray">Use positive numbers to add stock, negative to remove</small>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Reason *</label>
                    <select name="reason" class="form-control" required>
                        <option value="">Select Reason</option>
                        <option value="Stock adjustment">Stock adjustment</option>
                        <option value="Damaged goods">Damaged goods</option>
                        <option value="Lost items">Lost items</option>
                        <option value="Return from customer">Return from customer</option>
                        <option value="New delivery">New delivery</option>
                        <option value="Inventory count correction">Inventory count correction</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Adjust Stock
                    </button>
                    <button type="button" class="btn btn-secondary" onclick="closeModal('stockModal')">
                        Cancel
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script src="../assets/js/main.js"></script>
    <script>
        // Search and filter functionality
        document.getElementById('searchInput').addEventListener('input', debounce(function() {
            const search = this.value;
            const filter = document.getElementById('filterSelect').value;
            window.location.href = `inventory.php?search=${encodeURIComponent(search)}&filter=${filter}`;
        }, 500));

        document.getElementById('filterSelect').addEventListener('change', function() {
            const filter = this.value;
            const search = document.getElementById('searchInput').value;
            window.location.href = `inventory.php?search=${encodeURIComponent(search)}&filter=${filter}`;
        });

        // Stock adjustment modal
        function openStockModal(productId, productName, currentStock) {
            document.getElementById('stock_product_id').value = productId;
            document.getElementById('stock_product_name').value = productName;
            document.getElementById('current_stock').value = currentStock;
            openModal('stockModal');
        }

        // Edit product modal
        function editProduct(product) {
            document.getElementById('edit_product_id').value = product.id;
            
            const formFields = `
                <div class="row">
                    <div class="col-6">
                        <div class="form-group">
                            <label class="form-label">Product Name *</label>
                            <input type="text" name="name" class="form-control" value="${product.name}" required>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="form-group">
                            <label class="form-label">Brand *</label>
                            <input type="text" name="brand" class="form-control" value="${product.brand}" required>
                        </div>
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-6">
                        <div class="form-group">
                            <label class="form-label">Model</label>
                            <input type="text" name="model" class="form-control" value="${product.model || ''}">
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="form-group">
                            <label class="form-label">Category *</label>
                            <input type="text" name="category" class="form-control" value="${product.category}" required>
                        </div>
                    </div>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Description</label>
                    <textarea name="description" class="form-control" rows="3">${product.description || ''}</textarea>
                </div>
                
                <div class="row">
                    <div class="col-4">
                        <div class="form-group">
                            <label class="form-label">Selling Price *</label>
                            <input type="number" name="price" class="form-control" step="0.01" min="0" value="${product.price}" required>
                        </div>
                    </div>
                    <div class="col-4">
                        <div class="form-group">
                            <label class="form-label">Cost Price *</label>
                            <input type="number" name="cost_price" class="form-control" step="0.01" min="0" value="${product.cost_price}" required>
                        </div>
                    </div>
                    <div class="col-4">
                        <div class="form-group">
                            <label class="form-label">Warranty (months)</label>
                            <input type="number" name="warranty_months" class="form-control" min="0" value="${product.warranty_months}">
                        </div>
                    </div>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Minimum Stock Level *</label>
                    <input type="number" name="min_stock_level" class="form-control" min="1" value="${product.min_stock_level}" required>
                </div>
            `;
            
            document.getElementById('editFormFields').innerHTML = formFields;
            openModal('editProductModal');
        }

        // Delete product
        function deleteProduct(productId, productName) {
            if (confirm(`Are you sure you want to delete "${productName}"? This action cannot be undone.`)) {
                const formData = new FormData();
                formData.append('action', 'delete_product');
                formData.append('product_id', productId);
                formData.append('ajax', '1');
                
                fetch('inventory.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        showNotification(data.message, 'success');
                        setTimeout(() => location.reload(), 1500);
                    } else {
                        showNotification(data.message, 'error');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    showNotification('An error occurred while deleting the product.', 'error');
                });
            }
        }

        // View product details
        function viewProduct(productId) {
            // This would open a detailed view modal
            showNotification('Product details view coming soon!', 'info');
        }

        // Export inventory
        function exportInventory() {
            showNotification('Export functionality coming soon!', 'info');
        }

        // Auto-refresh low stock notifications
        setInterval(() => {
            fetch('inventory.php?ajax=1&check_low_stock=1')
                .then(response => response.json())
                .then(data => {
                    if (data.low_stock_count > 0) {
                        showNotification(`${data.low_stock_count} items are running low on stock!`, 'warning');
                    }
                })
                .catch(error => console.error('Error checking stock levels:', error));
        }, 300000); // Check every 5 minutes
    </script>

    <style>
        .btn-group {
            display: flex;
            gap: 0.25rem;
        }
        
        .badge {
            padding: 0.25rem 0.5rem;
            border-radius: 0.25rem;
            font-size: 0.75rem;
            font-weight: bold;
            text-transform: uppercase;
        }
        .badge-success { background-color: var(--success); color: white; }
        .badge-warning { background-color: var(--warning); color: var(--navy); }
        .badge-danger { background-color: var(--danger); color: white; }
        .badge-info { background-color: var(--info); color: white; }
        
        .pagination {
            display: flex;
            gap: 0.25rem;
        }
        
        .table-responsive {
            overflow-x: auto;
        }
        
        .text-danger { color: var(--danger) !important; }
        .text-gray { color: var(--gray) !important; }
    </style>
</body>
</html>