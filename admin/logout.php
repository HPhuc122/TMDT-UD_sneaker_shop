<?php
// Handle admin logout via URL param
if (session_status() === PHP_SESSION_NONE) session_start();
if (isset($_GET['action']) && $_GET['action'] === 'logout') {
    session_destroy();
    header('Location: login.php');
    exit;
}
// Redirect to index
header('Location: index.php');
exit;
