<?php
/* ============================================================
   DATABASE CONFIGURATION
   - Connects to the MySQL database using PDO
   ============================================================ */

/* ----- Database Credentials ----- */
$host = "localhost";
$db   = "vhk_system";
$user = "root";
$pass = "";

/* ----- Establish PDO Connection ----- */
try {
    $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8", $user, $pass);
    /* Throw exceptions on errors */
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    /* Return rows as associative arrays by default */
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    /* Stop execution and display error if connection fails */
    die("Connection failed: " . $e->getMessage());
}
?>