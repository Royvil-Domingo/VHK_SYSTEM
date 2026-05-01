<?php
/* ============================================================
   PRODUCTS PAGE
   - Displays all products in a grid layout
   - Supports category filter and search by product name
   - Shows stock status (In Stock / Low Stock)
   - Handles alert messages for add, update, delete, and errors
   ============================================================ */

require_once '../includes/security.php';
require_once '../config/db.php';

require_login();

/* ----- Fetch Categories for Filter Dropdown ----- */
$categories = $pdo->query('SELECT * FROM categories ORDER BY name ASC')->fetchAll();

/* ----- Get Filter Values from URL ----- */
$selected_category = isset($_GET['category_id']) ? (int)$_GET['category_id'] : 0;
$search = trim($_GET['search'] ?? '');

/* ----- Fetch Products with Optional Category Filter and Search ----- */
try {
    if ($selected_category > 0) {
        /* Filter by category and search keyword */
        $stmt = $pdo->prepare("
            SELECT p.*, c.name as category_name 
            FROM products p 
            LEFT JOIN categories c ON p.category_id = c.id 
            WHERE p.category_id = ? AND p.name LIKE ?
            ORDER BY p.name ASC
        ");
        $stmt->execute([$selected_category, '%' . $search . '%']);
    } else {
        /* Search all categories */
        $stmt = $pdo->prepare("
            SELECT p.*, c.name as category_name 
            FROM products p 
            LEFT JOIN categories c ON p.category_id = c.id 
            WHERE p.name LIKE ?
            ORDER BY p.name ASC
        ");
        $stmt->execute(['%' . $search . '%']);
    }
    $products = $stmt->fetchAll();
} catch (PDOException $e) {
    $products = [];
}

/* ----- Alert Messages from URL Parameters ----- */
$msg = '';
$msg_type = '';
if (isset($_GET['success'])) {
    $msg = 'Product added successfully.';
    $msg_type = 'success';
} elseif (isset($_GET['updated'])) {
    $msg = 'Product updated successfully.';
    $msg_type = 'success';
} elseif (isset($_GET['deleted'])) {
    $msg = 'Product deleted successfully.';
    $msg_type = 'danger';
} elseif (isset($_GET['error'])) {
    $errors = [
        'invalid'       => 'Invalid input. Please check all fields.',
        'invalid_token' => 'Invalid security token.',
        'failed'        => 'Operation failed. Please try again.',
    ];
    $msg = $errors[$_GET['error']] ?? 'Unknown error.';
    $msg_type = 'warning';
}

require_once '../includes/header.php';
?>

<main class="product-body">
    <div class="product-wrapper">

        <!-- ===== PAGE HEADER ===== -->
        <div class="product-header-section">
            <div class="product-title-group">
                <h1 class="product-title">Products Inventory</h1>
                <p class="product-subtitle">Manage your construction supplies and monitor stock levels.</p>
            </div>
            <!-- Add New Product Button -->
            <a href="add_product.php" class="product-btn-add">+ Add New Product</a>
        </div>

        <!-- ===== ALERT MESSAGE ===== -->
        <?php if ($msg): ?>
            <div class="product-alert product-alert-<?= $msg_type ?>">
                <?= h($msg) ?>
            </div>
        <?php endif; ?>

        <!-- ===== CATEGORY FILTER + SEARCH BAR ===== -->
        <!-- Submits on category change, search button for keyword filter -->
        <form method="GET" action="" class="product-filter-form">
            <select name="category_id" onchange="this.form.submit()" class="product-filter-select">
                <option value="0">All Categories</option>
                <?php foreach ($categories as $cat): ?>
                    <option value="<?= $cat['id'] ?>" <?= $selected_category === (int)$cat['id'] ? 'selected' : '' ?>>
                        <?= h($cat['name']) ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <div class="product-search-wrap">
                <img src="../resources/images/search_icon.png" alt="Search" class="product-search-icon">
                <input type="text" name="search" class="product-search-input" placeholder="Search product..." value="<?= h($_GET['search'] ?? '') ?>">
            </div>

            <button type="submit" class="product-search-btn">Search</button>
        </form>

        <!-- ===== PRODUCT GRID ===== -->
        <?php if (count($products) > 0): ?>
            <div class="product-grid">
                <?php foreach ($products as $row): ?>
                    <div class="product-grid-card">

                        <!-- Product Image -->
                        <div class="product-grid-image">
                            <?php if (!empty($row['image'])): ?>
                                <img src="../resources/uploads/<?= h($row['image']) ?>" alt="<?= h($row['name']) ?>">
                            <?php else: ?>
                                <div class="product-grid-no-image">No Image</div>
                            <?php endif; ?>
                        </div>

                        <!-- Product Info -->
                        <div class="product-grid-info">
                            <div class="product-grid-name"><?= h($row['name']) ?></div>
                            <div class="product-grid-category"><?= h($row['category_name'] ?? 'Uncategorized') ?></div>
                            <div class="product-grid-price">₱<?= number_format($row['price'], 2) ?></div>

                            <!-- Stock Status Badge -->
                            <div class="product-grid-stock">
                                Stock: <?= (int)$row['stock'] ?> &nbsp;
                                <?php if ($row['stock'] <= ($row['reorder_level'] ?? 0)): ?>
                                    <span class="product-stock-low">Low Stock</span>
                                <?php else: ?>
                                    <span class="product-stock-ok">In Stock</span>
                                <?php endif; ?>
                            </div>

                            <!-- Edit and Delete Actions -->
                            <div class="product-grid-actions">
                                <a href="edit_product.php?id=<?= $row['id'] ?>" class="product-btn-edit">Edit</a>
                                <!-- Delete Form (POST with CSRF protection) -->
                                <form method="POST" action="../actions/delete_product.php" style="display:inline;">
                                    <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
                                    <input type="hidden" name="delete_id" value="<?= $row['id'] ?>">
                                    <button type="submit" class="product-btn-delete" data-name="<?= h($row['name']) ?>">Delete</button>
                                </form>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <!-- Empty State -->
            <div class="product-empty">No products found.</div>
        <?php endif; ?>

    </div>

    <!-- ===== DELETE CONFIRMATION SCRIPT ===== -->
    <!-- Prompts user to confirm before submitting delete form -->
    <script>
        document.querySelectorAll('.product-btn-delete').forEach(btn => {
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