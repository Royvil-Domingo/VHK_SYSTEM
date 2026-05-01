<?php
/* ============================================================
   DASHBOARD PAGE
   - Shows business performance overview for today
   - Displays summary cards, quick actions, and recent records
   ============================================================ */

/* ----- Security & Database ----- */
require_once '../includes/security.php';
require_once '../config/db.php';

/* ----- Redirect if not logged in ----- */
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

/* ----- Data Logic ----- */

/* Total number of products in inventory */
$total_products = $pdo->query('SELECT COUNT(*) FROM products')->fetchColumn();

/* Total sales amount for today */
$sales_today = (float)$pdo->query('SELECT COALESCE(SUM(total), 0) FROM sales WHERE sale_date = CURDATE()')->fetchColumn();

/* Total expenses amount for today */
$expenses_today = (float)$pdo->query('SELECT COALESCE(SUM(amount), 0) FROM expenses WHERE expense_date = CURDATE()')->fetchColumn();

/* Net cash flow = sales - expenses */
$net_cash_flow = $sales_today - $expenses_today;

/* Products with stock at or below reorder level */
$low_stock = $pdo->query('
    SELECT p.*, c.name as category_name 
    FROM products p 
    LEFT JOIN categories c ON p.category_id = c.id 
    WHERE p.stock <= p.reorder_level 
    ORDER BY p.stock ASC
')->fetchAll();

/* 5 most recent sales */
$recent_sales = $pdo->query('
    SELECT s.*, p.name as product_name 
    FROM sales s 
    LEFT JOIN products p ON s.product_id = p.id 
    ORDER BY s.created_at DESC 
    LIMIT 5
')->fetchAll();

/* 5 most recent expenses */
$recent_expenses = $pdo->query('
    SELECT * FROM expenses 
    ORDER BY created_at DESC 
    LIMIT 5
')->fetchAll();

require_once '../includes/header.php';
?>

<main class="dash-body">
    <div class="dash-wrapper">
        
        <!-- ===== PAGE HEADER ===== -->
        <div class="dash-header-section">
            <div class="dash-title-group">
                <h1 class="dash-title">Dashboard</h1>
                <p class="dash-subtitle">Read-only overview of your business performance today.</p>
            </div>
        </div>

        <!-- ===== SUMMARY CARDS ===== -->
        <!-- Displays total products, sales today, expenses today, and net cash flow -->
        <div class="dash-cards-grid">
            <div class="dash-summary-card">
                <img src="../resources/images/product_icon.png" alt="Products" class="dash-card-icon">
                <div>
                    <div class="dash-label">Total Products</div>
                    <div class="dash-value"><?= $total_products ?></div>
                </div>
            </div>
            <div class="dash-summary-card">
                <img src="../resources/images/sales_icon.png" alt="Sales" class="dash-card-icon">
                <div>
                    <div class="dash-label">Sales Today</div>
                    <div class="dash-value green">₱<?= number_format($sales_today, 2) ?></div>
                </div>
            </div>
            <div class="dash-summary-card">
                <img src="../resources/images/expenses_icon.png" alt="Expenses" class="dash-card-icon">
                <div>
                    <div class="dash-label">Expenses Today</div>
                    <div class="dash-value red">₱<?= number_format($expenses_today, 2) ?></div>
                </div>
            </div>
            <div class="dash-summary-card">
                <img src="../resources/images/cash-flow_icon.png" alt="Cash Flow" class="dash-card-icon">
                <div>
                    <div class="dash-label">Net Cash Flow Today</div>
                    <!-- Green if positive, red if negative -->
                    <div class="dash-value <?= $net_cash_flow >= 0 ? 'green' : 'red' ?>">
                        ₱<?= number_format($net_cash_flow, 2) ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- ===== QUICK ACTION BUTTONS ===== -->
        <!-- Shortcuts to Record Sale, Add Expense, and View Reports -->
        <div class="dash-action-grid">
            <a href="sales.php" class="dash-action-card">Record Sale</a>
            <a href="expenses.php" class="dash-action-card">Add Expense</a>
            <a href="reports.php" class="dash-action-card">View Reports</a>
        </div>

        <!-- ===== TWO COLUMN SECTION ===== -->
        <!-- Low Stock Alerts | Recent Sales -->
        <div class="dash-two-col">

            <!-- Low Stock Alerts Table -->
            <div class="dash-section-card">
                <div class="dash-section-header">Low Stock Alerts</div>
                <div class="dash-table-wrap">
                    <table>
                        <thead>
                            <tr><th>Product</th><th>Category</th><th>Stock</th><th>Reorder</th></tr>
                        </thead>
                        <tbody>
                            <?php if (empty($low_stock)): ?>
                                <tr><td colspan="4" class="table-empty">No low stock items.</td></tr>
                            <?php else: ?>
                                <?php foreach ($low_stock as $item): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($item['name']) ?></td>
                                        <td><?= h($item['category_name'] ?? '—') ?></td>
                                        <td><?= (int)$item['stock'] ?></td>
                                        <td><?= (int)$item['reorder_level'] ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Recent Sales Table -->
            <div class="dash-section-card">
                <div class="dash-section-header">Recent Sales</div>
                <div class="dash-table-wrap">
                    <table>
                        <thead>
                            <tr><th>Date</th><th>Product</th><th>Qty</th><th>Total</th></tr>
                        </thead>
                        <tbody>
                            <?php if (empty($recent_sales)): ?>
                                <tr><td colspan="4" class="table-empty">No recent sales.</td></tr>
                            <?php else: ?>
                                <?php foreach ($recent_sales as $sale): ?>
                                    <tr>
                                        <td><?= date('m/d/Y', strtotime($sale['sale_date'])) ?></td>
                                        <td><?= h($sale['product_name'] ?? 'N/A') ?></td>
                                        <td><?= (int)$sale['quantity'] ?></td>
                                        <td>₱<?= number_format((float)$sale['total'], 2) ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- ===== RECENT EXPENSES TABLE ===== -->
        <div class="dash-section-card">
            <div class="dash-section-header">Recent Expenses</div>
            <div class="dash-table-wrap">
                <table>
                    <thead>
                        <tr><th>Date</th><th>Description</th><th>Amount</th></tr>
                    </thead>
                    <tbody>
                        <?php if (empty($recent_expenses)): ?>
                            <tr><td colspan="3" class="table-empty">No recent expenses.</td></tr>
                        <?php else: ?>
                            <?php foreach ($recent_expenses as $expense): ?>
                                <tr>
                                    <td><?= date('m/d/Y', strtotime($expense['expense_date'])) ?></td>
                                    <td><?= h($expense['description']) ?></td>
                                    <td>₱<?= number_format((float)$expense['amount'], 2) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

    </div>
</main>

<!-- ===== FOOTER ===== -->
<?php require_once '../includes/footer.php'; ?>