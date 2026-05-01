<?php
/* ============================================================
   SECURITY HELPERS
   - Starts the session securely
   - Provides reusable helper functions for CSRF, auth, and input
   ============================================================ */

/* ----- Start Session (if not already started) ----- */
if (session_status() === PHP_SESSION_NONE) {
    /* Use secure cookie only on HTTPS connections */
    $secure = !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off';
    session_set_cookie_params([
        'lifetime' => 0,
        'path' => '/',
        'secure' => $secure,
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
    session_start();
}

/* ----- Sanitize Output ----- */
/* Escapes special characters to prevent XSS */
function h($value): string
{
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

/* ----- Generate CSRF Token ----- */
/* Creates and stores a token in the session if one doesn't exist */
function csrf_token(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/* ----- Verify CSRF Token ----- */
/* Compares submitted token against the session token */
function verify_csrf(?string $token): bool
{
    return is_string($token) && isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

/* ----- Require Login ----- */
/* Redirects to login page if the user is not authenticated */
function require_login(): void
{
    if (empty($_SESSION['user_id'])) {
        header('Location: ../login.php');
        exit;
    }
}

/* ----- Redirect Helper ----- */
/* Sends the user to the given path and stops execution */
function redirect_to(string $path): void
{
    header('Location: ' . $path);
    exit;
}

/* ----- Validate Date Format ----- */
/* Returns true only if the date is a valid Y-m-d string */
function valid_date(?string $date): bool
{
    if (!is_string($date) || $date === '') {
        return false;
    }
    $dt = DateTime::createFromFormat('Y-m-d', $date);
    return $dt && $dt->format('Y-m-d') === $date;
}