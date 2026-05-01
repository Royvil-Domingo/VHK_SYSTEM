<?php
/* ============================================================
   DELETE PRODUCT PROCESS
   - Validates and processes the delete product request
   - Removes the product record from the database
   ============================================================ */

/* ----- Database & Security ----- */
require_once '../config/db.php';
require_once '../includes/security.php';

/* ----- Redirect if not logged in ----- */
require_login();

/* ----- Reject Non-POST Requests ----- */
if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['delete_id'])) {
    header('Location: ../pages/products.php');
    exit;
}

/* ----- Verify CSRF Token ----- */
if (!verify_csrf($_POST['csrf_token'] ?? null)) {
    header('Location: ../pages/products.php?error=invalid_token');
    exit;
}

/* ----- Sanitize & Validate Product ID ----- */
$id = (int)($_POST['delete_id'] ?? 0);

/* Reject invalid ID */
if ($id < 1) {
    header('Location: ../pages/products.php');
    exit;
}

/* ----- Delete Product from Database ----- */
try {
    $pdo->prepare('DELETE FROM products WHERE id = ?')->execute([$id]);
    header('Location: ../pages/products.php?deleted=1');
    exit;
} catch (PDOException $e) {
    /* Redirect with error on database failure */
    header('Location: ../pages/products.php?error=failed');
    exit;
}