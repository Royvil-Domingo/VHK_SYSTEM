<?php
/* ============================================================
   ADD CATEGORY PROCESS
   - Validates and processes the add category form submission
   - Inserts the new category record into the database
   ============================================================ */

/* ----- Security & Database ----- */
require_once '../includes/security.php';
require_once '../config/db.php';

/* ----- Redirect if not logged in ----- */
require_login();

/* ----- Reject Non-POST Requests ----- */
if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['submit_category'])) {
    header('Location: ../pages/categories.php');
    exit;
}

/* ----- Verify CSRF Token ----- */
if (!verify_csrf($_POST['csrf_token'] ?? null)) {
    header('Location: ../pages/categories.php?error=invalid_token');
    exit;
}

/* ----- Sanitize Input ----- */
$name = trim($_POST['name'] ?? '');

/* ----- Validate Required Field ----- */
if ($name === '') {
    header('Location: ../pages/add_category.php?error=empty');
    exit;
}

/* ----- Insert Category into Database ----- */
try {
    $stmt = $pdo->prepare('INSERT INTO categories (name) VALUES (?)');
    $stmt->execute([$name]);
    header('Location: ../pages/categories.php?success=1');
    exit;
} catch (PDOException $e) {
    /* Redirect with error on database failure */
    header('Location: ../pages/add_category.php?error=failed');
    exit;
}