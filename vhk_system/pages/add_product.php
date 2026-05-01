<?php
/* ============================================================
   ADD PRODUCT PAGE
   - Form to add a new construction item to the inventory
   ============================================================ */

/* ----- Security & Database ----- */
require_once '../includes/security.php';
require_once '../config/db.php';

/* ----- Redirect if not logged in ----- */
require_login();

/* ----- Fetch Categories for Dropdown ----- */
try {
    $cat_stmt = $pdo->query("SELECT * FROM categories ORDER BY name ASC");
    $categories = $cat_stmt->fetchAll();
} catch (PDOException $e) {
    /* Redirect with error if categories fail to load */
    header('Location: products.php?error=failed');
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
                <h2>Add New Construction Item</h2>
                <a href="products.php">← Back to Inventory</a>
            </div>

            <!-- ===== ADD PRODUCT FORM ===== -->
            <!-- Submits to add_product_process.php via POST -->
            <form action="../actions/add_product_process.php" method="POST" class="vhk-form" enctype="multipart/form-data">

                <!-- CSRF token for security -->
                <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">

                <!-- ----- Product Name ----- -->
                <div class="form-group">
                    <label>Product Name</label>
                    <input type="text" name="name" placeholder="e.g. Portland Cement" required>
                </div>

                <!-- ----- Category Dropdown ----- -->
                <div class="form-group">
                    <label>Category</label>
                    <select name="category_id" required>
                        <option value="">Select Category</option>
                        <?php foreach ($categories as $cat): ?>
                            <option value="<?= $cat['id'] ?>">
                                <?= h($cat['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- ----- Price & Stock (Side by Side) ----- -->
                <div class="form-row">
                    <div class="form-group">
                        <label>Price (₱)</label>
                        <input type="number" name="price" step="0.01" required>
                    </div>
                    <div class="form-group">
                        <label>Initial Stock</label>
                        <input type="number" name="stock" required>
                    </div>
                </div>

                <!-- ----- Low Stock Alert Level ----- -->
                <div class="form-group">
                    <label>Low Stock Alert Level</label>
                    <input type="number" name="reorder_level" value="10" required>
                </div>

                <!-- ----- Product Image (Optional) ----- -->
                <div class="form-group">
                    <label>Product Image <span style="color:#94a3b8; font-weight:400;">(optional)</span></label>
                    <input type="file" name="image" accept="image/jpeg,image/png,image/webp" class="product-file-input">
                </div>

                <!-- ----- Form Action Buttons ----- -->
                <div class="btn-group">
                    <a href="products.php" class="cancel-btn">Cancel</a>
                    <button type="submit" name="submit_product" class="save-btn">Save to System</button>
                </div>

            </form>

        </div>
    </div>
</main>

<!-- ===== FOOTER ===== -->
<?php require_once '../includes/footer.php'; ?>