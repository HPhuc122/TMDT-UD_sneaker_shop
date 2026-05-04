<?php
require __DIR__ . '/../includes/db.php';
$r = $conn->query("SELECT d.product_id, p.name FROM discount_code_products d JOIN products p ON p.id=d.product_id LIMIT 1");
if ($r) {
    $row = $r->fetch_assoc();
    echo json_encode($row);
} else {
    echo json_encode(null);
}
