<?php
/* ============================================================
   SITE HEADER
   - Loads on every page
   - Renders the navigation bar with active link highlighting
   ============================================================ */

/* ----- Load Security ----- */
require_once __DIR__ . '/security.php'; 

/* ----- Get Current Page Filename for Active Nav Link ----- */
$current = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>VHK Construction Supply</title>
    <!-- Main stylesheet -->
    <link rel="stylesheet" href="../resources/css/style.css">
    <!-- jQuery library -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
    <!-- Select2 dropdown styles -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.1.0-rc.0/css/select2.min.css" rel="stylesheet">
</head>
<body>

<!-- ===== SITE HEADER ===== -->
<header class="vhkHeader">

    <!-- ----- Logo ----- -->
    <div class="vhkHeader-logo">
        <img src="../resources/images/vhk_logo.png" alt="VHK Logo" class="vhkHeader-logo-img">
        VHK <span>CONSTRUCTION</span>
    </div>

    <!-- ----- Hamburger Button (Mobile) ----- -->
    <button class="vhkHeader-hamburger" id="hamburgerBtn" aria-label="Toggle navigation">
        <img src="../resources/images/menu_icon.png" class="icon-menu" alt="Menu">
        <img src="../resources/images/close_icon.png" class="icon-close" alt="Close">
    </button>

    <!-- ----- Navigation Links ----- -->
    <!-- Adds 'active' class to the link matching the current page -->
    <nav class="vhkHeader-nav" id="vhkNav">
        <a href="dashboard.php" class="<?= $current === 'dashboard.php' ? 'active' : '' ?>">Dashboard</a>
        <a href="products.php" class="<?= $current === 'products.php' ? 'active' : '' ?>">Products</a>
        <a href="categories.php" class="<?= $current === 'categories.php' ? 'active' : '' ?>">Categories</a>
        <a href="sales.php" class="<?= $current === 'sales.php' ? 'active' : '' ?>">Sales</a>
        <a href="expenses.php" class="<?= $current === 'expenses.php' ? 'active' : '' ?>">Expenses</a>
        <a href="reports.php" class="<?= $current === 'reports.php' ? 'active' : '' ?>">Reports</a>
        <a href="account.php" class="<?= $current === 'account.php' ? 'active' : '' ?>">Account</a>
    </nav>

</header>

<!-- ===== HAMBURGER MENU SCRIPT ===== -->
<script>
    const btn = document.getElementById('hamburgerBtn');
    const nav = document.getElementById('vhkNav');

    /* Toggle mobile menu open/close on hamburger click */
    btn.addEventListener('click', () => {
        btn.classList.toggle('open');
        nav.classList.toggle('open');
    });

    /* Close menu when a nav link is clicked */
    nav.querySelectorAll('a').forEach(link => {
        link.addEventListener('click', () => {
            btn.classList.remove('open');
            nav.classList.remove('open');
        });
    });
</script>