<?php
require_once '../includes/config.php';
require_once '../includes/auth.php';

if (!isset($_GET['order_id']) || !isset($_GET['amount']) || !isset($_GET['email'])) {
    header("Location: ../../client/cart/checkout.php");
    exit();
}

$orderId = (int)$_GET['order_id'];
$amount = (float)$_GET['amount'] * 100; // Paystack uses amount in kobo
$email = $_GET['email'];

// Initialize Paystack transaction
$url = PAYSTACK_PAYMENT_URL;
$fields = [
    'email' => $email,
    'amount' => $amount,
    'reference' => 'ASKO_' . $orderId . '_' . time(),
    'callback_url' => APP_URL . '/api/paystack_callback.php',
    'metadata' => [
        'order_id' => $orderId,
        'custom_fields' => [
            [
                'display_name' => "Order ID",
                'variable_name' => "order_id",
                'value' => $orderId
            ]
        ]
    ]
];

$fields_string = http_build_query($fields);

// Open connection
$ch = curl_init();

// Set the url, number of POST vars, POST data
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, $fields_string);
curl_setopt($ch, CURLOPT_HTTPHEADER, array(
    "Authorization: Bearer " . PAYSTACK_SECRET_KEY,
    "Cache-Control: no-cache",
));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

// Execute post
$result = curl_exec($ch);
if (curl_errno($ch)) {
    die('Error:' . curl_error($ch));
}
curl_close($ch);

// Decode response
$response = json_decode($result, true);
if ($response && $response['status'] && isset($response['data']['authorization_url'])) {
    // Update order with payment reference
    $stmt = $pdo->prepare("UPDATE orders SET payment_reference = ? WHERE id = ?");
    $stmt->execute([$fields['reference'], $orderId]);
    
    // Redirect to Paystack payment page
    header("Location: " . $response['data']['authorization_url']);
    exit();
} else {
    // Payment initialization failed
    $_SESSION['error'] = 'Failed to initialize payment. Please try again.';
    header("Location: ../../client/cart/checkout.php");
    exit();
}
?>