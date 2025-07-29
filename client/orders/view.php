<?php
require_once '../../includes/config.php';
require_once '../../includes/auth.php';
requireLogin();

if (!isset($_GET['id'])) {
    header("Location: index.php");
    exit();
}

$orderId = (int)$_GET['id'];

// Get order details
$stmt = $pdo->prepare("
    SELECT o.*, u.username, u.email, u.phone, u.address
    FROM orders o
    JOIN users u ON o.user_id = u.id
    WHERE o.id = ? AND o.user_id = ?
");
$stmt->execute([$orderId, $_SESSION['user_id']]);
$order = $stmt->fetch();

if (!$order) {
    header("Location: index.php");
    exit();
}

// Get order items
$stmt = $pdo->prepare("
    SELECT oi.*, p.name, p.image
    FROM order_items oi
    JOIN products p ON oi.product_id = p.id
    WHERE oi.order_id = ?
");
$stmt->execute([$orderId]);
$orderItems = $stmt->fetchAll();

$pageTitle = 'Order #' . $orderId;
include '../../includes/header.php';
?>

<div class="container py-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Order #<?php echo $orderId; ?></h2>
        <a href="index.php" class="btn btn-outline-secondary">Back to Orders</a>
    </div>
    
    <div class="row">
        <div class="col-md-8">
            <div class="card mb-4">
                <div class="card-header">
                    <h5>Order Items</h5>
                </div>
                <div class="card-body">
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
                                <?php foreach ($orderItems as $item): ?>
                                    <tr>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <img src="<?php echo APP_URL; ?>/assets/images/products/<?php echo $item['image'] ?? 'default.jpg'; ?>" 
                                                     alt="<?php echo htmlspecialchars($item['name']); ?>" width="60" class="me-3">
                                                <div>
                                                    <h6 class="mb-0"><?php echo htmlspecialchars($item['name']); ?></h6>
                                                    <small class="text-muted">Product ID: <?php echo $item['product_id']; ?></small>
                                                </div>
                                            </div>
                                        </td>
                                        <td>KES <?php echo number_format($item['price'], 2); ?></td>
                                        <td><?php echo $item['quantity']; ?></td>
                                        <td>KES <?php echo number_format($item['price'] * $item['quantity'], 2); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                            <tfoot>
                                <tr>
                                    <td colspan="3" class="text-end"><strong>Subtotal:</strong></td>
                                    <td><strong>KES <?php echo number_format($order['total_amount'], 2); ?></strong></td>
                                </tr>
                                <tr>
                                    <td colspan="3" class="text-end"><strong>Shipping:</strong></td>
                                    <td><strong>KES 0.00</strong></td>
                                </tr>
                                <tr>
                                    <td colspan="3" class="text-end"><strong>Total:</strong></td>
                                    <td><strong>KES <?php echo number_format($order['total_amount'], 2); ?></strong></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-4">
            <div class="card mb-4">
                <div class="card-header">
                    <h5>Order Summary</h5>
                </div>
                <div class="card-body">
                    <ul class="list-group list-group-flush">
                        <li class="list-group-item d-flex justify-content-between">
                            <span>Order ID</span>
                            <span>#<?php echo $orderId; ?></span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between">
                            <span>Date</span>
                            <span><?php echo date('M d, Y H:i', strtotime($order['created_at'])); ?></span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between">
                            <span>Status</span>
                            <span class="badge 
                                <?php echo $order['status'] == 'pending' ? 'bg-warning' : 
                                       ($order['status'] == 'processing' ? 'bg-info' : 
                                       ($order['status'] == 'shipped' ? 'bg-primary' : 
                                       ($order['status'] == 'delivered' ? 'bg-success' : 'bg-danger'))); ?>">
                                <?php echo ucfirst($order['status']); ?>
                            </span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between">
                            <span>Payment</span>
                            <span class="badge 
                                <?php echo $order['payment_status'] == 'paid' ? 'bg-success' : 
                                       ($order['payment_status'] == 'pending' ? 'bg-warning' : 'bg-danger'); ?>">
                                <?php echo ucfirst($order['payment_status']); ?>
                            </span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between">
                            <span>Payment Method</span>
                            <span><?php echo ucfirst($order['payment_method']); ?></span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between fw-bold">
                            <span>Total</span>
                            <span>KES <?php echo number_format($order['total_amount'], 2); ?></span>
                        </li>
                    </ul>
                </div>
            </div>
            
            <div class="card">
                <div class="card-header">
                    <h5>Customer Details</h5>
                </div>
                <div class="card-body">
                    <ul class="list-group list-group-flush">
                        <li class="list-group-item">
                            <strong>Name:</strong> <?php echo htmlspecialchars($order['username']); ?>
                        </li>
                        <li class="list-group-item">
                            <strong>Email:</strong> <?php echo htmlspecialchars($order['email']); ?>
                        </li>
                        <li class="list-group-item">
                            <strong>Phone:</strong> <?php echo htmlspecialchars($order['phone']); ?>
                        </li>
                        <li class="list-group-item">
                            <strong>Address:</strong> <?php echo nl2br(htmlspecialchars($order['address'])); ?>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../../includes/footer.php'; ?>