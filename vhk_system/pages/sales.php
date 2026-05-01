<?php
/* ============================================================
   SALES PAGE
   - Handles recording of new sales
   - Validates product selection, quantity, payment method, date
   - Deducts stock on successful sale
   - Displays full sales history
   ============================================================ */

require_once '../includes/security.php';
require_once '../config/db.php';

require_login();

$msg = '';
$today = date('Y-m-d');
$allowed_methods = ['Cash', 'GCash'];

/* ----- Handle Sale Form Submission ----- */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    /* Validate CSRF token */
    if (!verify_csrf($_POST['csrf_token'] ?? null)) {
        $msg = '<div class="sale-alert sale-alert-error">Invalid security token. Please refresh and try again.</div>';
    } else {
        /* Sanitize and validate inputs */
        $product_id = filter_var($_POST['product_id'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
        $quantity = filter_var($_POST['quantity'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
        $payment_method = $_POST['payment_method'] ?? 'Cash';
        $sale_date = $_POST['sale_date'] ?? $today;

        if (!$product_id || !$quantity) {
            $msg = '<div class="sale-alert sale-alert-error">Please select a product and enter a valid quantity.</div>';
        } elseif (!in_array($payment_method, $allowed_methods, true)) {
            $msg = '<div class="sale-alert sale-alert-error">Invalid payment method selected.</div>';
        } elseif (!valid_date($sale_date)) {
            $msg = '<div class="sale-alert sale-alert-error">Please enter a valid sale date.</div>';
        } else {
            try {
                /* Start transaction to ensure stock and sale are saved together */
                $pdo->beginTransaction();

                /* Lock product row to prevent race conditions */
                $stmt = $pdo->prepare('SELECT id, price, stock FROM products WHERE id = ? FOR UPDATE');
                $stmt->execute([$product_id]);
                $product = $stmt->fetch();

                if (!$product) {
                    $pdo->rollBack();
                    $msg = '<div class="sale-alert sale-alert-error">Selected product does not exist.</div>';
                } elseif ((int)$product['stock'] < $quantity) {
                    /* Not enough stock */
                    $pdo->rollBack();
                    $msg = '<div class="sale-alert sale-alert-error">Insufficient stock. Available: ' . (int)$product['stock'] . '</div>';
                } else {
                    /* Calculate total and insert sale record */
                    $unit_price = (float)$product['price'];
                    $total = $unit_price * $quantity;
                    $pdo->prepare('INSERT INTO sales (product_id, quantity, unit_price, total, payment_method, sale_date) VALUES (?,?,?,?,?,?)')
                        ->execute([$product_id, $quantity, $unit_price, $total, $payment_method, $sale_date]);

                    /* Deduct stock from product */
                    $pdo->prepare('UPDATE products SET stock = stock - ? WHERE id = ?')->execute([$quantity, $product_id]);
                    $pdo->commit();
                    $msg = '<div class="sale-alert sale-alert-success">Sale recorded successfully.</div>';
                }
            } catch (Throwable $e) {
                /* Rollback on any error */
                if ($pdo->inTransaction()) {
                    $pdo->rollBack();
                }
                $msg = '<div class="sale-alert sale-alert-error">' . $e->getMessage() . '</div>';
            }
        }
    }
}

/* ----- Fetch Products for Dropdown (only in stock) ----- */
$products_list = $pdo->query('SELECT id, name, price, stock FROM products WHERE stock > 0 ORDER BY name')->fetchAll();

/* ----- Fetch Full Sales History ----- */
$sales = $pdo->query('SELECT s.sale_date, s.quantity, s.unit_price, s.total, s.payment_method, p.name AS product_name FROM sales s LEFT JOIN products p ON s.product_id = p.id ORDER BY s.created_at DESC')->fetchAll();

require_once '../includes/header.php';
?>

<main class="sale-body">
    <div class="sale-wrapper">

        <!-- ===== PAGE HEADER ===== -->
        <div class="sale-header-section">
            <div>
                <h1 class="sale-title">Sales</h1>
                <p class="sale-subtitle">Record a new sale and review the full sales history.</p>
            </div>
        </div>

        <!-- ===== ALERT MESSAGE ===== -->
        <?= $msg ?>

        <!-- ===== RECORD NEW SALE FORM ===== -->
        <div class="sale-card">
            <div class="sale-card-header">Record New Sale</div>
            <form method="POST" action="" class="vhk-form">

                <!-- CSRF Token (security) -->
                <input type="hidden" name="csrf_token" value="<?= h(csrf_token()) ?>">

                <div class="sale-two-col">
                    <div>
                        <!-- Product Dropdown with Select2 search -->
                        <div class="form-group">
                            <label>Product</label>
                            <select name="product_id" id="sale_product" onchange="updateSaleTotal()" required>
                                <option value="">— select product —</option>
                                <?php foreach ($products_list as $p): ?>
                                    <option value="<?= (int)$p['id'] ?>" data-price="<?= h($p['price']) ?>" data-stock="<?= (int)$p['stock'] ?>">
                                        <?= h($p['name']) ?> (Stock: <?= (int)$p['stock'] ?>)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <!-- Quantity Input -->
                        <div class="form-group">
                            <label>Quantity</label>
                            <input type="number" name="quantity" id="sale_qty" min="1" value="1" required oninput="updateSaleTotal()">
                        </div>
                    </div>
                    <div>
                        <!-- Payment Method -->
                        <div class="form-group">
                            <label>Payment Method</label>
                            <select name="payment_method" required>
                                <option value="Cash">Cash</option>
                                <option value="GCash">GCash</option>
                            </select>
                        </div>

                        <!-- Sale Date -->
                        <div class="form-group">
                            <label>Date</label>
                            <input type="date" name="sale_date" value="<?= h($today) ?>" required>
                        </div>

                        <!-- Auto-calculated Total Display -->
                        <div class="sale-total-display">Total: ₱<span id="sale_total">0.00</span></div>
                    </div>
                </div>

                <!-- Submit Button -->
                <button type="submit" class="save-btn">Save Sale</button>
            </form>
        </div>

        <!-- ===== FULL SALES HISTORY TABLE ===== -->
        <div class="sale-card">
            <div class="sale-card-header">Full Sales History</div>
            <div class="sale-table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Product</th>
                            <th>Qty</th>
                            <th>Unit Price</th>
                            <th>Total</th>
                            <th>Method</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($sales)): ?>
                            <tr><td colspan="6" class="table-empty">No sales yet.</td></tr>
                        <?php else: ?>
                            <?php foreach ($sales as $sale): ?>
                                <tr>
                                    <td><?= h(date('m/d/Y', strtotime($sale['sale_date']))) ?></td>
                                    <td><?= h($sale['product_name'] ?? 'N/A') ?></td>
                                    <td><?= (int)$sale['quantity'] ?></td>
                                    <td>₱<?= number_format((float)$sale['unit_price'], 2) ?></td>
                                    <td>₱<?= number_format((float)$sale['total'], 2) ?></td>
                                    <td>
                                        <!-- Payment Method Badge -->
                                        <span class="sale-badge sale-badge-<?= strtolower(h($sale['payment_method'])) ?>">
                                            <?= h($sale['payment_method']) ?>
                                        </span>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

    </div>

    <!-- ===== TOTAL CALCULATOR SCRIPT ===== -->
    <!-- Updates the total display when product or quantity changes -->
    <script>
        function updateSaleTotal() {
            const product = document.getElementById('sale_product');
            const qty = parseInt(document.getElementById('sale_qty').value || '0', 10);
            const totalEl = document.getElementById('sale_total');
            const price = product && product.selectedIndex > 0
                ? parseFloat(product.options[product.selectedIndex].dataset.price || '0')
                : 0;
            totalEl.textContent = (price * qty).toFixed(2);
        }
        updateSaleTotal();
    </script>

    <!-- ===== SELECT2 SEARCHABLE DROPDOWN SCRIPT ===== -->
    <!-- Enhances product dropdown with search functionality -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.1.0-rc.0/js/select2.min.js"></script>
    <script>
        $('#sale_product').select2({
            placeholder: '— select product —',
            width: '100%'
        });

        $('#sale_product').on('change', function() {
            updateSaleTotal();
        });
    </script>
</main>

<!-- ===== FOOTER ===== -->
<?php require_once '../includes/footer.php'; ?>