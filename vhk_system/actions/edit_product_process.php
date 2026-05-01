<?php
/* ============================================================
   EDIT PRODUCT PROCESS
   - Validates and processes the edit product form submission
   - Handles image removal and new image upload
   - Updates the product record in the database
   ============================================================ */

/* ----- Database & Security ----- */
require_once '../config/db.php';
require_once '../includes/security.php';

/* ----- Redirect if not logged in ----- */
require_login();

/* ----- Reject Non-POST Requests ----- */
if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['update_product'])) {
    header('Location: ../pages/products.php');
    exit;
}

/* ----- Verify CSRF Token ----- */
if (!verify_csrf($_POST['csrf_token'] ?? null)) {
    header('Location: ../pages/products.php?error=invalid_token');
    exit;
}

/* ----- Sanitize & Cast Inputs ----- */
$id            = (int)($_POST['id'] ?? 0);
$name          = trim($_POST['name'] ?? '');
$category_id   = (int)($_POST['category_id'] ?? 0);
$price         = (float)($_POST['price'] ?? 0);
$stock         = (int)($_POST['stock'] ?? 0);
$reorder_level = (int)($_POST['reorder_level'] ?? 0);

/* ----- Validate Required Fields ----- */
if ($id < 1 || $name === '' || $category_id < 1 || $price <= 0) {
    header('Location: ../pages/products.php?error=invalid');
    exit;
}

/* ----- Fetch Current Product Image ----- */
$stmt = $pdo->prepare('SELECT image FROM products WHERE id = ?');
$stmt->execute([$id]);
$current = $stmt->fetch();
$current_image = $current['image'] ?? null;
$new_image = $current_image;

/* ----- Handle Remove Image Checkbox ----- */
if (isset($_POST['remove_image']) && $_POST['remove_image'] == '1') {
    /* Delete file from server if it exists */
    if ($current_image && file_exists(__DIR__ . '/../resources/uploads/' . $current_image)) {
        unlink(__DIR__ . '/../resources/uploads/' . $current_image);
    }
    $new_image = null;
}

/* ----- Handle New Image Upload ----- */
if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
    $allowed_types = ['image/jpeg', 'image/png', 'image/webp'];
    $max_size = 2 * 1024 * 1024; // 2MB

    $file_type = mime_content_type($_FILES['image']['tmp_name']);
    $file_size = $_FILES['image']['size'];

    /* Reject invalid file type */
    if (!in_array($file_type, $allowed_types)) {
        header('Location: ../pages/edit_product.php?id=' . $id . '&error=invalid_image');
        exit;
    }

    /* Reject file exceeding size limit */
    if ($file_size > $max_size) {
        header('Location: ../pages/edit_product.php?id=' . $id . '&error=image_too_large');
        exit;
    }

    /* Delete old image before saving new one */
    if ($current_image && file_exists(__DIR__ . '/../resources/uploads/' . $current_image)) {
        unlink(__DIR__ . '/../resources/uploads/' . $current_image);
    }

    /* Generate unique filename and move uploaded file */
    $ext = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
    $new_image = uniqid('product_') . '.' . strtolower($ext);
    $upload_path = __DIR__ . '/../resources/uploads/' . $new_image;

    if (!move_uploaded_file($_FILES['image']['tmp_name'], $upload_path)) {
        header('Location: ../pages/edit_product.php?id=' . $id . '&error=upload_failed');
        exit;
    }
}

/* ----- Update Product in Database ----- */
try {
    $stmt = $pdo->prepare('UPDATE products SET name = ?, category_id = ?, price = ?, stock = ?, reorder_level = ?, image = ? WHERE id = ?');
    $stmt->execute([$name, $category_id, $price, $stock, $reorder_level, $new_image, $id]);
    header('Location: ../pages/products.php?updated=1');
    exit;
} catch (PDOException $e) {
    /* Redirect with error on database failure */
    header('Location: ../pages/products.php?error=failed');
    exit;
}