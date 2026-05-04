<?php
require __DIR__ . '/../includes/db.php';
$r = $conn->query("SELECT * FROM discount_codes WHERE id = 8");
if ($r) {
    $d = $r->fetch_assoc();
    echo json_encode($d, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
}
