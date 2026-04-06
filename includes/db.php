<?php
// includes/db.php
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'sneaker_shop');

// Session names - admin uses separate cookie to allow simultaneous login
if (!defined('USER_SESSION_NAME')) {
    define('USER_SESSION_NAME', 'sneaker_user_sess');
}
if (!defined('ADMIN_SESSION_NAME')) {
    define('ADMIN_SESSION_NAME', 'sneaker_admin_sess');
}

$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
$conn->set_charset('utf8mb4');

if ($conn->connect_error) {
    die("Kết nối thất bại: " . $conn->connect_error);
}

// Helper functions
function sanitize($conn, $str) {
    return $conn->real_escape_string(trim($str));
}

function getSellPrice($import_price, $profit_rate) {
    return $import_price * (1 + $profit_rate / 100);
}

function formatPrice($price) {
    return number_format($price, 0, ',', '.') . ' ₫';
}

function isLoggedIn() {
    if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'customer') {
        return false;
    }
    // Check DB status - if locked, clear session data but do NOT call session_destroy()
    // (session_destroy sends headers which causes "headers already sent" if called after HTML)
    global $conn;
    $uid = (int)$_SESSION['user_id'];
    $result = $conn->query("SELECT status FROM users WHERE id=$uid AND role='customer' LIMIT 1");
    if (!$result || $result->num_rows === 0) {
        $_SESSION = [];
        return false;
    }
    $row = $result->fetch_assoc();
    if ($row['status'] === 'locked') {
        $_SESSION = [];
        return false;
    }
    return true;
}

// Call this BEFORE any HTML output to fully destroy session of locked/deleted users
function kickIfLocked() {
    if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'customer') {
        return;
    }
    global $conn;
    $uid = (int)$_SESSION['user_id'];
    $result = $conn->query("SELECT status FROM users WHERE id=$uid AND role='customer' LIMIT 1");
    if (!$result || $result->num_rows === 0 || $result->fetch_assoc()['status'] === 'locked') {
        $_SESSION = [];
        session_destroy();
        redirect('/sneaker_shop/login.php');
    }
}

function isAdmin() {
    return isset($_SESSION['user_id']) && isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
}

function redirect($url) {
    header("Location: $url");
    exit();
}

function generateCode($prefix) {
    return $prefix . date('YmdHis') . rand(100, 999);
}

function hasTableColumn($conn, $table, $column) {
    static $cache = [];
    $key = $table . '.' . $column;
    if (isset($cache[$key])) return $cache[$key];
    $res = $conn->query("SHOW COLUMNS FROM {$table} LIKE '" . $conn->real_escape_string($column) . "'");
    $cache[$key] = ($res && $res->num_rows > 0);
    return $cache[$key];
}

function getStockColumnName($conn) {
    if (hasTableColumn($conn, 'products', 'stock_quantity')) return 'stock_quantity';
    if (hasTableColumn($conn, 'products', 'quantity')) return 'quantity';
    return null;
}

function getPendingPaymentStatuses($conn) {
    $statuses = [];
    $statusCol = $conn->query("SHOW COLUMNS FROM orders LIKE 'status'");
    if ($statusCol && $statusCol->num_rows > 0) {
        $statusDef = $statusCol->fetch_assoc();
        $type = $statusDef['Type'] ?? '';
        if (strpos($type, "'pending_payment'") !== false) $statuses[] = 'pending_payment';
        if (strpos($type, "'awaiting_payment'") !== false) $statuses[] = 'awaiting_payment';
    }
    if (empty($statuses)) $statuses[] = 'pending';
    return $statuses;
}

function getOnlinePendingStatus($conn) {
    $statuses = getPendingPaymentStatuses($conn);
    if (in_array('pending_payment', $statuses, true)) return 'pending_payment';
    if (in_array('awaiting_payment', $statuses, true)) return 'awaiting_payment';
    return 'pending';
}

function isPendingPaymentOrderStatus($conn, $status) {
    return in_array($status, getPendingPaymentStatuses($conn), true);
}

function cancelExpiredPendingOrders($conn, $limit = 100) {
    if (!hasTableColumn($conn, 'orders', 'payment_deadline')) return 0;

    $pendingStatuses = getPendingPaymentStatuses($conn);
    if (empty($pendingStatuses)) return 0;

    $stockColumn = getStockColumnName($conn);
    if (!$stockColumn) return 0;

    $statusSql = [];
    foreach ($pendingStatuses as $s) {
        $statusSql[] = "'" . $conn->real_escape_string($s) . "'";
    }
    $inStatuses = implode(',', $statusSql);
    $hasPaymentStatus = hasTableColumn($conn, 'orders', 'payment_status');

    $cancelled = 0;
    $limit = max(1, (int)$limit);

    try {
        $conn->begin_transaction();

        $wherePaid = $hasPaymentStatus ? "AND (payment_status IS NULL OR payment_status <> 'paid')" : '';

        // Backfill deadline from order creation time for old rows.
        $conn->query("UPDATE orders SET payment_deadline = DATE_ADD(created_at, INTERVAL 24 HOUR) WHERE status IN ($inStatuses) AND payment_deadline IS NULL $wherePaid");

        $expired = $conn->query("SELECT id FROM orders WHERE status IN ($inStatuses) AND payment_deadline IS NOT NULL AND payment_deadline < NOW() $wherePaid ORDER BY payment_deadline ASC LIMIT $limit FOR UPDATE");

        if ($expired && $expired->num_rows > 0) {
            while ($row = $expired->fetch_assoc()) {
                $oid = (int)$row['id'];

                $setSql = "status='cancelled'";
                if ($hasPaymentStatus) $setSql .= ", payment_status='failed'";
                $conn->query("UPDATE orders SET $setSql WHERE id=$oid");

                $details = $conn->query("SELECT product_id, size_id, color_id, quantity FROM order_details WHERE order_id=$oid");
                while ($d = $details->fetch_assoc()) {
                    $pid = (int)$d['product_id'];
                    $size = (int)($d['size_id'] ?? 0);
                    $color = (int)($d['color_id'] ?? 0);
                    $qty = (int)$d['quantity'];
                    if ($size > 0 && $color > 0 && hasTableColumn($conn, 'product_varieties', 'stock_quantity')) {
                        $conn->query("UPDATE product_varieties SET stock_quantity = stock_quantity + $qty WHERE product_id=$pid AND size_id=$size AND color_id=$color");
                    } else {
                        $conn->query("UPDATE products SET {$stockColumn} = {$stockColumn} + $qty WHERE id=$pid");
                    }
                }

                $cancelled++;
            }
        }

        $conn->commit();
    } catch (Throwable $e) {
        $conn->rollback();
        return 0;
    }

    return $cancelled;
}

function runExpiredOrderCancellationFallback($conn) {
    static $alreadyRan = false;
    if ($alreadyRan) return;
    $alreadyRan = true;
    cancelExpiredPendingOrders($conn, 50);
}

// Start USER session (only if not already started by admin side)
// Admin files set their own session name before including db.php
if (session_status() === PHP_SESSION_NONE) {
    session_name(USER_SESSION_NAME);
    session_start();
}

// Auto-kick locked/deleted users BEFORE any HTML is output.
// This is safe because db.php is always included before header.php outputs HTML.
// We only do this for user sessions (not admin).
if (session_name() === USER_SESSION_NAME) {
    kickIfLocked();
}

// Fallback: auto-cancel expired unpaid orders on normal traffic.
runExpiredOrderCancellationFallback($conn);
?>
