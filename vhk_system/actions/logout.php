<?php
/* ============================================================
   LOGOUT
   - Clears session data and destroys the session
   - Redirects to the login page
   ============================================================ */

/* ----- Load Security ----- */
require_once '../includes/security.php';

/* ----- Clear All Session Data ----- */
$_SESSION = [];

/* ----- Delete Session Cookie ----- */
if (ini_get('session.use_cookies')) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'], $params['secure'], $params['httponly']);
}

/* ----- Destroy the Session ----- */
session_destroy();

/* ----- Redirect to Login Page ----- */
header('Location: ../index.php');
exit;