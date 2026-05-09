<?php
// =====================================================
// ZALOPAY SANDBOX CONFIG
// Credentials sandbox public — dùng được luôn, không cần đăng ký
// =====================================================

define('ZALOPAY_APP_ID',   getenv('ZALOPAY_APP_ID') ?: 2553);
define('ZALOPAY_KEY1',     getenv('ZALOPAY_KEY1') ?: 'PcY4iZIKFCIdgZvA6ueMcMHHUbRLYjPL');
define('ZALOPAY_KEY2',     getenv('ZALOPAY_KEY2') ?: 'kLtgPl8HHhfvMuDHPwKfgfsY4Vu/kms31PDP4Czfts=');
define('ZALOPAY_ENDPOINT', 'https://sb-openapi.zalopay.vn/v2/create');

// APP_URL cho Railway (VD: https://my-app.railway.app)
define('APP_URL', getenv('APP_URL') ?: 'http://localhost');

// APP_PATH: Để trống nếu deploy lên root của domain Railway
define('APP_PATH', getenv('APP_PATH') ?: '/TMDT-UD_sneaker_shop/zalo_pay');

define('ZALOPAY_RETURN_URL',   APP_URL . APP_PATH . '/zalopay_return.php');
define('ZALOPAY_CALLBACK_URL', APP_URL . APP_PATH . '/zalopay_callback.php');
