<?php
/* ============================================================
   ACCOUNT PAGE
   - Displays logged-in user info
   - Links to change password and logout
   ============================================================ */

/* ----- Security & Database ----- */
require_once '../includes/security.php';
require_once '../config/db.php';

/* ----- Redirect if not logged in ----- */
require_login();

/* ----- Load Site Header ----- */
require_once '../includes/header.php';
?>

<main class="acc-body">
    <div class="acc-wrapper">

        <!-- ===== PAGE HEADER ===== -->
        <div class="acc-header-section">
            <h1 class="acc-title">Account</h1>
            <p class="acc-subtitle">Manage your account settings.</p>
        </div>

        <!-- ===== ACCOUNT INFO CARD ===== -->
        <div class="acc-card">
            <div class="acc-card-header">Account Info</div>
            <div class="acc-info-body">

                <!-- ----- Logged-in Username ----- -->
                <div class="acc-info-row">
                    <span class="acc-info-label">Logged in as</span>
                    <span class="acc-info-value"><?= h($_SESSION['username']) ?></span>
                </div>

                <!-- ----- Account Action Buttons ----- -->
                <div class="acc-info-footer">
                    <a href="change_password.php" class="acc-change-pass-btn">Change Password</a>
                    <a href="../actions/logout.php" class="acc-logout-btn">Logout</a>
                </div>

            </div>
        </div>

    </div>
</main>

<!-- ===== FOOTER ===== -->
<?php require_once '../includes/footer.php'; ?>