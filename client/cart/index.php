<?php
require_once '../../includes/config.php';
require_once '../../includes/auth.php';
requireLogin();

$pageTitle = 'Shopping Cart';
include '../../includes/header.php';

// Handle remove item from cart
if (isset($_GET['remove']) && is_numeric($_GET['remove'])) {
    $productId = (int)$_GET['remove'];
    $stmt = $pdo->prepare("DELETE FROM cart WHERE user_id = ? AND product_id = ?");
    $stmt->execute([$_SESSION['user_id'], $productId]);
    
    // Update cart count in session
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM cart WHERE user_id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $_SESSION['cart_count'] = $stmt->fetchColumn();
    
    header("Location: index.php");
    exit();
}

// Handle quantity update
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_cart'])) {
    foreach ($_POST['quantities'] as $productId => $quantity) {
        $productId = (int)$productId;
        $quantity = (int)$quantity;
        
        if ($quantity <= 0) {
            // Remove item if quantity is 0 or less
            $stmt = $pdo->prepare("DELETE FROM cart WHERE user_id = ? AND product_id = ?");
            $stmt->execute([$_SESSION['user_id'], $productId]);
        } else {
            // Update quantity
            $stmt = $pdo->prepare("UPDATE cart SET quantity = ? WHERE user_id = ? AND product_id = ?");
            $stmt->execute([$quantity, $_SESSION['user_id'], $productId]);
        }
    }
    
    header("Location: index.php");
    exit();
}

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
?>

<div class="container py-5">
    <h2 class="mb-4">Shopping Cart</h2>
    
    <?php if (empty($cartItems)): ?>
        <div class="alert alert-info">
            Your cart is empty. <a href="../products/index.php">Browse products</a> to add items.
        </div>
    <?php else: ?>
        <form method="POST">
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Product</th>
                            <th>Price</th>
                            <th>Quantity</th>
                            <th>Total</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($cartItems as $item): ?>
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <img src="<?php echo APP_URL; ?>/assets/images/products/<?php echo $item['image'] ?? 'default.jpg'; ?>" 
                                             alt="<?php echo htmlspecialchars($item['name']); ?>" width="60" class="me-3">
                                        <div>
                                            <h6 class="mb-0"><?php echo htmlspecialchars($item['name']); ?></h6>
                                            <small class="text-muted">Product ID: <?php echo $item['id']; ?></small>
                                        </div>
                                    </div>
                                </td>
                                <td>KES <?php echo number_format($item['price'], 2); ?></td>
                                <td>
                                    <input type="number" name="quantities[<?php echo $item['id']; ?>]" 
                                           value="<?php echo $item['quantity']; ?>" min="1" class="form-control" style="width: 70px;">
                                </td>
                                <td>KES <?php echo number_format($item['total'], 2); ?></td>
                                <td>
                                    <a href="index.php?remove=<?php echo $item['id']; ?>" class="btn btn-sm btn-danger">
                                        <i class="fas fa-trash"></i>
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                    <tfoot>
                        <tr>
                            <td colspan="3" class="text-end"><strong>Cart Total:</strong></td>
                            <td colspan="2"><strong>KES <?php echo number_format($cartTotal, 2); ?></strong></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
            
            <div class="d-flex justify-content-between">
                <a href="../products/index.php" class="btn btn-outline-primary">Continue Shopping</a>
                <div class="d-flex gap-2">
                    <button type="submit" name="update_cart" class="btn btn-secondary">Update Cart</button>
                    <a href="checkout.php" class="btn btn-primary">Proceed to Checkout</a>
                </div>
            </div>
        </form>
    <?php endif; ?>
</div>

<?php include '../../includes/footer.php'; ?>