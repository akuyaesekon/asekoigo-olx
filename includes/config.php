<?php
ini_set('session.cookie_httponly', 1);
ini_set('session.cookie_secure', isset($_SERVER['HTTPS']));
ini_set('session.use_strict_mode', 1);
session_start();
// Application configuration
define('APP_NAME', 'AsekosiGo-Olx');
define('APP_URL', 'http://localhost/asekosigo-olx');
define('CURRENCY', 'KES');

// Paystack configuration
define('PAYSTACK_PUBLIC_KEY', 'pk_test_4cbf2ec3215a3f315a9964b0cab31f0e4c5faf33');
define('PAYSTACK_SECRET_KEY', 'sk_test_6b97fc2a19e0d59e210e5ac0540c1987f52c0447');
define('PAYSTACK_PAYMENT_URL', 'https://api.paystack.co/transaction/initialize');

// M-Pesa configuration
define('MPESA_CONSUMER_KEY', 'PuS5mk2fVH1QXUoHQhT0vN0fMX8DjO9LgW6RHGr3NTlPuaUg');
define('MPESA_CONSUMER_SECRET', 'BD4oAFoe2nI2zZsxwtsxAW3VUtAiZu3LdViKCcQcGT6CXXxjBc2anbw4AVI3k6dZ');
define('MPESA_PASSKEY', 'bfb279f9aa9bdbcf158e97dd71a467cd2e0c893059b10f78e6b72ada1ed2c919');
define('MPESA_BUSINESS_SHORTCODE', '174379');
define('MPESA_TRANSACTION_TYPE', 'CustomerPayBillOnline');
define('MPESA_CALLBACK_URL', APP_URL.'/api/mpesa_callback.php');
define('MPESA_STK_PUSH_URL', 'https://sandbox.safaricom.co.ke/mpesa/stkpush/v1/processrequest');

// Database configuration
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'asekosigo_olx');

// Start session
session_start();

// Error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);
?>