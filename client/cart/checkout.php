<?php
require_once '../../includes/config.php';
require_once '../../includes/auth.php';
requireLogin();

$pageTitle = 'Checkout';
include '../../includes/header.php';

// Get cart items with product details
$stmt = $pdo->prepare("
    SELECT p.id, p.name, p.price, p.image, c.quantity, (p.price * c.quantity) as total 
    FROM cart c 
    JOIN products p ON c.product_id = p.id 
    WHERE c.user_id = ?
");
$stmt->execute([$_SESSION['user_id']]);
$cartItems = $stmt->fetchAll();

// Calculate cart total
$cartTotal = 0;
foreach ($cartItems as $item) {
    $cartTotal += $item['total'];
}

// Get user details
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch();

// Handle checkout submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Validate payment method
    $paymentMethod = $_POST['payment_method'] ?? '';
    if (!in_array($paymentMethod, ['paystack', 'mpesa'])) {
        $error = 'Please select a valid payment method';
    } elseif (empty($cartItems)) {
        $error = 'Your cart is empty';
    } else {
        try {
            $pdo->beginTransaction();
            
            // Create order
            $stmt = $pdo->prepare("
                INSERT INTO orders (user_id, total_amount, payment_method, status)
                VALUES (?, ?, ?, 'pending')
            ");
            $stmt->execute([$_SESSION['user_id'], $cartTotal, $paymentMethod]);
            $orderId = $pdo->lastInsertId();
            
            // Add order items
            foreach ($cartItems as $item) {
                $stmt = $pdo->prepare("
                    INSERT INTO order_items (order_id, product_id, quantity, price)
                    VALUES (?, ?, ?, ?)
                ");
                $stmt->execute([$orderId, $item['id'], $item['quantity'], $item['price']]);
                
                // Update product stock
                $stmt = $pdo->prepare("UPDATE products SET stock = stock - ? WHERE id = ?");
                $stmt->execute([$item['quantity'], $item['id']]);
            }
            
            // Clear cart
            $stmt = $pdo->prepare("DELETE FROM cart WHERE user_id = ?");
            $stmt->execute([$_SESSION['user_id']]);
            
            // Update cart count in session
            $_SESSION['cart_count'] = 0;
            
            $pdo->commit();
            
            // Process payment based on selected method
            if ($paymentMethod == 'paystack') {
                // Redirect to Paystack payment page
                header("Location: ../../api/paystack.php?order_id=$orderId&amount=$cartTotal&email=" . urlencode($user['email']));
                exit();
            } elseif ($paymentMethod == 'mpesa') {
                // Process M-Pesa STK Push
                header("Location: ../../api/mpesa.php?order_id=$orderId&amount=$cartTotal&phone=" . urlencode($user['phone']));
                exit();
            }
        } catch (Exception $e) {
            $pdo->rollBack();
            $error = 'An error occurred during checkout. Please try again.';
        }
    }
}
?>

<div class="container py-5">
    <div class="row">
        <div class="col-md-8">
            <div class="card mb-4">
                <div class="card-header">
                    <h4>Checkout</h4>
                </div>
                <div class="card-body">
                    <?php if (empty($cartItems)): ?>
                        <div class="alert alert-info">
                            Your cart is empty. <a href="../products/index.php">Browse products</a> to add items.
                        </div>
                    <?php else: ?>
                        <?php if (isset($error)): ?>
                            <div class="alert alert-danger"><?php echo $error; ?></div>
                        <?php endif; ?>
                        
                        <h5 class="mb-3">Order Summary</h5>
                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Product</th>
                                        <th>Price</th>
                                        <th>Quantity</th>
                                        <th>Total</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($cartItems as $item): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($item['name']); ?></td>
                                            <td>KES <?php echo number_format($item['price'], 2); ?></td>
                                            <td><?php echo $item['quantity']; ?></td>
                                            <td>KES <?php echo number_format($item['total'], 2); ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                                <tfoot>
                                    <tr>
                                        <td colspan="3" class="text-end"><strong>Total:</strong></td>
                                        <td><strong>KES <?php echo number_format($cartTotal, 2); ?></strong></td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                        
                        <form method="POST">
                            <h5 class="mt-4 mb-3">Payment Method</h5>
                            <div class="form-check mb-2">
                                <input class="form-check-input" type="radio" name="payment_method" id="paystack" value="paystack" checked>
                                <label class="form-check-label" for="paystack">
                                    Paystack (Card Payment)
                                </label>
                            </div>
                            <div class="form-check mb-3">
                                <input class="form-check-input" type="radio" name="payment_method" id="mpesa" value="mpesa">
                                <label class="form-check-label" for="mpesa">
                                    M-Pesa STK Push
                                </label>
                            </div>
                            
                            <h5 class="mt-4 mb-3">Shipping Information</h5>
                            <div class="mb-3">
                                <label class="form-label">Name</label>
                                <input type="text" class="form-control" value="<?php echo htmlspecialchars($user['username']); ?>" readonly>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Email</label>
                                <input type="email" class="form-control" value="<?php echo htmlspecialchars($user['email']); ?>" readonly>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Phone</label>
                                <input type="tel" class="form-control" value="<?php echo htmlspecialchars($user['phone']); ?>" readonly>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Address</label>
                                <textarea class="form-control" readonly><?php echo htmlspecialchars($user['address']); ?></textarea>
                            </div>
                            
                            <div class="d-grid mt-4">
                                <button type="submit" class="btn btn-primary btn-lg">Complete Order</button>
                            </div>
                        </form>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        
        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h5>Order Summary</h5>
                </div>
                <div class="card-body">
                    <ul class="list-group list-group-flush">
                        <?php foreach ($cartItems as $item): ?>
                            <li class="list-group-item d-flex justify-content-between">
                                <span><?php echo htmlspecialchars($item['name']); ?> × <?php echo $item['quantity']; ?></span>
                                <span>KES <?php echo number_format($item['total'], 2); ?></span>
                            </li>
                        <?php endforeach; ?>
                        <li class="list-group-item d-flex justify-content-between fw-bold">
                            <span>Total</span>
                            <span>KES <?php echo number_format($cartTotal, 2); ?></span>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../../includes/footer.php'; ?>