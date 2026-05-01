<?php
/* ============================================================
   DELETE CATEGORY PROCESS
   - Validates and processes the delete category request
   - Blocks deletion if the category is in use by products
   - Removes the category record from the database
   ============================================================ */

/* ----- Database & Security ----- */
require_once '../config/db.php';
require_once '../includes/security.php';

/* ----- Redirect if not logged in ----- */
require_login();

/* ----- Reject Non-POST Requests ----- */
if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['delete_id'])) {
    header('Location: ../pages/categories.php');
    exit;
}

/* ----- Verify CSRF Token ----- */
if (!verify_csrf($_POST['csrf_token'] ?? null)) {
    header('Location: ../pages/categories.php?error=invalid_token');
    exit;
}

/* ----- Sanitize & Validate Category ID ----- */
$id = (int)($_POST['delete_id'] ?? 0);

/* Reject invalid ID */
if ($id < 1) {
    header('Location: ../pages/categories.php');
    exit;
}

/* ----- Check if Category is In Use & Delete ----- */
try {
    /* Block deletion if any products are linked to this category */
    $check = $pdo->prepare('SELECT COUNT(*) FROM products WHERE category_id = ?');
    $check->execute([$id]);
    if ((int)$check->fetchColumn() > 0) {
        header('Location: ../pages/categories.php?error=in_use');
        exit;
    }

    /* ----- Delete Category from Database ----- */
    $pdo->prepare('DELETE FROM categories WHERE id = ?')->execute([$id]);
    header('Location: ../pages/categories.php?deleted=1');
    exit;
} catch (PDOException $e) {
    /* Redirect with error on database failure */
    header('Location: ../pages/categories.php?error=failed');
    exit;
}