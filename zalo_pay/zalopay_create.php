<?php
// zalopay_create.php — Tạo đơn ZaloPay và redirect user
require_once '../includes/db.php';
require_once 'zalopay_config.php';

if (!isLoggedIn()) redirect('../login.php');

$order_id = (int)($_GET['order_id'] ?? 0);
$user_id  = $_SESSION['user_id'];

$order = $conn->query("SELECT * FROM orders WHERE id=$order_id AND user_id=$user_id")->fetch_assoc();
if (!$order) redirect('../cart.php');

// Tạo app_trans_id
$app_trans_id = date('ymd') . '_' . $order_id . '_' . time();

$app_time   = round(microtime(true) * 1000);
$amount     = (int)$order['total_amount'];
$embed_data = json_encode(['redirecturl' => ZALOPAY_RETURN_URL]);
$items      = json_encode([]);
$desc       = 'SneakerShop - Thanh toan don ' . $order['order_code'];

$mac_str = implode('|', [
    ZALOPAY_APP_ID, $app_trans_id, 'user_' . $user_id,
    $amount, $app_time, $embed_data, $items,
]);
$mac = hash_hmac('sha256', $mac_str, ZALOPAY_KEY1);

$payload = [
    'app_id'       => ZALOPAY_APP_ID,
    'app_trans_id' => $app_trans_id,
    'app_user'     => 'user_' . $user_id,
    'app_time'     => $app_time,
    'amount'       => $amount,
    'item'         => $items,
    'description'  => $desc,
    'embed_data'   => $embed_data,
    'bank_code'    => '',
    'callback_url' => ZALOPAY_CALLBACK_URL,
    'mac'          => $mac,
];

// Lưu app_trans_id vào order (chỉ cột app_trans_id, không cần payment_status)
$ats = $conn->real_escape_string($app_trans_id);
$conn->query("UPDATE orders SET app_trans_id='$ats' WHERE id=$order_id");

// Gọi API ZaloPay
$ch = curl_init(ZALOPAY_ENDPOINT);
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST           => true,
    CURLOPT_POSTFIELDS     => http_build_query($payload),
    CURLOPT_HTTPHEADER     => ['Content-Type: application/x-www-form-urlencoded'],
    CURLOPT_TIMEOUT        => 30,
    CURLOPT_SSL_VERIFYPEER => false,
]);
$response = curl_exec($ch);
$curl_err  = curl_error($ch);
curl_close($ch);

if ($curl_err) {
    redirect('../checkout.php?zp_fail=1&order_id=' . $order_id);
}

$result = json_decode($response, true);

if (isset($result['order_url']) && $result['return_code'] == 1) {
    // Lưu order_url để trang sandbox test có thể dùng
    $_SESSION['zp_order_url']   = $result['order_url'];
    $_SESSION['zp_app_trans_id'] = $app_trans_id;
    $_SESSION['zp_order_id']    = $order_id;
    $_SESSION['zp_amount']      = $amount;
    $_SESSION['zp_order_code']  = $order['order_code'];

    // Hiển thị trang sandbox với QR + nút Test
    $pageTitle = 'Thanh toán ZaloPay';
    require_once '../includes/db.php'; // already included, but header needs it
    ?>
    <!DOCTYPE html>
    <html lang="vi">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Thanh toán ZaloPay - SneakerShop</title>
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
        <style>
            body { background: #f0f4ff; min-height: 100vh; }
            .zp-card { border-radius: 16px; max-width: 480px; }
            .zp-logo { color: #0068ff; font-weight: 800; font-size: 1.5rem; }
            .btn-zp { background: #0068ff; border: none; color: white; }
            .btn-zp:hover { background: #0054cc; color: white; }
            .sandbox-bar { background: #fff3cd; border: 1px solid #ffc107; border-radius: 8px; }
            .qr-frame { border: 3px solid #0068ff; border-radius: 12px; padding: 12px; display: inline-block; }
        </style>
    </head>
    <body class="d-flex align-items-center justify-content-center py-4">
        <div class="w-100 px-3" style="max-width:500px">

            <!-- Sandbox notice -->
            <div class="sandbox-bar p-3 mb-3 text-center">
                <i class="bi bi-flask me-2 text-warning"></i>
                <strong>Chế độ Sandbox (Test)</strong> — Không thể quét QR thật.
                Dùng nút bên dưới để mô phỏng kết quả.
            </div>

            <!-- ZaloPay card -->
            <div class="card shadow zp-card mx-auto">
                <div class="card-body p-4">
                    <!-- Header -->
                    <div class="d-flex align-items-center gap-3 mb-4">
                        <img src="https://cdn.haitrieu.com/wp-content/uploads/2022/10/Logo-ZaloPay-Square.png"
                             style="width:48px;height:48px;border-radius:10px" alt="ZaloPay">
                        <div>
                            <div class="zp-logo">ZaloPay</div>
                            <div class="text-muted small">GATEWAY — Sandbox</div>
                        </div>
                    </div>

                    <!-- Order info -->
                    <div class="mb-4 p-3 rounded" style="background:#f0f6ff">
                        <div class="d-flex justify-content-between mb-1">
                            <span class="text-muted small">Mã đơn hàng</span>
                            <strong class="small" style="color:#0068ff"><?= htmlspecialchars($order['order_code']) ?></strong>
                        </div>
                        <div class="d-flex justify-content-between mb-1">
                            <span class="text-muted small">Số tiền</span>
                            <strong><?= number_format($amount, 0, ',', '.') ?> ₫</strong>
                        </div>
                        <div class="d-flex justify-content-between">
                            <span class="text-muted small">Nội dung</span>
                            <span class="small text-end" style="max-width:200px"><?= htmlspecialchars($desc) ?></span>
                        </div>
                    </div>

                    <!-- QR code (thật từ ZaloPay, nhưng sandbox không quét được) -->
                    <div class="text-center mb-4">
                        <div class="qr-frame">
                            <img src="https://api.qrserver.com/v1/create-qr-code/?size=180x180&data=<?= urlencode($result['order_url']) ?>"
                                 width="180" height="180" alt="QR Code ZaloPay">
                        </div>
                        <p class="text-muted small mt-2">
                            <i class="bi bi-info-circle me-1"></i>QR chỉ dùng được trong sandbox ZaloPay App thật
                        </p>
                    </div>

                    <!-- Sandbox test buttons -->
                    <div class="border-top pt-3">
                        <p class="text-center small fw-bold text-muted mb-3">
                            <i class="bi bi-gear me-1"></i>SANDBOX — Chọn kết quả để test
                        </p>
                        <div class="d-grid gap-2">
                            <a href="zalopay_test.php?order_id=<?= $order_id ?>&app_trans_id=<?= urlencode($app_trans_id) ?>&result=success"
                               class="btn btn-success fw-semibold py-2"
                               onclick="return confirm('Mô phỏng thanh toán THÀNH CÔNG?')">
                                <i class="bi bi-check-circle me-2"></i>✅ Test — Thanh toán thành công
                            </a>
                            <a href="zalopay_test.php?order_id=<?= $order_id ?>&app_trans_id=<?= urlencode($app_trans_id) ?>&result=fail"
                               class="btn btn-outline-danger fw-semibold py-2"
                               onclick="return confirm('Mô phỏng thanh toán THẤT BẠI?')">
                                <i class="bi bi-x-circle me-2"></i>❌ Test — Thanh toán thất bại
                            </a>
                        </div>
                    </div>

                    <!-- Cancel -->
                    <div class="text-center mt-3">
                        <a href="../checkout.php?zp_fail=1&order_id=<?= $order_id ?>" class="text-muted small text-decoration-none">
                            <i class="bi bi-arrow-left me-1"></i>Hủy giao dịch
                        </a>
                    </div>
                </div>
            </div>
        </div>
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    </body>
    </html>
    <?php
    exit;
} else {
    $err = $result['return_message'] ?? 'Không thể tạo đơn ZaloPay';
    redirect('../checkout.php?zp_fail=1&order_id=' . $order_id);
}