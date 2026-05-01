<?php
/* ============================================================
   ADD CATEGORY PAGE
   - Form to add a new category to the inventory
   ============================================================ */

/* ----- Security & Database ----- */
require_once '../includes/security.php';
require_once '../config/db.php';

/* ----- Redirect if not logged in ----- */
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

/* ----- Load Site Header ----- */
require_once '../includes/header.php';
?>

<main class="product-body">
    <div class="edit-container">
        <div class="edit-card">

            <!-- ===== PAGE HEADER ===== -->
            <div class="edit-header">
                <h2>Add New Category</h2>
                <a href="categories.php">← Back to Categories</a>
            </div>

            <!-- ===== ADD CATEGORY FORM ===== -->
            <!-- Submits to add_category_process.php via POST -->
            <form action="../actions/add_category_process.php" method="POST" class="vhk-form">

                <!-- CSRF token for security -->
                <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">

                <!-- ----- Category Name ----- -->
                <div class="form-group">
                    <label>Category Name</label>
                    <input type="text" name="name" placeholder="e.g. Cement & Aggregates" required>
                </div>

                <!-- ----- Form Action Buttons ----- -->
                <div class="btn-group">
                    <a href="categories.php" class="cancel-btn">Cancel</a>
                    <button type="submit" name="submit_category" class="save-btn">Save Category</button>
                </div>

            </form>

        </div>
    </div>
</main>

<!-- ===== FOOTER ===== -->
<?php require_once '../includes/footer.php'; ?>