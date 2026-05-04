<?php
require __DIR__ . '/../includes/db.php';
$product_id = 9;
$r = $conn->query("SELECT * FROM discount_code_products WHERE product_id = $product_id");
$rows = [];
if ($r) {
    while ($row = $r->fetch_assoc()) $rows[] = $row;
}
echo json_encode($rows, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
