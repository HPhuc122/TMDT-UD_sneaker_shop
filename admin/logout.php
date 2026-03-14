<?php
if (session_status() === PHP_SESSION_NONE) {
    session_name('sneaker_admin_sess');
    session_start();
}
if (isset($_GET['action']) && $_GET['action'] === 'logout') {
    $_SESSION = [];
    session_destroy();
    header('Location: login.php');
    exit;
}
header('Location: index.php');
exit;
