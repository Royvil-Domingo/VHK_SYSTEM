<?php
/* ============================================================
   ADD PRODUCT PROCESS
   - Validates and processes the add product form submission
   - Handles optional image upload
   - Inserts the new product record into the database
   ============================================================ */

/* ----- Database & Security ----- */
require_once '../config/db.php';
require_once '../includes/security.php';

/* ----- Redirect if not logged in ----- */
require_login();

/* ----- Reject Non-POST Requests ----- */
if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['submit_product'])) {
    header('Location: ../pages/products.php');
    exit;
}

/* ----- Verify CSRF Token ----- */
if (!verify_csrf($_POST['csrf_token'] ?? null)) {
    header('Location: ../pages/products.php?error=invalid_token');
    exit;
}

/* ----- Sanitize & Cast Inputs ----- */
$name          = trim($_POST['name'] ?? '');
$category_id   = (int)($_POST['category_id'] ?? 0);
$price         = (float)($_POST['price'] ?? 0);
$stock         = (int)($_POST['stock'] ?? 0);
$reorder_level = (int)($_POST['reorder_level'] ?? 0);

/* ----- Validate Required Fields ----- */
if ($name === '' || $category_id < 1 || $price <= 0) {
    header('Location: ../pages/add_product.php?error=invalid');
    exit;
}

/* ----- Handle Image Upload (Optional) ----- */
$image_filename = null;

if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
    $allowed_types = ['image/jpeg', 'image/png', 'image/webp'];
    $max_size = 2 * 1024 * 1024; // 2MB

    $file_type = mime_content_type($_FILES['image']['tmp_name']);
    $file_size = $_FILES['image']['size'];

    /* Reject invalid file type */
    if (!in_array($file_type, $allowed_types)) {
        header('Location: ../pages/add_product.php?error=invalid_image');
        exit;
    }

    /* Reject file exceeding size limit */
    if ($file_size > $max_size) {
        header('Location: ../pages/add_product.php?error=image_too_large');
        exit;
    }

    /* Generate unique filename and move uploaded file */
    $ext = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
    $image_filename = uniqid('product_') . '.' . strtolower($ext);
    $upload_path = __DIR__ . '/../resources/uploads/' . $image_filename;

    if (!move_uploaded_file($_FILES['image']['tmp_name'], $upload_path)) {
        header('Location: ../pages/add_product.php?error=upload_failed');
        exit;
    }
}

/* ----- Insert Product into Database ----- */
try {
    $stmt = $pdo->prepare('INSERT INTO products (name, category_id, price, stock, reorder_level, image) VALUES (?, ?, ?, ?, ?, ?)');
    $stmt->execute([$name, $category_id, $price, $stock, $reorder_level, $image_filename]);
    header('Location: ../pages/products.php?success=1');
    exit;
} catch (PDOException $e) {
    /* Delete uploaded image if DB insert fails */
    if ($image_filename && file_exists(__DIR__ . '/../resources/uploads/' . $image_filename)) {
        unlink(__DIR__ . '/../resources/uploads/' . $image_filename);
    }
    header('Location: ../pages/add_product.php?error=failed');
    exit;
}