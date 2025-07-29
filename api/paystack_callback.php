<?php
require_once '../includes/config.php';
require_once '../includes/auth.php';
require_once '../includes/db.php';

// Retrieve the transaction reference from the callback
$reference = $_GET['reference'] ?? '';

if (empty($reference)) {
    die('No reference supplied');
}

// Verify the transaction with Paystack
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, "https://api.paystack.co/transaction/verify/" . rawurlencode($reference));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    "Authorization: Bearer " . PAYSTACK_SECRET_KEY
]);

$response = curl_exec($ch);
curl_close($ch);

if ($response) {
    $result = json_decode($response, true);
    
    if ($result && $result['status'] && $result['data']['status'] == 'success') {
        $orderId = $result['data']['metadata']['order_id'];
        $amountPaid = $result['data']['amount'] / 100; // Convert back to KES
        
        // Verify that the amount paid matches the order amount
        $stmt = $pdo->prepare("SELECT total_amount FROM orders WHERE id = ?");
        $stmt->execute([$orderId]);
        $order = $stmt->fetch();
        
        if ($order && $order['total_amount'] == $amountPaid) {
            // Update order payment status
            $stmt = $pdo->prepare("UPDATE orders SET payment_status = 'paid' WHERE id = ?");
            $stmt->execute([$orderId]);
            
            // Redirect to success page
            header("Location: ../../client/orders/view.php?id=$orderId");
            exit();
        }
    }
}

// If we get here, payment verification failed
$_SESSION['error'] = 'Payment verification failed. Please contact support.';
header("Location: ../../client/orders/index.php");
exit();
?>