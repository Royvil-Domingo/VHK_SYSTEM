<?php
/* ============================================================
   EDIT CATEGORY PROCESS
   - Validates and processes the edit category form submission
   - Updates the category record in the database
   ============================================================ */

/* ----- Database & Security ----- */
require_once '../config/db.php';
require_once '../includes/security.php';

/* ----- Redirect if not logged in ----- */
require_login();

/* ----- Reject Non-POST Requests ----- */
if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['update_category'])) {
    header('Location: ../pages/categories.php');
    exit;
}

/* ----- Verify CSRF Token ----- */
if (!verify_csrf($_POST['csrf_token'] ?? null)) {
    header('Location: ../pages/categories.php?error=invalid_token');
    exit;
}

/* ----- Sanitize & Cast Inputs ----- */
$id   = (int)($_POST['id'] ?? 0);
$name = trim($_POST['name'] ?? '');

/* ----- Validate Required Fields ----- */
if ($id < 1 || $name === '') {
    header('Location: ../pages/categories.php?error=invalid');
    exit;
}

/* ----- Update Category in Database ----- */
try {
    $stmt = $pdo->prepare('UPDATE categories SET name = ? WHERE id = ?');
    $stmt->execute([$name, $id]);
    header('Location: ../pages/categories.php?updated=1');
    exit;
} catch (PDOException $e) {
    /* Redirect with error on database failure */
    header('Location: ../pages/categories.php?error=failed');
    exit;
}