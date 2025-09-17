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
            case 'record_sale':
                $result = recordSale($db, $_POST);
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

// Get sales with filtering
$filter = $_GET['filter'] ?? 'all';
$search = $_GET['search'] ?? '';
$page = (int)($_GET['page'] ?? 1);
$limit = RECORDS_PER_PAGE;
$offset = ($page - 1) * $limit;

try {
    // Get sales
    $query = "
        SELECT s.*, 
               CONCAT(c.first_name, ' ', c.last_name) as customer_name,
               CONCAT(staff.first_name, ' ', staff.last_name) as staff_name,
               GROUP_CONCAT(CONCAT(si.quantity, 'x ', p.name) SEPARATOR ', ') as items
        FROM sales s
        JOIN users c ON s.customer_id = c.id
        JOIN users staff ON s.staff_id = staff.id
        JOIN sale_items si ON s.id = si.sale_id
        JOIN products p ON si.product_id = p.id
        GROUP BY s.id
        ORDER BY s.sale_date DESC
        LIMIT :limit OFFSET :offset
    ";
    
    $stmt = $db->prepare($query);
    $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    $stmt->execute();
    $sales = $stmt->fetchAll();

    // Get customers for dropdown
    $stmt = $db->prepare("SELECT id, CONCAT(first_name, ' ', last_name) as name FROM users WHERE role_id = 3 AND is_active = 1 ORDER BY first_name");
    $stmt->execute();
    $customers = $stmt->fetchAll();

    // Get products for sale
    $stmt = $db->prepare("SELECT id, name, price, stock_quantity FROM products WHERE is_active = 1 AND stock_quantity > 0 ORDER BY name");
    $stmt->execute();
    $products = $stmt->fetchAll();

} catch (PDOException $e) {
    $error_message = "Database error: " . $e->getMessage();
    $sales = [];
    $customers = [];
    $products = [];
}

function recordSale($db, $data) {
    try {
        $db->beginTransaction();
        
        // Insert sale
        $query = "INSERT INTO sales (customer_id, staff_id, total_amount, payment_type, payment_status, discount, tax_amount, notes) 
                 VALUES (:customer_id, :staff_id, :total_amount, :payment_type, :payment_status, :discount, :tax_amount, :notes)";
        
        $stmt = $db->prepare($query);
        $stmt->bindParam(':customer_id', $data['customer_id']);
        $stmt->bindParam(':staff_id', $_SESSION['user_id']);
        $stmt->bindParam(':total_amount', $data['total_amount']);
        $stmt->bindParam(':payment_type', $data['payment_type']);
        $stmt->bindParam(':payment_status', $data['payment_status']);
        $stmt->bindParam(':discount', $data['discount'] ?? 0);
        $stmt->bindParam(':tax_amount', $data['tax_amount'] ?? 0);
        $stmt->bindParam(':notes', $data['notes'] ?? '');
        $stmt->execute();
        
        $sale_id = $db->lastInsertId();
        
        // Insert sale items and update inventory
        $items = json_decode($data['items'], true);
        foreach ($items as $item) {
            // Insert sale item
            $query = "INSERT INTO sale_items (sale_id, product_id, quantity, unit_price, total_price) 
                     VALUES (:sale_id, :product_id, :quantity, :unit_price, :total_price)";
            
            $stmt = $db->prepare($query);
            $stmt->bindParam(':sale_id', $sale_id);
            $stmt->bindParam(':product_id', $item['product_id']);
            $stmt->bindParam(':quantity', $item['quantity']);
            $stmt->bindParam(':unit_price', $item['unit_price']);
            $stmt->bindParam(':total_price', $item['total_price']);
            $stmt->execute();
            
            // Update product stock
            $query = "UPDATE products SET stock_quantity = stock_quantity - :quantity WHERE id = :id";
            $stmt = $db->prepare($query);
            $stmt->bindParam(':quantity', $item['quantity']);
            $stmt->bindParam(':id', $item['product_id']);
            $stmt->execute();
        }
        
        // Create installments if payment type is installment
        if ($data['payment_type'] === 'installment' && isset($data['installments'])) {
            $installments = json_decode($data['installments'], true);
            foreach ($installments as $installment) {
                $query = "INSERT INTO installments (sale_id, installment_number, due_date, amount) 
                         VALUES (:sale_id, :installment_number, :due_date, :amount)";
                
                $stmt = $db->prepare($query);
                $stmt->bindParam(':sale_id', $sale_id);
                $stmt->bindParam(':installment_number', $installment['number']);
                $stmt->bindParam(':due_date', $installment['due_date']);
                $stmt->bindParam(':amount', $installment['amount']);
                $stmt->execute();
            }
        }
        
        $db->commit();
        
        return [
            'success' => true,
            'message' => 'Sale recorded successfully',
            'sale_id' => $sale_id
        ];
        
    } catch (PDOException $e) {
        $db->rollBack();
        return ['success' => false, 'message' => 'Database error: ' . $e->getMessage()];
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sales Management - <?php echo APP_NAME; ?></title>
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
            <li><a href="inventory.php"><i class="fas fa-boxes"></i> Inventory</a></li>
            <li><a href="sales.php" class="active"><i class="fas fa-shopping-cart"></i> Sales</a></li>
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
                    <h1><i class="fas fa-shopping-cart"></i> Sales Management</h1>
                    <p>Record sales, process transactions, and manage customer purchases</p>
                </div>
                <div>
                    <button class="btn btn-primary" onclick="openModal('newSaleModal')">
                        <i class="fas fa-plus"></i> New Sale
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

        <!-- Sales Table -->
        <div class="card">
            <div class="card-header">
                <div style="display: flex; justify-content: space-between; align-items: center;">
                    <h3>Recent Sales</h3>
                    <div class="btn-group">
                        <button class="btn btn-secondary btn-sm" onclick="exportSales()">
                            <i class="fas fa-download"></i> Export
                        </button>
                        <button class="btn btn-info btn-sm" onclick="location.reload()">
                            <i class="fas fa-sync-alt"></i> Refresh
                        </button>
                    </div>
                </div>
            </div>

            <?php if (empty($sales)): ?>
                <div class="text-center p-5">
                    <i class="fas fa-shopping-cart" style="font-size: 4rem; color: var(--gray); opacity: 0.5;"></i>
                    <h4 class="mt-3">No Sales Found</h4>
                    <p>Start by recording your first sale.</p>
                    <button class="btn btn-primary" onclick="openModal('newSaleModal')">
                        <i class="fas fa-plus"></i> Record First Sale
                    </button>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table data-table">
                        <thead>
                            <tr>
                                <th>Sale ID</th>
                                <th>Customer</th>
                                <th>Items</th>
                                <th>Amount</th>
                                <th>Payment</th>
                                <th>Status</th>
                                <th>Date</th>
                                <th>Staff</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($sales as $sale): ?>
                            <tr>
                                <td><strong>#<?php echo str_pad($sale['id'], 6, '0', STR_PAD_LEFT); ?></strong></td>
                                <td><?php echo htmlspecialchars($sale['customer_name']); ?></td>
                                <td>
                                    <div style="max-width: 200px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;" title="<?php echo htmlspecialchars($sale['items']); ?>">
                                        <?php echo htmlspecialchars($sale['items']); ?>
                                    </div>
                                </td>
                                <td><strong><?php echo format_currency($sale['total_amount']); ?></strong></td>
                                <td>
                                    <span class="badge <?php echo $sale['payment_type'] === 'full' ? 'badge-success' : 'badge-warning'; ?>">
                                        <?php echo ucfirst($sale['payment_type']); ?>
                                    </span>
                                </td>
                                <td>
                                    <span class="badge badge-<?php 
                                        echo $sale['payment_status'] === 'paid' ? 'success' : 
                                            ($sale['payment_status'] === 'partial' ? 'warning' : 'danger'); 
                                    ?>">
                                        <?php echo ucfirst($sale['payment_status']); ?>
                                    </span>
                                </td>
                                <td><?php echo format_date($sale['sale_date']); ?></td>
                                <td><?php echo htmlspecialchars($sale['staff_name']); ?></td>
                                <td>
                                    <div class="btn-group">
                                        <button class="btn btn-sm" onclick="viewSale(<?php echo $sale['id']; ?>)" title="View Details">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                        <button class="btn btn-success btn-sm" onclick="printReceipt(<?php echo $sale['id']; ?>)" title="Print Receipt">
                                            <i class="fas fa-print"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </main>

    <!-- New Sale Modal -->
    <div id="newSaleModal" class="modal">
        <div class="modal-content" style="max-width: 800px;">
            <div class="modal-header">
                <h3><i class="fas fa-plus"></i> Record New Sale</h3>
                <button type="button" class="close" onclick="closeModal('newSaleModal')">&times;</button>
            </div>
            
            <form class="ajax-form" action="sales.php" method="POST" id="newSaleForm">
                <input type="hidden" name="action" value="record_sale">
                <input type="hidden" name="ajax" value="1">
                <input type="hidden" name="items" id="sale_items">
                <input type="hidden" name="installments" id="sale_installments">
                
                <!-- Customer Selection -->
                <div class="form-group">
                    <label class="form-label">Customer *</label>
                    <select name="customer_id" class="form-control" required>
                        <option value="">Select Customer</option>
                        <?php foreach ($customers as $customer): ?>
                            <option value="<?php echo $customer['id']; ?>"><?php echo htmlspecialchars($customer['name']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Products Selection -->
                <div class="form-group">
                    <label class="form-label">Products</label>
                    <div id="product-selection">
                        <div class="product-row" style="display: flex; gap: 1rem; margin-bottom: 1rem; align-items: end;">
                            <div style="flex: 1;">
                                <select class="form-control product-select" onchange="updateProductPrice(this)">
                                    <option value="">Select Product</option>
                                    <?php foreach ($products as $product): ?>
                                        <option value="<?php echo $product['id']; ?>" data-price="<?php echo $product['price']; ?>" data-stock="<?php echo $product['stock_quantity']; ?>">
                                            <?php echo htmlspecialchars($product['name']); ?> - <?php echo format_currency($product['price']); ?> (Stock: <?php echo $product['stock_quantity']; ?>)
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div style="width: 100px;">
                                <input type="number" class="form-control quantity-input" placeholder="Qty" min="1" onchange="calculateTotal()">
                            </div>
                            <div style="width: 120px;">
                                <input type="number" class="form-control price-input" placeholder="Price" step="0.01" readonly>
                            </div>
                            <div style="width: 120px;">
                                <input type="number" class="form-control total-input" placeholder="Total" step="0.01" readonly>
                            </div>
                            <button type="button" class="btn btn-success btn-sm" onclick="addProductRow()">
                                <i class="fas fa-plus"></i>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Sale Summary -->
                <div class="row">
                    <div class="col-6">
                        <div class="form-group">
                            <label class="form-label">Subtotal</label>
                            <input type="number" id="subtotal" class="form-control" step="0.01" readonly>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="form-group">
                            <label class="form-label">Discount</label>
                            <input type="number" name="discount" class="form-control" step="0.01" min="0" value="0" onchange="calculateTotal()">
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-6">
                        <div class="form-group">
                            <label class="form-label">Tax</label>
                            <input type="number" name="tax_amount" class="form-control" step="0.01" min="0" value="0" onchange="calculateTotal()">
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="form-group">
                            <label class="form-label">Total Amount *</label>
                            <input type="number" name="total_amount" id="total_amount" class="form-control" step="0.01" readonly required>
                        </div>
                    </div>
                </div>

                <!-- Payment Type -->
                <div class="form-group">
                    <label class="form-label">Payment Type *</label>
                    <select name="payment_type" class="form-control" required onchange="toggleInstallmentSection(this.value)">
                        <option value="">Select Payment Type</option>
                        <option value="full">Full Payment</option>
                        <option value="installment">Installment Plan</option>
                    </select>
                </div>

                <!-- Installment Section -->
                <div id="installment-section" style="display: none;">
                    <div class="form-group">
                        <label class="form-label">Number of Installments</label>
                        <select id="installment-count" class="form-control" onchange="generateInstallmentSchedule()">
                            <option value="">Select</option>
                            <option value="3">3 months</option>
                            <option value="6">6 months</option>
                            <option value="12">12 months</option>
                            <option value="24">24 months</option>
                        </select>
                    </div>
                    <div id="installment-schedule"></div>
                </div>

                <!-- Payment Status -->
                <div class="form-group">
                    <label class="form-label">Payment Status *</label>
                    <select name="payment_status" class="form-control" required>
                        <option value="pending">Pending</option>
                        <option value="paid">Paid</option>
                        <option value="partial">Partial</option>
                    </select>
                </div>

                <!-- Notes -->
                <div class="form-group">
                    <label class="form-label">Notes</label>
                    <textarea name="notes" class="form-control" rows="3" placeholder="Additional notes about this sale"></textarea>
                </div>

                <div class="form-group">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Record Sale
                    </button>
                    <button type="button" class="btn btn-secondary" onclick="closeModal('newSaleModal')">
                        Cancel
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script src="../assets/js/main.js"></script>
    <script>
        let productRowCount = 1;

        function addProductRow() {
            const productSelection = document.getElementById('product-selection');
            const newRow = productSelection.querySelector('.product-row').cloneNode(true);
            
            // Clear values in new row
            newRow.querySelectorAll('input, select').forEach(input => {
                input.value = '';
            });
            
            // Add remove button
            const addButton = newRow.querySelector('.btn-success');
            addButton.className = 'btn btn-danger btn-sm';
            addButton.innerHTML = '<i class="fas fa-minus"></i>';
            addButton.onclick = function() { removeProductRow(this); };
            
            productSelection.appendChild(newRow);
            productRowCount++;
        }

        function removeProductRow(button) {
            button.closest('.product-row').remove();
            calculateTotal();
        }

        function updateProductPrice(select) {
            const row = select.closest('.product-row');
            const priceInput = row.querySelector('.price-input');
            const quantityInput = row.querySelector('.quantity-input');
            
            if (select.value) {
                const option = select.querySelector(`option[value="${select.value}"]`);
                const price = option.dataset.price;
                priceInput.value = price;
                
                if (quantityInput.value) {
                    calculateRowTotal(row);
                }
            } else {
                priceInput.value = '';
                row.querySelector('.total-input').value = '';
            }
            calculateTotal();
        }

        function calculateRowTotal(row) {
            const quantity = parseFloat(row.querySelector('.quantity-input').value) || 0;
            const price = parseFloat(row.querySelector('.price-input').value) || 0;
            const total = quantity * price;
            row.querySelector('.total-input').value = total.toFixed(2);
            calculateTotal();
        }

        function calculateTotal() {
            let subtotal = 0;
            
            // Calculate subtotal from all product rows
            document.querySelectorAll('.product-row').forEach(row => {
                const quantity = parseFloat(row.querySelector('.quantity-input').value) || 0;
                const price = parseFloat(row.querySelector('.price-input').value) || 0;
                const total = quantity * price;
                row.querySelector('.total-input').value = total.toFixed(2);
                subtotal += total;
            });
            
            // Update subtotal
            document.getElementById('subtotal').value = subtotal.toFixed(2);
            
            // Calculate final total
            const discount = parseFloat(document.querySelector('input[name="discount"]').value) || 0;
            const tax = parseFloat(document.querySelector('input[name="tax_amount"]').value) || 0;
            const finalTotal = subtotal - discount + tax;
            
            document.getElementById('total_amount').value = finalTotal.toFixed(2);
        }

        function toggleInstallmentSection(paymentType) {
            const section = document.getElementById('installment-section');
            section.style.display = paymentType === 'installment' ? 'block' : 'none';
        }

        function generateInstallmentSchedule() {
            const count = parseInt(document.getElementById('installment-count').value);
            const totalAmount = parseFloat(document.getElementById('total_amount').value);
            
            if (!count || !totalAmount) return;
            
            const monthlyAmount = (totalAmount / count).toFixed(2);
            const scheduleDiv = document.getElementById('installment-schedule');
            
            let scheduleHTML = '<h4>Payment Schedule</h4><div class="table-responsive"><table class="table"><thead><tr><th>#</th><th>Due Date</th><th>Amount</th></tr></thead><tbody>';
            
            for (let i = 1; i <= count; i++) {
                const dueDate = new Date();
                dueDate.setMonth(dueDate.getMonth() + i);
                const dueDateStr = dueDate.toISOString().split('T')[0];
                
                scheduleHTML += `<tr><td>${i}</td><td>${dueDateStr}</td><td>$${monthlyAmount}</td></tr>`;
            }
            
            scheduleHTML += '</tbody></table></div>';
            scheduleDiv.innerHTML = scheduleHTML;
        }

        // Add event listeners to quantity and price inputs
        document.addEventListener('change', function(e) {
            if (e.target.classList.contains('quantity-input')) {
                calculateRowTotal(e.target.closest('.product-row'));
            }
        });

        // Prepare form data before submission
        document.getElementById('newSaleForm').addEventListener('submit', function(e) {
            // Collect product items
            const items = [];
            document.querySelectorAll('.product-row').forEach(row => {
                const productId = row.querySelector('.product-select').value;
                const quantity = row.querySelector('.quantity-input').value;
                const price = row.querySelector('.price-input').value;
                const total = row.querySelector('.total-input').value;
                
                if (productId && quantity && price) {
                    items.push({
                        product_id: productId,
                        quantity: parseInt(quantity),
                        unit_price: parseFloat(price),
                        total_price: parseFloat(total)
                    });
                }
            });
            
            document.getElementById('sale_items').value = JSON.stringify(items);
            
            // Collect installment data if applicable
            const paymentType = document.querySelector('select[name="payment_type"]').value;
            if (paymentType === 'installment') {
                const installmentCount = document.getElementById('installment-count').value;
                const totalAmount = document.getElementById('total_amount').value;
                
                if (installmentCount && totalAmount) {
                    const installments = [];
                    const monthlyAmount = parseFloat(totalAmount) / parseInt(installmentCount);
                    
                    for (let i = 1; i <= installmentCount; i++) {
                        const dueDate = new Date();
                        dueDate.setMonth(dueDate.getMonth() + i);
                        
                        installments.push({
                            number: i,
                            due_date: dueDate.toISOString().split('T')[0],
                            amount: monthlyAmount.toFixed(2)
                        });
                    }
                    
                    document.getElementById('sale_installments').value = JSON.stringify(installments);
                }
            }
        });

        function viewSale(saleId) {
            showNotification('Sale details view coming soon!', 'info');
        }

        function printReceipt(saleId) {
            showNotification('Receipt printing coming soon!', 'info');
        }

        function exportSales() {
            showNotification('Sales export coming soon!', 'info');
        }
    </script>

    <style>
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

        .btn-group {
            display: flex;
            gap: 0.25rem;
        }

        .product-row {
            border: 1px solid #eee;
            padding: 1rem;
            border-radius: 8px;
            margin-bottom: 1rem;
        }
    </style>
</body>
</html>