<?php
require_once '../includes/config.php';
require_once '../includes/auth.php';
require_once '../includes/db.php';

// Get callback data
$callbackData = file_get_contents('php://input');
$data = json_decode($callbackData, true);

if ($data && isset($data['Body']['stkCallback']['ResultCode'])) {
    $resultCode = $data['Body']['stkCallback']['ResultCode'];
    $merchantRequestId = $data['Body']['stkCallback']['MerchantRequestID'];
    $checkoutRequestId = $data['Body']['stkCallback']['CheckoutRequestID'];
    
    if ($resultCode == 0) {
        // Payment was successful
        $callbackMetadata = $data['Body']['stkCallback']['CallbackMetadata']['Item'];
        
        $amount = null;
        $mpesaReceiptNumber = null;
        $phoneNumber = null;
        
        foreach ($callbackMetadata as $item) {
            if ($item['Name'] == 'Amount') {
                $amount = $item['Value'];
            } elseif ($item['Name'] == 'MpesaReceiptNumber') {
                $mpesaReceiptNumber = $item['Value'];
            } elseif ($item['Name'] == 'PhoneNumber') {
                $phoneNumber = $item['Value'];
            }
        }
        
        // Extract order ID from reference
        $reference = $data['Body']['stkCallback']['MerchantRequestID'];
        preg_match('/ASKO_(\d+)_/', $reference, $matches);
        $orderId = $matches[1] ?? 0;
        
        if ($orderId) {
            // Update order payment status
            $stmt = $pdo->prepare("
                UPDATE orders 
                SET payment_status = 'paid', 
                    payment_reference = ?
                WHERE id = ?
            ");
            $stmt->execute([$mpesaReceiptNumber, $orderId]);
            
            // Log the successful payment
            file_put_contents('mpesa_payments.log', date('Y-m-d H:i:s') . " - Order #$orderId paid via M-Pesa. Receipt: $mpesaReceiptNumber\n", FILE_APPEND);
        }
    } else {
        // Payment failed
        $errorMessage = $data['Body']['stkCallback']['ResultDesc'] ?? 'Unknown error';
        file_put_contents('mpesa_errors.log', date('Y-m-d H:i:s') . " - Payment failed. Error: $errorMessage\n", FILE_APPEND);
    }
}

// Send response to M-Pesa
header('Content-Type: application/json');
echo json_encode(['ResultCode' => 0, 'ResultDesc' => 'Callback processed successfully']);
?>