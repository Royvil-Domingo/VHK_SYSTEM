<?php
/* ============================================================
   CATEGORY MANAGEMENT PAGE
   - Displays all categories with product count
   - Allows adding, editing, and deleting categories
   ============================================================ */

/* ----- Security & Database ----- */
require_once '../includes/security.php'; 
require_once '../config/db.php';

/* ----- Redirect if not logged in ----- */
require_login();

/* ----- Flash Messages from Redirects ----- */
$msg = '';
$msg_type = '';
if (isset($_GET['success'])) {
    $msg = 'Category added successfully.';
    $msg_type = 'success';
} elseif (isset($_GET['updated'])) {
    $msg = 'Category updated successfully.';
    $msg_type = 'success';
} elseif (isset($_GET['deleted'])) {
    $msg = 'Category deleted successfully.';
    $msg_type = 'danger';
} elseif (isset($_GET['error'])) {
    /* Map error codes to human-readable messages */
    $errors = [
        'in_use'        => 'Cannot delete — category is in use by products.',
        'invalid'       => 'Invalid input. Please check all fields.',
        'invalid_token' => 'Invalid security token.',
        'failed'        => 'Operation failed. Please try again.',
    ];
    $msg = $errors[$_GET['error']] ?? 'Unknown error.';
    $msg_type = 'warning';
}

/* ----- Fetch All Categories with Product Count ----- */
$categories = $pdo->query('
    SELECT c.*, COUNT(p.id) AS product_count 
    FROM categories c 
    LEFT JOIN products p ON p.category_id = c.id 
    GROUP BY c.id 
    ORDER BY c.name ASC
')->fetchAll();

/* ----- Load Site Header ----- */
require_once '../includes/header.php';
?>

<main class="cat-page-container">
    <div class="cat-content-box">

        <!-- ===== PAGE HEADER ===== -->
        <div class="cat-header-row">
            <div>
                <h1 class="cat-main-title">Category Management</h1>
                <p class="cat-sub-text">Organize your inventory groupings independently.</p>
            </div>
            <a href="add_category.php" class="cat-action-btn-add">+ New Category</a>
        </div>

        <!-- ===== FLASH MESSAGE ===== -->
        <!-- Shows success, warning, or danger alert based on redirect parameter -->
        <?php if ($msg): ?>
            <div class="product-alert product-alert-<?= $msg_type ?>">
                <?= h($msg) ?>
            </div>
        <?php endif; ?>

        <!-- ===== CATEGORIES TABLE ===== -->
        <div class="cat-data-card">
            <table class="cat-custom-table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Name</th>
                        <th>Linked Items</th>
                        <th style="text-align: right;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($categories)): ?>
                        <!-- ----- Empty State ----- -->
                        <tr><td colspan="4" class="table-empty">No categories yet.</td></tr>
                    <?php else: ?>
                        <?php foreach ($categories as $index => $c): ?>
                            <tr>
                                <td><?= $index + 1 ?></td>
                                <td class="cat-name-cell"><?= h($c['name']) ?></td>
                                <!-- Shows how many products are linked to this category -->
                                <td><span class="cat-count-badge"><?= (int)$c['product_count'] ?> Products</span></td>
                                <td style="text-align: right;">
                                    <div class="cat-button-group">
                                        <!-- ----- Edit Button ----- -->
                                        <a href="edit_category.php?edit=<?= $c['id'] ?>" class="cat-link-edit">Edit</a>
                                        <!-- ----- Delete Form ----- -->
                                        <!-- Uses a form with CSRF token for secure deletion -->
                                        <form method="POST" action="../actions/delete_category.php">
                                            <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
                                            <input type="hidden" name="delete_id" value="<?= $c['id'] ?>">
                                            <button type="submit" class="cat-link-delete" data-name="<?= h($c['name']) ?>">Delete</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- ===== DELETE CONFIRMATION SCRIPT ===== -->
    <!-- Prompts the user to confirm before submitting the delete form -->
    <script>
        document.querySelectorAll('.cat-link-delete').forEach(btn => {
            btn.addEventListener('click', function(e) {
                e.preventDefault();
                const name = this.dataset.name;
                if (confirm('Are you sure you want to delete "' + name + '"?')) {
                    this.closest('form').submit();
                }
            });
        });
    </script>
</main>

<!-- ===== FOOTER ===== -->
<?php require_once '../includes/footer.php'; ?>