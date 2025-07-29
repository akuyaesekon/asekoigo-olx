<?php
require_once '../includes/config.php';
require_once '../includes/auth.php';
require_once '../includes/db.php';

if (!isset($_GET['order_id']) || !isset($_GET['amount']) || !isset($_GET['phone'])) {
    header("Location: ../../client/cart/checkout.php");
    exit();
}

$orderId = (int)$_GET['order_id'];
$amount = (float)$_GET['amount'];
$phone = $_GET['phone'];

// Format phone number (remove leading 0 and add country code)
$phone = preg_replace('/^0/', '254', $phone);

// Generate access token
$credentials = base64_encode(MPESA_CONSUMER_KEY . ':' . MPESA_CONSUMER_SECRET);
$ch = curl_init('https://sandbox.safaricom.co.ke/oauth/v1/generate?grant_type=client_credentials');
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Authorization: Basic ' . $credentials]);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$response = curl_exec($ch);
curl_close($ch);

$result = json_decode($response, true);
$accessToken = $result['access_token'] ?? '';

if (empty($accessToken)) {
    $_SESSION['error'] = 'Failed to get M-Pesa access token';
    header("Location: ../../client/cart/checkout.php");
    exit();
}

// Generate timestamp
$timestamp = date('YmdHis');

// Generate password
$password = base64_encode(MPESA_BUSINESS_SHORTCODE . MPESA_PASSKEY . $timestamp);

// Prepare STK push request
$stkPushUrl = 'https://sandbox.safaricom.co.ke/mpesa/stkpush/v1/processrequest';
$callbackUrl = MPESA_CALLBACK_URL;
$reference = 'ASKO_' . $orderId . '_' . time();
$transactionDesc = 'AsekosiGo Order #' . $orderId;

$stkPushHeader = [
    'Content-Type: application/json',
    'Authorization: Bearer ' . $accessToken
];

$stkPushData = [
    'BusinessShortCode' => MPESA_BUSINESS_SHORTCODE,
    'Password' => $password,
    'Timestamp' => $timestamp,
    'TransactionType' => MPESA_TRANSACTION_TYPE,
    'Amount' => $amount,
    'PartyA' => $phone,
    'PartyB' => MPESA_BUSINESS_SHORTCODE,
    'PhoneNumber' => $phone,
    'CallBackURL' => $callbackUrl,
    'AccountReference' => $reference,
    'TransactionDesc' => $transactionDesc
];

// Initiate STK push
$ch = curl_init($stkPushUrl);
curl_setopt($ch, CURLOPT_HTTPHEADER, $stkPushHeader);
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($stkPushData));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
$response = curl_exec($ch);
curl_close($ch);

$result = json_decode($response, true);

if (isset($result['ResponseCode']) && $result['ResponseCode'] == '0') {
    // Update order with payment reference
    $stmt = $pdo->prepare("UPDATE orders SET payment_reference = ? WHERE id = ?");
    $stmt->execute([$reference, $orderId]);
    
    $_SESSION['success'] = 'M-Pesa STK push initiated. Please check your phone to complete payment.';
    header("Location: ../../client/orders/view.php?id=$orderId");
    exit();
} else {
    $_SESSION['error'] = 'Failed to initiate M-Pesa payment. Please try again.';
    header("Location: ../../client/cart/checkout.php");
    exit();
}
?>