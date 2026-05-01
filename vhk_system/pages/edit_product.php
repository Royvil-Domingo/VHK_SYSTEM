<?php
/* ============================================================
   EDIT PRODUCT PAGE
   - Form to update an existing product in the inventory
   ============================================================ */

/* ----- Security & Database ----- */
require_once '../includes/security.php';
require_once '../config/db.php';

/* ----- Redirect if not logged in ----- */
require_login();

/* ----- Validate Product ID from URL ----- */
if (!isset($_GET['id'])) {
    header('Location: products.php');
    exit;
}

$id = (int)$_GET['id'];

/* ----- Reject Invalid ID ----- */
if ($id < 1) {
    header('Location: products.php');
    exit;
}

/* ----- Fetch Product & Categories ----- */
try {
    /* Get product by ID */
    $stmt = $pdo->prepare('SELECT * FROM products WHERE id = ?');
    $stmt->execute([$id]);
    $product = $stmt->fetch();

    /* Redirect if product not found */
    if (!$product) {
        header('Location: products.php');
        exit;
    }

    /* Fetch all categories for the dropdown */
    $cat_stmt = $pdo->query('SELECT * FROM categories ORDER BY name ASC');
    $categories = $cat_stmt->fetchAll();
} catch (PDOException $e) {
    /* Redirect on database error */
    header('Location: products.php');
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
                <h2>Edit Item</h2>
                <p>Updating: <strong><?= h($product['name']) ?></strong></p>
            </div>

            <!-- ===== EDIT PRODUCT FORM ===== -->
            <!-- Submits to edit_product_process.php via POST -->
            <form action="../actions/edit_product_process.php" method="POST" class="vhk-form" enctype="multipart/form-data">

                <!-- CSRF token for security -->
                <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">

                <!-- Hidden product ID to identify which record to update -->
                <input type="hidden" name="id" value="<?= $product['id'] ?>">

                <!-- ----- Product Name ----- -->
                <div class="form-group">
                    <label>Product Name</label>
                    <input type="text" name="name" value="<?= h($product['name']) ?>" required>
                </div>

                <!-- ----- Category Dropdown ----- -->
                <!-- Pre-selects the product's current category -->
                <div class="form-group">
                    <label>Category</label>
                    <select name="category_id" required>
                        <?php foreach ($categories as $cat): ?>
                            <option value="<?= $cat['id'] ?>" <?= ($cat['id'] == $product['category_id']) ? 'selected' : '' ?>>
                                <?= h($cat['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- ----- Price & Stock (Side by Side) ----- -->
                <div class="form-row">
                    <div class="form-group">
                        <label>Price (₱)</label>
                        <input type="number" name="price" step="0.01" value="<?= $product['price'] ?>" required>
                    </div>
                    <div class="form-group">
                        <label>Current Stock</label>
                        <input type="number" name="stock" value="<?= $product['stock'] ?>" required>
                    </div>
                </div>

                <!-- ----- Low Stock Alert Level ----- -->
                <div class="form-group">
                    <label>Low Stock Alert Level</label>
                    <input type="number" name="reorder_level" value="<?= $product['reorder_level'] ?>" required>
                </div>

                <!-- ----- Product Image (Optional) ----- -->
                <div class="form-group">
                    <label>Product Image <span style="color:#94a3b8; font-weight:400;">(optional)</span></label>
                    <?php if (!empty($product['image'])): ?>
                        <!-- Show current image with option to remove -->
                        <div class="product-current-image">
                            <img src="../resources/uploads/<?= h($product['image']) ?>" alt="Current image">
                            <span>Current image</span>
                        </div>
                    <?php endif; ?>
                    <!-- Upload new image to replace current -->
                    <input type="file" name="image" accept="image/jpeg,image/png,image/webp" class="product-file-input">
                    <small style="color:#94a3b8;">Upload a new image to replace the current one.</small>
                </div>

                <!-- ----- Form Action Buttons ----- -->
                <div class="btn-group">
                    <a href="products.php" class="cancel-btn">Cancel</a>
                    <button type="submit" name="update_product" class="save-btn">Save Changes</button>
                </div>

            </form>

        </div>
    </div>
</main>

<!-- ===== FOOTER ===== -->
<?php require_once '../includes/footer.php'; ?>