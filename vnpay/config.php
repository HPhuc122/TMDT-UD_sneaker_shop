<?php
// config.php - Thông tin cấu hình VNPAY (Sandbox)

date_default_timezone_set('Asia/Ho_Chi_Minh');

$vnp_TmnCode     = "GYLFMILV";                                           // Terminal ID của bạn
$vnp_HashSecret  = "SLBNCWQ9VZ6CRMGW62JRR2CZBJH49BYR";                   // Secret Key
$vnp_Url         = "https://sandbox.vnpayment.vn/paymentv2/vpcpay.html"; // URL thanh toán Sandbox

// URL trả về sau khi khách thanh toán xong (Return URL)
$vnp_Returnurl   = "http://localhost/TMDT-UD_sneaker_shop/vnpay/vnpay_return.php";

// URL IPN - VNPAY gọi ngầm để thông báo kết quả (rất quan trọng)
$vnp_IpnUrl      = "http://localhost/TMDT-UD_sneaker_shop/vnpay/vnpay_ipn.php";

$vnp_Version     = "2.1.0";
$vnp_Command     = "pay";
$vnp_CurrCode    = "VND"; 
$vnp_Locale      = "vn";        // Tiếng Việt
$vnp_OrderType   = "other";     // Loại đơn hàng (có thể thay thành 'fashion' nếu muốn)

//Lưu ý: Nếu chạy trên hosting thật sau này, hãy đổi http://localhost/... thành https://tenmien.com/...
?>
