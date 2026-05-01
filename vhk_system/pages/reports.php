<?php
/* ============================================================
   REPORTS PAGE
   - Filter sales and expenses by date range
   - Displays summary cards and detailed tables
   ============================================================ */

/* ----- Security & Database ----- */
require_once '../includes/security.php';
require_once '../config/db.php';

/* ----- Redirect if not logged in ----- */
require_login();

/* ----- Date Range Setup ----- */
/* Default: from start of current month to today */
$today = date('Y-m-d');
$from = $_GET['from'] ?? date('Y-m-01');
$to = $_GET['to'] ?? $today;

/* ----- Validate Date Inputs ----- */
if (!valid_date($from)) {
    $from = date('Y-m-01');
}
if (!valid_date($to)) {
    $to = $today;
}

/* Swap dates if from is later than to */
if ($from > $to) {
    [$from, $to] = [$to, $from];
}

/* ----- Fetch Total Sales in Range ----- */
$stmt = $pdo->prepare('SELECT COALESCE(SUM(total),0) FROM sales WHERE sale_date BETWEEN ? AND ?');
$stmt->execute([$from, $to]);
$total_sales = (float)$stmt->fetchColumn();

/* ----- Fetch Total Expenses in Range ----- */
$stmt = $pdo->prepare('SELECT COALESCE(SUM(amount),0) FROM expenses WHERE expense_date BETWEEN ? AND ?');
$stmt->execute([$from, $to]);
$total_expenses = (float)$stmt->fetchColumn();

/* Net cash flow = sales - expenses */
$net = $total_sales - $total_expenses;

/* ----- Fetch All Sales Records in Range ----- */
$stmt = $pdo->prepare('SELECT s.sale_date, p.name AS product_name, s.quantity, s.unit_price, s.total, s.payment_method FROM sales s LEFT JOIN products p ON s.product_id = p.id WHERE s.sale_date BETWEEN ? AND ? ORDER BY s.sale_date DESC, s.created_at DESC');
$stmt->execute([$from, $to]);
$sales = $stmt->fetchAll();

/* ----- Fetch All Expense Records in Range ----- */
$stmt = $pdo->prepare('SELECT expense_date, description, amount FROM expenses WHERE expense_date BETWEEN ? AND ? ORDER BY expense_date DESC, created_at DESC');
$stmt->execute([$from, $to]);
$expenses = $stmt->fetchAll();

/* ----- Load Site Header ----- */
require_once '../includes/header.php';
?>

<main class="report-body">
    <div class="report-wrapper">

        <!-- ===== PAGE HEADER ===== -->
        <div class="report-header-section">
            <div>
                <h1 class="report-title">Reports</h1>
                <p class="report-subtitle">Filter by date, summarize performance, and print the report.</p>
            </div>
        </div>

        <!-- ===== DATE RANGE FILTER FORM ===== -->
        <div class="report-card">
            <div class="report-card-header">Filter by Date Range</div>
            <form method="GET" action="" class="vhk-form">
                <div class="report-filter-row">
                    <div class="form-group">
                        <label>From</label>
                        <input type="date" name="from" value="<?= h($from) ?>">
                    </div>
                    <div class="form-group">
                        <label>To</label>
                        <input type="date" name="to" value="<?= h($to) ?>">
                    </div>
                    <div class="report-filter-actions">
                        <button type="submit" class="save-btn">Generate</button>
                        <!-- Print button triggers browser print dialog -->
                        <button type="button" class="cancel-btn" onclick="window.print()">Print</button>
                    </div>
                </div>
            </form>
        </div>

        <!-- ===== SUMMARY CARDS ===== -->
        <!-- Shows total sales, expenses, net cash flow, and selected date range -->
        <div class="report-summary-grid">
            <div class="report-summary-card">
                <div class="report-summary-label">Total Sales</div>
                <div class="report-summary-value green">₱<?= number_format($total_sales, 2) ?></div>
            </div>
            <div class="report-summary-card">
                <div class="report-summary-label">Total Expenses</div>
                <div class="report-summary-value red">₱<?= number_format($total_expenses, 2) ?></div>
            </div>
            <div class="report-summary-card">
                <div class="report-summary-label">Net Cash Flow</div>
                <!-- Green if positive, red if negative -->
                <div class="report-summary-value <?= $net >= 0 ? 'green' : 'red' ?>">₱<?= number_format($net, 2) ?></div>
            </div>
            <div class="report-summary-card">
                <div class="report-summary-label">Date Range</div>
                <div class="report-summary-value report-range"><?= h($from) ?> — <?= h($to) ?></div>
            </div>
        </div>

        <!-- ===== TWO COLUMN SECTION ===== -->
        <!-- Sales in Range | Expenses in Range -->
        <div class="report-two-col">

            <!-- ----- Sales Table ----- -->
            <div class="report-card">
                <div class="report-card-header">Sales in Range</div>
                <div class="report-table-wrap">
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
                                <tr><td colspan="6" class="table-empty">No sales in this range.</td></tr>
                            <?php else: ?>
                                <?php foreach ($sales as $sale): ?>
                                    <tr>
                                        <td><?= h(date('m/d/Y', strtotime($sale['sale_date']))) ?></td>
                                        <td><?= h($sale['product_name'] ?? 'N/A') ?></td>
                                        <td><?= (int)$sale['quantity'] ?></td>
                                        <td>₱<?= number_format((float)$sale['unit_price'], 2) ?></td>
                                        <td>₱<?= number_format((float)$sale['total'], 2) ?></td>
                                        <td>
                                            <!-- Badge color varies by payment method -->
                                            <span class="report-badge report-badge-<?= strtolower(h($sale['payment_method'])) ?>">
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

            <!-- ----- Expenses Table ----- -->
            <div class="report-card">
                <div class="report-card-header">Expenses in Range</div>
                <div class="report-table-wrap">
                    <table>
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Description</th>
                                <th>Amount</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($expenses)): ?>
                                <tr><td colspan="3" class="table-empty">No expenses in this range.</td></tr>
                            <?php else: ?>
                                <?php foreach ($expenses as $expense): ?>
                                    <tr>
                                        <td><?= h(date('m/d/Y', strtotime($expense['expense_date']))) ?></td>
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

    </div>
</main>

<!-- ===== FOOTER ===== -->
<?php require_once '../includes/footer.php'; ?>