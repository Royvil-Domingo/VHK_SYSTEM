<?php
/* ============================================================
   LOGIN PAGE
   - Handles user authentication
   - Redirects to dashboard if already logged in
   - Validates CSRF token on POST
   ============================================================ */

require_once '../includes/security.php';
require_once '../config/db.php';

/* ----- Redirect if already logged in ----- */
if (isset($_SESSION['user_id'])) {
    header('Location: dashboard.php');
    exit;
}

$error = '';
$username = '';

/* ----- Handle Login Form Submission ----- */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    /* Validate CSRF token */
    if (!verify_csrf($_POST['csrf_token'] ?? null)) {
        $error = 'Invalid security token. Please refresh and try again.';
    } else {
        $username = trim($_POST['username'] ?? '');
        $password = $_POST['password'] ?? '';

        /* Check if fields are empty */
        if ($username === '' || $password === '') {
            $error = 'Please fill in all fields.';
        } else {
            /* Query user from database */
            $stmt = $pdo->prepare('SELECT id, username, password FROM users WHERE username = ? LIMIT 1');
            $stmt->execute([$username]);
            $user = $stmt->fetch();

            /* Verify password and start session */
            if ($user && password_verify($password, $user['password'])) {
                session_regenerate_id(true);
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                header('Location: dashboard.php');
                exit;
            }
            $error = 'Invalid username or password.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - VHK Construction Supply</title>
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <!-- Main Stylesheet -->
    <link rel="stylesheet" href="../resources/css/style.css">
</head>
<body class="login-body">

    <!-- ===== LOGIN CARD ===== -->
    <div class="login-card">

        <!-- Logo -->
        <div class="login-logo-icon">
            <img src="../resources/images/vhk_logo.png" alt="VHK Construction Supply">
        </div>

        <!-- Title -->
        <h2 class="login-title">Welcome back</h2>
        <p class="login-subtitle">VHK Construction Supply</p>

        <!-- ===== LOGIN FORM ===== -->
        <form action="" method="POST">

            <!-- CSRF Token (security) -->
            <input type="hidden" name="csrf_token" value="<?= h(csrf_token()) ?>">

            <!-- Username Field -->
            <div class="login-form-group">
                <label class="login-form-label">USERNAME</label>
                <input type="text" name="username" class="login-input" value="<?= h($username) ?>" placeholder="Enter Username" required>
            </div>

            <!-- Password Field with Show/Hide Toggle -->
            <div class="login-form-group">
                <label class="login-form-label">PASSWORD</label>
                <div class="login-password-wrap">
                    <input type="password" name="password" id="passwordInput" class="login-input" placeholder="Enter Password" required>
                    <button type="button" class="login-eye-btn" id="togglePassword">
                        <img src="../resources/images/eye_close.png" id="eyeIcon" alt="Show password">
                    </button>
                </div>
            </div>

            <!-- Error Message -->
            <?php if ($error !== ''): ?>
                <div class="login-error-text"><?= h($error) ?></div>
            <?php endif; ?>

            <!-- Submit Button -->
            <button type="submit" class="login-btn">Login</button>
        </form>

        <!-- Footer Info -->
        <div class="login-footer-info">
            IT 225 — Web Systems and Technologies I BASC
        </div>
    </div>

    <!-- ===== SHOW/HIDE PASSWORD SCRIPT ===== -->
    <script>
        const toggle = document.getElementById('togglePassword');
        const input = document.getElementById('passwordInput');
        const icon = document.getElementById('eyeIcon');

        toggle.addEventListener('click', function() {
            if (input.type === 'password') {
                input.type = 'text';
                icon.src = '../resources/images/eye_open.png';
            } else {
                input.type = 'password';
                icon.src = '../resources/images/eye_close.png';
            }
        });
    </script>
</body>
</html>