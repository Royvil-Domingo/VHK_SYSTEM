<?php
/* ============================================================
   EXPENSES PAGE
   - Form to record a new expense
   - Displays full expense history
   ============================================================ */

/* ----- Security & Database ----- */
require_once '../includes/security.php';
require_once '../config/db.php';

/* ----- Redirect if not logged in ----- */
require_login();

/* ----- Initialize Message & Today's Date ----- */
$msg = '';
$today = date('Y-m-d');

/* ----- Handle Form Submission ----- */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    /* Verify CSRF token before processing */
    if (!verify_csrf($_POST['csrf_token'] ?? null)) {
        $msg = '<div class="exp-alert exp-alert-error">Invalid security token. Please refresh and try again.</div>';
    } else {

        /* ----- Sanitize & Validate Inputs ----- */
        $description = trim($_POST['description'] ?? '');
        $amount = filter_var($_POST['amount'] ?? null, FILTER_VALIDATE_FLOAT);
        $expense_date = $_POST['expense_date'] ?? $today;

        if ($description === '' || mb_strlen($description) > 255) {
            $msg = '<div class="exp-alert exp-alert-error">Description is required and must be 255 characters or less.</div>';
        } elseif ($amount === false || $amount <= 0) {
            $msg = '<div class="exp-alert exp-alert-error">Amount must be a valid number greater than zero.</div>';
        } elseif (!valid_date($expense_date)) {
            $msg = '<div class="exp-alert exp-alert-error">Please enter a valid expense date.</div>';
        } else {
            /* ----- Insert Expense into Database ----- */
            $pdo->prepare('INSERT INTO expenses (description, amount, expense_date) VALUES (?,?,?)')->execute([$description, $amount, $expense_date]);
            $msg = '<div class="exp-alert exp-alert-success">Expense recorded successfully.</div>';
        }
    }
}

/* ----- Fetch Full Expense History ----- */
$expenses = $pdo->query('SELECT * FROM expenses ORDER BY created_at DESC')->fetchAll();

/* ----- Load Site Header ----- */
require_once '../includes/header.php';
?>

<main class="exp-body">
    <div class="exp-wrapper">

        <!-- ===== PAGE HEADER ===== -->
        <div class="exp-header-section">
            <div>
                <h1 class="exp-title">Expenses</h1>
                <p class="exp-subtitle">Record operating costs and view the full expense history.</p>
            </div>
        </div>

        <!-- ===== FLASH MESSAGE ===== -->
        <?= $msg ?>

        <!-- ===== RECORD NEW EXPENSE FORM ===== -->
        <div class="exp-card">
            <div class="exp-card-header">Record New Expense</div>
            <form method="POST" action="" class="vhk-form">

                <!-- CSRF token for security -->
                <input type="hidden" name="csrf_token" value="<?= h(csrf_token()) ?>">

                <!-- ----- Description & Amount (Side by Side) ----- -->
                <div class="exp-two-col">
                    <div class="form-group">
                        <label>Description</label>
                        <input type="text" name="description" placeholder="e.g. Delivery fee, supplies, transport" maxlength="255" required>
                    </div>
                    <div class="form-group">
                        <label>Amount (₱)</label>
                        <input type="number" name="amount" step="0.01" min="0.01" placeholder="0.00" required>
                    </div>
                </div>

                <!-- ----- Expense Date ----- -->
                <div class="form-group exp-date-group">
                    <label>Date</label>
                    <input type="date" name="expense_date" value="<?= h($today) ?>" required>
                </div>

                <button type="submit" class="save-btn">Save Expense</button>
            </form>
        </div>

        <!-- ===== FULL EXPENSE HISTORY TABLE ===== -->
        <div class="exp-card">
            <div class="exp-card-header">Full Expense History</div>
            <div class="exp-table-wrap">
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
                            <!-- ----- Empty State ----- -->
                            <tr><td colspan="3" class="table-empty">No expenses yet.</td></tr>
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
</main>

<!-- ===== FOOTER ===== -->
<?php require_once '../includes/footer.php'; ?>