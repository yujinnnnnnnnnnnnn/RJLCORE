<?php
/**
 * Inventory Management for Appliances Management System
 */

require_once '../config/config.php';

// Check if user is logged in and has admin/staff role
if (!isLoggedIn() || !hasAnyRole(['admin', 'staff'])) {
    redirectTo('../login.php');
}

$user = getCurrentUser();
$action = $_GET['action'] ?? 'list';
$product_id = $_GET['id'] ?? null;

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($action === 'add' || $action === 'edit') {
        $result = handleProductSave();
        if ($result['success']) {
            flashMessage('success', $result['message']);
            redirectTo('inventory.php');
        } else {
            flashMessage('error', $result['message']);
        }
    } elseif ($action === 'delete' && $product_id) {
        $result = handleProductDelete($product_id);
        if ($result['success']) {
            flashMessage('success', $result['message']);
        } else {
            flashMessage('error', $result['message']);
        }
        redirectTo('inventory.php');
    }
}

// Get categories for dropdowns
$categories = fetchAll("SELECT * FROM categories ORDER BY category_name");

// Handle different actions
switch ($action) {
    case 'add':
        $page_title = 'Add Product';
        $page_subtitle = 'Add a new product to inventory';
        $product = null;
        break;
        
    case 'edit':
        if (!$product_id) {
            redirectTo('inventory.php');
        }
        $product = fetchOne("SELECT * FROM products WHERE product_id = ?", [$product_id]);
        if (!$product) {
            flashMessage('error', 'Product not found.');
            redirectTo('inventory.php');
        }
        $page_title = 'Edit Product';
        $page_subtitle = 'Edit product information';
        break;
        
    case 'view':
        if (!$product_id) {
            redirectTo('inventory.php');
        }
        $product = fetchOne("
            SELECT p.*, c.category_name 
            FROM products p 
            LEFT JOIN categories c ON p.category_id = c.category_id 
            WHERE p.product_id = ?
        ", [$product_id]);
        if (!$product) {
            flashMessage('error', 'Product not found.');
            redirectTo('inventory.php');
        }
        $page_title = 'Product Details';
        $page_subtitle = $product['product_name'];
        break;
        
    default:
        $page_title = 'Inventory Management';
        $page_subtitle = 'Manage your product inventory';
        
        // Get filter parameters
        $search = $_GET['search'] ?? '';
        $category_filter = $_GET['category'] ?? '';
        $status_filter = $_GET['status'] ?? '';
        $stock_filter = $_GET['filter'] ?? '';
        
        // Build query
        $where_conditions = [];
        $params = [];
        
        if ($search) {
            $where_conditions[] = "(p.product_name LIKE ? OR p.brand LIKE ? OR p.model LIKE ?)";
            $params[] = "%$search%";
            $params[] = "%$search%";
            $params[] = "%$search%";
        }
        
        if ($category_filter) {
            $where_conditions[] = "p.category_id = ?";
            $params[] = $category_filter;
        }
        
        if ($status_filter) {
            $where_conditions[] = "p.status = ?";
            $params[] = $status_filter;
        }
        
        if ($stock_filter === 'low_stock') {
            $where_conditions[] = "p.stock_quantity <= p.min_stock_level";
        } elseif ($stock_filter === 'out_of_stock') {
            $where_conditions[] = "p.stock_quantity = 0";
        }
        
        $where_clause = '';
        if (!empty($where_conditions)) {
            $where_clause = 'WHERE ' . implode(' AND ', $where_conditions);
        }
        
        $products = fetchAll("
            SELECT p.*, c.category_name 
            FROM products p 
            LEFT JOIN categories c ON p.category_id = c.category_id 
            $where_clause
            ORDER BY p.created_at DESC
        ", $params);
        
        break;
}

// Page actions for list view
if ($action === 'list' || $action === '') {
    $page_actions = '<a href="inventory.php?action=add" class="btn btn-primary"><i class="fas fa-plus"></i> Add Product</a>';
}

// Functions
function handleProductSave() {
    global $product_id;
    
    $data = [
        'category_id' => $_POST['category_id'] ?? null,
        'product_name' => sanitizeInput($_POST['product_name'] ?? ''),
        'brand' => sanitizeInput($_POST['brand'] ?? ''),
        'model' => sanitizeInput($_POST['model'] ?? ''),
        'description' => sanitizeInput($_POST['description'] ?? ''),
        'price' => floatval($_POST['price'] ?? 0),
        'cost_price' => floatval($_POST['cost_price'] ?? 0),
        'stock_quantity' => intval($_POST['stock_quantity'] ?? 0),
        'min_stock_level' => intval($_POST['min_stock_level'] ?? 5),
        'warranty_period' => intval($_POST['warranty_period'] ?? 12),
        'status' => $_POST['status'] ?? 'active',
    ];
    
    // Validation
    if (empty($data['product_name']) || empty($data['brand']) || $data['price'] <= 0) {
        return ['success' => false, 'message' => 'Please fill in all required fields with valid values.'];
    }
    
    // Handle specifications
    $specifications = [];
    if (!empty($_POST['spec_keys']) && !empty($_POST['spec_values'])) {
        $keys = $_POST['spec_keys'];
        $values = $_POST['spec_values'];
        for ($i = 0; $i < count($keys); $i++) {
            if (!empty($keys[$i]) && !empty($values[$i])) {
                $specifications[sanitizeInput($keys[$i])] = sanitizeInput($values[$i]);
            }
        }
    }
    $data['specifications'] = json_encode($specifications);
    
    try {
        if ($product_id) {
            // Update existing product
            $sql = "UPDATE products SET 
                    category_id = ?, product_name = ?, brand = ?, model = ?, description = ?,
                    price = ?, cost_price = ?, stock_quantity = ?, min_stock_level = ?,
                    warranty_period = ?, specifications = ?, status = ?, updated_at = CURRENT_TIMESTAMP
                    WHERE product_id = ?";
            
            $params = [
                $data['category_id'], $data['product_name'], $data['brand'], $data['model'],
                $data['description'], $data['price'], $data['cost_price'], $data['stock_quantity'],
                $data['min_stock_level'], $data['warranty_period'], $data['specifications'],
                $data['status'], $product_id
            ];
            
            $result = executeQuery($sql, $params);
            if ($result) {
                logAudit('UPDATE_PRODUCT', 'products', $product_id, null, $data);
                return ['success' => true, 'message' => 'Product updated successfully.'];
            }
        } else {
            // Insert new product
            $sql = "INSERT INTO products (category_id, product_name, brand, model, description, price, cost_price, stock_quantity, min_stock_level, warranty_period, specifications, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
            
            $params = [
                $data['category_id'], $data['product_name'], $data['brand'], $data['model'],
                $data['description'], $data['price'], $data['cost_price'], $data['stock_quantity'],
                $data['min_stock_level'], $data['warranty_period'], $data['specifications'],
                $data['status']
            ];
            
            $result = executeQuery($sql, $params);
            if ($result) {
                $new_id = getLastInsertId();
                logAudit('CREATE_PRODUCT', 'products', $new_id, null, $data);
                return ['success' => true, 'message' => 'Product added successfully.'];
            }
        }
        
        return ['success' => false, 'message' => 'Failed to save product. Please try again.'];
        
    } catch (Exception $e) {
        error_log("Product save error: " . $e->getMessage());
        return ['success' => false, 'message' => 'An error occurred while saving the product.'];
    }
}

function handleProductDelete($product_id) {
    try {
        // Check if product has sales
        $sales_count = fetchOne("SELECT COUNT(*) as count FROM sale_items WHERE product_id = ?", [$product_id])['count'];
        
        if ($sales_count > 0) {
            // Don't delete, just mark as discontinued
            $result = executeQuery("UPDATE products SET status = 'discontinued' WHERE product_id = ?", [$product_id]);
            if ($result) {
                logAudit('DISCONTINUE_PRODUCT', 'products', $product_id);
                return ['success' => true, 'message' => 'Product marked as discontinued (has sales history).'];
            }
        } else {
            // Safe to delete
            $result = executeQuery("DELETE FROM products WHERE product_id = ?", [$product_id]);
            if ($result) {
                logAudit('DELETE_PRODUCT', 'products', $product_id);
                return ['success' => true, 'message' => 'Product deleted successfully.'];
            }
        }
        
        return ['success' => false, 'message' => 'Failed to delete product.'];
        
    } catch (Exception $e) {
        error_log("Product delete error: " . $e->getMessage());
        return ['success' => false, 'message' => 'An error occurred while deleting the product.'];
    }
}

include 'includes/header.php';
?>

<?php if ($action === 'list' || $action === ''): ?>
    <!-- Inventory List View -->
    <div class="inventory-container">
        <!-- Filters -->
        <div class="filters-card">
            <form method="GET" class="filters-form">
                <div class="filter-group">
                    <input type="text" name="search" value="<?php echo htmlspecialchars($search); ?>" 
                           placeholder="Search products..." class="form-control">
                </div>
                <div class="filter-group">
                    <select name="category" class="form-control">
                        <option value="">All Categories</option>
                        <?php foreach ($categories as $category): ?>
                            <option value="<?php echo $category['category_id']; ?>" 
                                    <?php echo $category_filter == $category['category_id'] ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($category['category_name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="filter-group">
                    <select name="status" class="form-control">
                        <option value="">All Status</option>
                        <option value="active" <?php echo $status_filter === 'active' ? 'selected' : ''; ?>>Active</option>
                        <option value="discontinued" <?php echo $status_filter === 'discontinued' ? 'selected' : ''; ?>>Discontinued</option>
                        <option value="out_of_stock" <?php echo $status_filter === 'out_of_stock' ? 'selected' : ''; ?>>Out of Stock</option>
                    </select>
                </div>
                <div class="filter-group">
                    <select name="filter" class="form-control">
                        <option value="">Stock Level</option>
                        <option value="low_stock" <?php echo $stock_filter === 'low_stock' ? 'selected' : ''; ?>>Low Stock</option>
                        <option value="out_of_stock" <?php echo $stock_filter === 'out_of_stock' ? 'selected' : ''; ?>>Out of Stock</option>
                    </select>
                </div>
                <div class="filter-actions">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-search"></i> Filter
                    </button>
                    <a href="inventory.php" class="btn btn-outline">
                        <i class="fas fa-times"></i> Clear
                    </a>
                </div>
            </form>
        </div>

        <!-- Products Table -->
        <div class="table-card">
            <div class="table-responsive">
                <table class="table data-table">
                    <thead>
                        <tr>
                            <th data-sort>Product</th>
                            <th data-sort>Category</th>
                            <th data-sort>Price</th>
                            <th data-sort>Stock</th>
                            <th data-sort>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($products)): ?>
                            <tr>
                                <td colspan="6" class="text-center">
                                    <div class="empty-state">
                                        <i class="fas fa-box-open"></i>
                                        <p>No products found</p>
                                        <a href="inventory.php?action=add" class="btn btn-primary">Add First Product</a>
                                    </div>
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($products as $product): ?>
                                <tr>
                                    <td>
                                        <div class="product-info">
                                            <strong><?php echo htmlspecialchars($product['product_name']); ?></strong>
                                            <small><?php echo htmlspecialchars($product['brand'] . ' ' . $product['model']); ?></small>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="category-badge">
                                            <?php echo htmlspecialchars($product['category_name'] ?? 'Uncategorized'); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <div class="price-info">
                                            <strong><?php echo formatCurrency($product['price']); ?></strong>
                                            <?php if ($product['cost_price'] > 0): ?>
                                                <small>Cost: <?php echo formatCurrency($product['cost_price']); ?></small>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="stock-info">
                                            <span class="stock-number <?php 
                                                echo $product['stock_quantity'] == 0 ? 'out-of-stock' : 
                                                    ($product['stock_quantity'] <= $product['min_stock_level'] ? 'low-stock' : 'in-stock'); 
                                            ?>">
                                                <?php echo $product['stock_quantity']; ?>
                                            </span>
                                            <small>Min: <?php echo $product['min_stock_level']; ?></small>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge badge-<?php 
                                            echo $product['status'] === 'active' ? 'success' : 
                                                ($product['status'] === 'discontinued' ? 'warning' : 'secondary'); 
                                        ?>">
                                            <?php echo ucfirst($product['status']); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <div class="action-buttons">
                                            <a href="inventory.php?action=view&id=<?php echo $product['product_id']; ?>" 
                                               class="btn btn-sm btn-outline" data-tooltip="View Details">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <a href="inventory.php?action=edit&id=<?php echo $product['product_id']; ?>" 
                                               class="btn btn-sm btn-primary" data-tooltip="Edit Product">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <a href="inventory.php?action=delete&id=<?php echo $product['product_id']; ?>" 
                                               class="btn btn-sm btn-danger" 
                                               data-confirm="Are you sure you want to delete this product?"
                                               data-tooltip="Delete Product">
                                                <i class="fas fa-trash"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

<?php elseif ($action === 'view'): ?>
    <!-- Product View -->
    <div class="product-view-container">
        <div class="product-details-card">
            <div class="product-header">
                <div class="product-main-info">
                    <h2><?php echo htmlspecialchars($product['product_name']); ?></h2>
                    <p class="product-brand"><?php echo htmlspecialchars($product['brand'] . ' ' . $product['model']); ?></p>
                    <span class="badge badge-<?php 
                        echo $product['status'] === 'active' ? 'success' : 
                            ($product['status'] === 'discontinued' ? 'warning' : 'secondary'); 
                    ?>">
                        <?php echo ucfirst($product['status']); ?>
                    </span>
                </div>
                <div class="product-actions">
                    <a href="inventory.php?action=edit&id=<?php echo $product['product_id']; ?>" class="btn btn-primary">
                        <i class="fas fa-edit"></i> Edit Product
                    </a>
                    <a href="inventory.php" class="btn btn-outline">
                        <i class="fas fa-arrow-left"></i> Back to List
                    </a>
                </div>
            </div>

            <div class="product-details-grid">
                <div class="detail-section">
                    <h4>Basic Information</h4>
                    <div class="detail-grid">
                        <div class="detail-item">
                            <label>Category</label>
                            <span><?php echo htmlspecialchars($product['category_name'] ?? 'Uncategorized'); ?></span>
                        </div>
                        <div class="detail-item">
                            <label>Brand</label>
                            <span><?php echo htmlspecialchars($product['brand']); ?></span>
                        </div>
                        <div class="detail-item">
                            <label>Model</label>
                            <span><?php echo htmlspecialchars($product['model']); ?></span>
                        </div>
                        <div class="detail-item">
                            <label>Warranty Period</label>
                            <span><?php echo $product['warranty_period']; ?> months</span>
                        </div>
                    </div>
                </div>

                <div class="detail-section">
                    <h4>Pricing & Stock</h4>
                    <div class="detail-grid">
                        <div class="detail-item">
                            <label>Selling Price</label>
                            <span class="price"><?php echo formatCurrency($product['price']); ?></span>
                        </div>
                        <div class="detail-item">
                            <label>Cost Price</label>
                            <span><?php echo formatCurrency($product['cost_price']); ?></span>
                        </div>
                        <div class="detail-item">
                            <label>Stock Quantity</label>
                            <span class="stock-number <?php 
                                echo $product['stock_quantity'] == 0 ? 'out-of-stock' : 
                                    ($product['stock_quantity'] <= $product['min_stock_level'] ? 'low-stock' : 'in-stock'); 
                            ?>">
                                <?php echo $product['stock_quantity']; ?>
                            </span>
                        </div>
                        <div class="detail-item">
                            <label>Minimum Stock Level</label>
                            <span><?php echo $product['min_stock_level']; ?></span>
                        </div>
                    </div>
                </div>

                <?php if ($product['description']): ?>
                <div class="detail-section">
                    <h4>Description</h4>
                    <p><?php echo nl2br(htmlspecialchars($product['description'])); ?></p>
                </div>
                <?php endif; ?>

                <?php 
                $specifications = json_decode($product['specifications'], true);
                if ($specifications && !empty($specifications)): 
                ?>
                <div class="detail-section">
                    <h4>Specifications</h4>
                    <div class="specifications-list">
                        <?php foreach ($specifications as $key => $value): ?>
                            <div class="spec-item">
                                <label><?php echo htmlspecialchars($key); ?></label>
                                <span><?php echo htmlspecialchars($value); ?></span>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endif; ?>

                <div class="detail-section">
                    <h4>System Information</h4>
                    <div class="detail-grid">
                        <div class="detail-item">
                            <label>Created</label>
                            <span><?php echo formatDateTime($product['created_at']); ?></span>
                        </div>
                        <div class="detail-item">
                            <label>Last Updated</label>
                            <span><?php echo formatDateTime($product['updated_at']); ?></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

<?php else: ?>
    <!-- Add/Edit Product Form -->
    <div class="product-form-container">
        <div class="form-card">
            <form method="POST" class="product-form needs-validation" novalidate>
                <div class="form-sections">
                    <!-- Basic Information -->
                    <div class="form-section">
                        <h4>Basic Information</h4>
                        <div class="form-grid">
                            <div class="form-group">
                                <label for="product_name" class="form-label">Product Name *</label>
                                <input type="text" id="product_name" name="product_name" class="form-control" 
                                       value="<?php echo htmlspecialchars($product['product_name'] ?? ''); ?>" required>
                                <div class="invalid-feedback">Please provide a product name.</div>
                            </div>

                            <div class="form-group">
                                <label for="category_id" class="form-label">Category</label>
                                <select id="category_id" name="category_id" class="form-control">
                                    <option value="">Select Category</option>
                                    <?php foreach ($categories as $category): ?>
                                        <option value="<?php echo $category['category_id']; ?>" 
                                                <?php echo ($product['category_id'] ?? '') == $category['category_id'] ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($category['category_name']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="form-group">
                                <label for="brand" class="form-label">Brand *</label>
                                <input type="text" id="brand" name="brand" class="form-control" 
                                       value="<?php echo htmlspecialchars($product['brand'] ?? ''); ?>" required>
                                <div class="invalid-feedback">Please provide a brand name.</div>
                            </div>

                            <div class="form-group">
                                <label for="model" class="form-label">Model</label>
                                <input type="text" id="model" name="model" class="form-control" 
                                       value="<?php echo htmlspecialchars($product['model'] ?? ''); ?>">
                            </div>

                            <div class="form-group col-span-2">
                                <label for="description" class="form-label">Description</label>
                                <textarea id="description" name="description" class="form-control" rows="3"><?php echo htmlspecialchars($product['description'] ?? ''); ?></textarea>
                            </div>
                        </div>
                    </div>

                    <!-- Pricing & Stock -->
                    <div class="form-section">
                        <h4>Pricing & Stock</h4>
                        <div class="form-grid">
                            <div class="form-group">
                                <label for="price" class="form-label">Selling Price *</label>
                                <div class="input-group">
                                    <span class="input-group-text">$</span>
                                    <input type="number" id="price" name="price" class="form-control" step="0.01" min="0"
                                           value="<?php echo $product['price'] ?? ''; ?>" required>
                                </div>
                                <div class="invalid-feedback">Please provide a valid price.</div>
                            </div>

                            <div class="form-group">
                                <label for="cost_price" class="form-label">Cost Price</label>
                                <div class="input-group">
                                    <span class="input-group-text">$</span>
                                    <input type="number" id="cost_price" name="cost_price" class="form-control" step="0.01" min="0"
                                           value="<?php echo $product['cost_price'] ?? ''; ?>">
                                </div>
                            </div>

                            <div class="form-group">
                                <label for="stock_quantity" class="form-label">Stock Quantity *</label>
                                <input type="number" id="stock_quantity" name="stock_quantity" class="form-control" min="0"
                                       value="<?php echo $product['stock_quantity'] ?? '0'; ?>" required>
                                <div class="invalid-feedback">Please provide stock quantity.</div>
                            </div>

                            <div class="form-group">
                                <label for="min_stock_level" class="form-label">Minimum Stock Level</label>
                                <input type="number" id="min_stock_level" name="min_stock_level" class="form-control" min="0"
                                       value="<?php echo $product['min_stock_level'] ?? '5'; ?>">
                            </div>

                            <div class="form-group">
                                <label for="warranty_period" class="form-label">Warranty (Months)</label>
                                <input type="number" id="warranty_period" name="warranty_period" class="form-control" min="0"
                                       value="<?php echo $product['warranty_period'] ?? '12'; ?>">
                            </div>

                            <div class="form-group">
                                <label for="status" class="form-label">Status</label>
                                <select id="status" name="status" class="form-control">
                                    <option value="active" <?php echo ($product['status'] ?? 'active') === 'active' ? 'selected' : ''; ?>>Active</option>
                                    <option value="discontinued" <?php echo ($product['status'] ?? '') === 'discontinued' ? 'selected' : ''; ?>>Discontinued</option>
                                    <option value="out_of_stock" <?php echo ($product['status'] ?? '') === 'out_of_stock' ? 'selected' : ''; ?>>Out of Stock</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <!-- Specifications -->
                    <div class="form-section">
                        <h4>Specifications</h4>
                        <div class="specifications-container">
                            <div id="specifications-list">
                                <?php 
                                $specifications = json_decode($product['specifications'] ?? '{}', true) ?: [];
                                if (empty($specifications)) {
                                    $specifications = [''] = ''; // Add one empty row
                                }
                                foreach ($specifications as $key => $value): 
                                ?>
                                    <div class="spec-row">
                                        <input type="text" name="spec_keys[]" class="form-control" 
                                               placeholder="Specification name" value="<?php echo htmlspecialchars($key); ?>">
                                        <input type="text" name="spec_values[]" class="form-control" 
                                               placeholder="Value" value="<?php echo htmlspecialchars($value); ?>">
                                        <button type="button" class="btn btn-danger btn-sm remove-spec">
                                            <i class="fas fa-times"></i>
                                        </button>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                            <button type="button" id="add-specification" class="btn btn-outline btn-sm">
                                <i class="fas fa-plus"></i> Add Specification
                            </button>
                        </div>
                    </div>
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i>
                        <?php echo $action === 'edit' ? 'Update Product' : 'Add Product'; ?>
                    </button>
                    <a href="inventory.php" class="btn btn-secondary">
                        <i class="fas fa-times"></i> Cancel
                    </a>
                </div>
            </form>
        </div>
    </div>
<?php endif; ?>

<style>
/* Inventory Specific Styles */
.inventory-container {
    padding: 2rem;
}

.filters-card {
    background: var(--white);
    border-radius: 10px;
    padding: 1.5rem;
    margin-bottom: 2rem;
    box-shadow: var(--shadow-sm);
}

.filters-form {
    display: grid;
    grid-template-columns: 2fr 1fr 1fr 1fr auto;
    gap: 1rem;
    align-items: end;
}

.filter-group {
    display: flex;
    flex-direction: column;
}

.filter-actions {
    display: flex;
    gap: 0.5rem;
}

.table-card {
    background: var(--white);
    border-radius: 10px;
    box-shadow: var(--shadow-sm);
    overflow: hidden;
}

.product-info strong {
    display: block;
    color: var(--navy-blue);
    margin-bottom: 0.25rem;
}

.product-info small {
    color: var(--medium-gray);
    font-size: 0.85rem;
}

.category-badge {
    background: var(--secondary-beige);
    color: var(--navy-blue);
    padding: 0.25rem 0.75rem;
    border-radius: 15px;
    font-size: 0.8rem;
    font-weight: 500;
}

.price-info strong {
    display: block;
    color: var(--navy-blue);
    font-size: 1.1rem;
    margin-bottom: 0.25rem;
}

.price-info small {
    color: var(--medium-gray);
    font-size: 0.8rem;
}

.stock-info {
    text-align: center;
}

.stock-number {
    display: block;
    font-size: 1.2rem;
    font-weight: bold;
    padding: 0.25rem 0.5rem;
    border-radius: 4px;
    margin-bottom: 0.25rem;
}

.stock-number.in-stock {
    background: var(--success);
    color: var(--white);
}

.stock-number.low-stock {
    background: var(--warning);
    color: var(--white);
}

.stock-number.out-of-stock {
    background: var(--danger);
    color: var(--white);
}

.action-buttons {
    display: flex;
    gap: 0.25rem;
    justify-content: center;
}

.empty-state {
    padding: 3rem;
    text-align: center;
    color: var(--medium-gray);
}

.empty-state i {
    font-size: 4rem;
    margin-bottom: 1rem;
    opacity: 0.5;
}

/* Product View Styles */
.product-view-container {
    padding: 2rem;
}

.product-details-card {
    background: var(--white);
    border-radius: 10px;
    box-shadow: var(--shadow-sm);
    overflow: hidden;
}

.product-header {
    padding: 2rem;
    border-bottom: 1px solid #e9ecef;
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    background: var(--secondary-beige);
}

.product-main-info h2 {
    color: var(--navy-blue);
    margin-bottom: 0.5rem;
}

.product-brand {
    color: var(--medium-gray);
    margin-bottom: 1rem;
}

.product-actions {
    display: flex;
    gap: 1rem;
}

.product-details-grid {
    padding: 2rem;
    display: flex;
    flex-direction: column;
    gap: 2rem;
}

.detail-section h4 {
    color: var(--navy-blue);
    margin-bottom: 1rem;
    padding-bottom: 0.5rem;
    border-bottom: 2px solid var(--primary-green);
}

.detail-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 1.5rem;
}

.detail-item {
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
}

.detail-item label {
    font-weight: 600;
    color: var(--medium-gray);
    font-size: 0.9rem;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.detail-item span {
    color: var(--navy-blue);
    font-size: 1.1rem;
}

.detail-item .price {
    font-size: 1.3rem;
    font-weight: bold;
    color: var(--success);
}

.specifications-list {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 1rem;
}

.spec-item {
    background: var(--light-gray);
    padding: 1rem;
    border-radius: 8px;
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
}

.spec-item label {
    font-weight: 600;
    color: var(--navy-blue);
    font-size: 0.9rem;
}

.spec-item span {
    color: var(--dark-gray);
}

/* Product Form Styles */
.product-form-container {
    padding: 2rem;
}

.form-card {
    background: var(--white);
    border-radius: 10px;
    box-shadow: var(--shadow-sm);
    overflow: hidden;
}

.product-form {
    padding: 2rem;
}

.form-sections {
    display: flex;
    flex-direction: column;
    gap: 2rem;
    margin-bottom: 2rem;
}

.form-section h4 {
    color: var(--navy-blue);
    margin-bottom: 1.5rem;
    padding-bottom: 0.5rem;
    border-bottom: 2px solid var(--primary-green);
}

.form-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 1.5rem;
}

.form-grid .col-span-2 {
    grid-column: span 2;
}

.input-group {
    display: flex;
}

.input-group-text {
    background: var(--secondary-beige);
    color: var(--navy-blue);
    padding: 0.75rem;
    border: 2px solid #e9ecef;
    border-right: none;
    border-radius: 5px 0 0 5px;
    font-weight: 600;
}

.input-group .form-control {
    border-radius: 0 5px 5px 0;
    border-left: none;
}

.specifications-container {
    border: 2px dashed #e9ecef;
    border-radius: 8px;
    padding: 1.5rem;
}

.spec-row {
    display: grid;
    grid-template-columns: 1fr 1fr auto;
    gap: 1rem;
    margin-bottom: 1rem;
    align-items: center;
}

.remove-spec {
    width: 40px;
    height: 40px;
    display: flex;
    align-items: center;
    justify-content: center;
}

.form-actions {
    display: flex;
    gap: 1rem;
    justify-content: flex-end;
    padding-top: 2rem;
    border-top: 1px solid #e9ecef;
}

/* Responsive Design */
@media (max-width: 768px) {
    .filters-form {
        grid-template-columns: 1fr;
        gap: 1rem;
    }
    
    .filter-actions {
        justify-content: stretch;
    }
    
    .filter-actions .btn {
        flex: 1;
    }
    
    .product-header {
        flex-direction: column;
        gap: 1rem;
        align-items: stretch;
    }
    
    .product-actions {
        justify-content: stretch;
    }
    
    .detail-grid {
        grid-template-columns: 1fr;
    }
    
    .form-grid {
        grid-template-columns: 1fr;
    }
    
    .form-grid .col-span-2 {
        grid-column: span 1;
    }
    
    .spec-row {
        grid-template-columns: 1fr;
        gap: 0.5rem;
    }
    
    .form-actions {
        flex-direction: column;
    }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Add specification functionality
    const addSpecBtn = document.getElementById('add-specification');
    const specsList = document.getElementById('specifications-list');
    
    if (addSpecBtn && specsList) {
        addSpecBtn.addEventListener('click', function() {
            const specRow = document.createElement('div');
            specRow.className = 'spec-row';
            specRow.innerHTML = `
                <input type="text" name="spec_keys[]" class="form-control" placeholder="Specification name">
                <input type="text" name="spec_values[]" class="form-control" placeholder="Value">
                <button type="button" class="btn btn-danger btn-sm remove-spec">
                    <i class="fas fa-times"></i>
                </button>
            `;
            specsList.appendChild(specRow);
        });
        
        // Remove specification functionality
        specsList.addEventListener('click', function(e) {
            if (e.target.closest('.remove-spec')) {
                e.target.closest('.spec-row').remove();
            }
        });
    }
    
    // Auto-calculate profit margin
    const priceInput = document.getElementById('price');
    const costInput = document.getElementById('cost_price');
    
    if (priceInput && costInput) {
        function updateProfitMargin() {
            const price = parseFloat(priceInput.value) || 0;
            const cost = parseFloat(costInput.value) || 0;
            
            if (price > 0 && cost > 0) {
                const margin = ((price - cost) / price * 100).toFixed(1);
                // You could display this somewhere if needed
                console.log('Profit margin:', margin + '%');
            }
        }
        
        priceInput.addEventListener('input', updateProfitMargin);
        costInput.addEventListener('input', updateProfitMargin);
    }
});
</script>

<?php include 'includes/footer.php'; ?>