<?php
// config.php - Thông tin cấu hình VNPAY (Sandbox - Ngrok)

date_default_timezone_set('Asia/Ho_Chi_Minh');

$vnp_TmnCode     = "GYLFMILV";                                           // Terminal ID của bạn
$vnp_HashSecret  = "SLBNCWQ9VZ6CRMGW62JRR2CZBJH49BYR";                   // Secret Key
$vnp_Url         = "https://sandbox.vnpayment.vn/paymentv2/vpcpay.html"; // URL thanh toán Sandbox

// ==================== URL NGROK TĨNH (ĐÃ SỬA) ====================
// Domain tĩnh của bạn: rosana-cucurbitaceous-pei.ngrok-free.dev

$vnp_Returnurl   = "https://rosana-cucurbitaceous-pei.ngrok-free.dev/TMDT-UD_sneaker_shop/vnpay/vnpay_return.php";

$vnp_IpnUrl      = "https://rosana-cucurbitaceous-pei.ngrok-free.dev/TMDT-UD_sneaker_shop/vnpay/vnpay_ipn.php";

// =================================================================

$vnp_Version     = "2.1.0";
$vnp_Command     = "pay";
$vnp_CurrCode    = "VND"; 
$vnp_Locale      = "vn";        // Tiếng Việt
$vnp_OrderType   = "other";     // Loại đơn hàng

// Lưu ý: Khi lên hosting thật, hãy đổi sang https://tenmien.com/...
?>