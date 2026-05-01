<?php
/* ============================================================
   EDIT CATEGORY PAGE
   - Form to update an existing category
   ============================================================ */

/* ----- Security & Database ----- */
require_once '../includes/security.php';
require_once '../config/db.php';

/* ----- Redirect if not logged in ----- */
require_login();

/* ----- Validate Category ID from URL ----- */
if (!isset($_GET['edit'])) {
    header('Location: categories.php');
    exit;
}

$id = (int)$_GET['edit'];

/* ----- Reject Invalid ID ----- */
if ($id < 1) {
    header('Location: categories.php');
    exit;
}

/* ----- Fetch Category by ID ----- */
try {
    $stmt = $pdo->prepare('SELECT * FROM categories WHERE id = ?');
    $stmt->execute([$id]);
    $category = $stmt->fetch();

    /* Redirect if category not found */
    if (!$category) {
        header('Location: categories.php');
        exit;
    }
} catch (PDOException $e) {
    /* Redirect on database error */
    header('Location: categories.php');
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
                <h2>Edit Category</h2>
                <p>Updating: <strong><?= h($category['name']) ?></strong></p>
                <a href="categories.php">← Back to Categories</a>
            </div>

            <!-- ===== EDIT CATEGORY FORM ===== -->
            <!-- Submits to edit_category_process.php via POST -->
            <form action="../actions/edit_category_process.php" method="POST" class="vhk-form">

                <!-- CSRF token for security -->
                <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">

                <!-- Hidden category ID to identify which record to update -->
                <input type="hidden" name="id" value="<?= $category['id'] ?>">

                <!-- ----- Category Name ----- -->
                <div class="form-group">
                    <label>Category Name</label>
                    <input type="text" name="name" value="<?= h($category['name']) ?>" required>
                </div>

                <!-- ----- Form Action Buttons ----- -->
                <div class="btn-group">
                    <a href="categories.php" class="cancel-btn">Cancel</a>
                    <button type="submit" name="update_category" class="save-btn">Save Changes</button>
                </div>

            </form>

        </div>
    </div>
</main>

<!-- ===== FOOTER ===== -->
<?php require_once '../includes/footer.php'; ?>