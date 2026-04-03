<?php
// CLI/cron entrypoint: cancel expired unpaid orders and restore stock.
require_once __DIR__ . '/includes/db.php';

$count = cancelExpiredPendingOrders($conn, 500);

echo '[' . date('Y-m-d H:i:s') . "] Cancelled expired orders: {$count}" . PHP_EOL;
