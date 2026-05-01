<?php
/* ============================================================
   CHANGE PASSWORD PAGE
   - Validates current password and updates to a new one
   ============================================================ */

/* ----- Security & Database ----- */
require_once '../includes/security.php';
require_once '../config/db.php';

/* ----- Redirect if not logged in ----- */
require_login();

/* ----- Initialize Message ----- */
$msg = '';

/* ----- Handle Form Submission ----- */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['change_password'])) {

    /* Verify CSRF token before processing */
    if (!verify_csrf($_POST['csrf_token'] ?? null)) {
        $msg = '<div class="acc-alert acc-alert-error">Invalid security token. Please refresh and try again.</div>';
    } else {

        /* ----- Sanitize Inputs ----- */
        $current = $_POST['current_password'] ?? '';
        $new     = $_POST['new_password'] ?? '';
        $confirm = $_POST['confirm_password'] ?? '';

        /* ----- Validate Inputs ----- */
        if ($current === '' || $new === '' || $confirm === '') {
            $msg = '<div class="acc-alert acc-alert-error">Please fill in all fields.</div>';
        } elseif (strlen($new) < 8) {
            $msg = '<div class="acc-alert acc-alert-error">New password must be at least 8 characters.</div>';
        } elseif ($new !== $confirm) {
            $msg = '<div class="acc-alert acc-alert-error">New passwords do not match.</div>';
        } else {

            /* ----- Verify Current Password Against Database ----- */
            $stmt = $pdo->prepare('SELECT password FROM users WHERE id = ?');
            $stmt->execute([$_SESSION['user_id']]);
            $user = $stmt->fetch();

            if (!$user || !password_verify($current, $user['password'])) {
                $msg = '<div class="acc-alert acc-alert-error">Current password is incorrect.</div>';
            } else {
                /* ----- Hash & Save New Password ----- */
                $hashed = password_hash($new, PASSWORD_DEFAULT);
                $pdo->prepare('UPDATE users SET password = ? WHERE id = ?')->execute([$hashed, $_SESSION['user_id']]);
                $msg = '<div class="acc-alert acc-alert-success">Password changed successfully.</div>';
            }
        }
    }
}

/* ----- Load Site Header ----- */
require_once '../includes/header.php';
?>

<main class="acc-body">
    <div class="acc-wrapper">

        <!-- ===== PAGE HEADER ===== -->
        <div class="acc-header-section">
            <h1 class="acc-title">Change Password</h1>
            <p class="acc-subtitle">Update your account password.</p>
        </div>

        <!-- ===== CHANGE PASSWORD FORM ===== -->
        <div class="acc-card">
            <div class="acc-card-header">Change Password</div>
            <div class="acc-form-body">

                <!-- Flash message (success or error) -->
                <?= $msg ?>

                <form method="POST" action="" class="vhk-form">

                    <!-- CSRF token for security -->
                    <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">

                    <!-- ----- Current Password ----- -->
                    <div class="form-group">
                        <label>Current Password</label>
                        <input type="password" name="current_password" placeholder="Enter current password">
                    </div>

                    <!-- ----- New Password ----- -->
                    <div class="form-group">
                        <label>New Password</label>
                        <input type="password" name="new_password" placeholder="At least 8 characters">
                    </div>

                    <!-- ----- Confirm New Password ----- -->
                    <div class="form-group">
                        <label>Confirm New Password</label>
                        <input type="password" name="confirm_password" placeholder="Repeat new password">
                    </div>

                    <!-- ----- Form Action Buttons ----- -->
                    <div class="btn-group">
                        <a href="account.php" class="cancel-btn">Cancel</a>
                        <button type="submit" name="change_password" class="save-btn">Update Password</button>
                    </div>

                </form>
            </div>
        </div>

    </div>
</main>

<!-- ===== FOOTER ===== -->
<?php require_once '../includes/footer.php'; ?>