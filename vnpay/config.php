<?php
// config.php - Thông tin cấu hình VNPAY (Sandbox - Ngrok)

date_default_timezone_set('Asia/Ho_Chi_Minh');

$vnp_TmnCode     = getenv('VNPAY_TMN_CODE') ?: "GYLFMILV";
$vnp_HashSecret  = getenv('VNPAY_HASH_SECRET') ?: "SLBNCWQ9VZ6CRMGW62JRR2CZBJH49BYR";
$vnp_Url         = "https://sandbox.vnpayment.vn/paymentv2/vpcpay.html";

// Lấy base URL từ môi trường (Ví dụ: https://my-app.railway.app)
$app_url = getenv('APP_URL') ?: (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]";

$vnp_Returnurl   = $app_url . "/vnpay/vnpay_return.php";
$vnp_IpnUrl      = $app_url . "/vnpay/vnpay_ipn.php";

// =================================================================

$vnp_Version     = "2.1.0";
$vnp_Command     = "pay";
$vnp_CurrCode    = "VND"; 
$vnp_Locale      = "vn";        // Tiếng Việt
$vnp_OrderType   = "other";     // Loại đơn hàng

// Lưu ý: Khi lên hosting thật, hãy đổi sang https://tenmien.com/...
?>