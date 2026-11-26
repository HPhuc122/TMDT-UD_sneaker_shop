<?php
// =====================================================
// ZALOPAY SANDBOX CONFIG
// Credentials sandbox public — dùng được luôn, không cần đăng ký
// =====================================================

define('ZALOPAY_APP_ID',   getenv('ZALOPAY_APP_ID') ?: 2553);
define('ZALOPAY_KEY1',     getenv('ZALOPAY_KEY1') ?: 'PcY4iZIKFCIdgZvA6ueMcMHHUbRLYjPL');
define('ZALOPAY_KEY2',     getenv('ZALOPAY_KEY2') ?: 'kLtgPl8HHhfvMuDHPwKfgfsY4Vu/kms31PDP4Czfts=');
define('ZALOPAY_ENDPOINT', 'https://sb-openapi.zalopay.vn/v2/create');

// URL public dùng cho redirect/callback khi chạy local qua Ngrok.
define('APP_URL', getenv('APP_URL') ?: 'https://uninfusive-audry-reptilelike.ngrok-free.dev/ktpm-sang-thu5');

// APP_URL đã chứa thư mục project; APP_PATH chỉ trỏ tới module thanh toán.
$default_path = '/zalo_pay';
define('APP_PATH', getenv('APP_PATH') ?: $default_path);

define('ZALOPAY_RETURN_URL',   APP_URL . APP_PATH . '/zalopay_return.php');
define('ZALOPAY_CALLBACK_URL', APP_URL . APP_PATH . '/zalopay_callback.php');
