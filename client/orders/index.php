<?php
require_once '../../includes/config.php';
require_once '../../includes/auth.php';
requireLogin();

$pageTitle = 'My Orders';
include '../../includes/header.php';

// Get user's orders
$stmt = $pdo->prepare("
    SELECT o.id, o.total_amount, o.status, o.payment_status, o.created_at, 
           COUNT(oi.id) as item_count
    FROM orders o
    LEFT JOIN order_items oi ON o.id = oi.order_id
    WHERE o.user_id = ?
    GROUP BY o.id
    ORDER BY o.created_at DESC
");
$stmt->execute([$_SESSION['user_id']]);
$orders = $stmt->fetchAll();
?>

<div class="container py-5">
    <h2 class="mb-4">My Orders</h2>
    
    <?php if (empty($orders)): ?>
        <div class="alert alert-info">
            You haven't placed any orders yet. <a href="../products/index.php">Browse products</a> to get started.
        </div>
    <?php else: ?>
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>Order ID</th>
                        <th>Date</th>
                        <th>Items</th>
                        <th>Total</th>
                        <th>Status</th>
                        <th>Payment</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($orders as $order): ?>
                        <tr>
                            <td>#<?php echo $order['id']; ?></td>
                            <td><?php echo date('M d, Y', strtotime($order['created_at'])); ?></td>
                            <td><?php echo $order['item_count']; ?></td>
                            <td>KES <?php echo number_format($order['total_amount'], 2); ?></td>
                            <td>
                                <span class="badge 
                                    <?php echo $order['status'] == 'pending' ? 'bg-warning' : 
                                           ($order['status'] == 'processing' ? 'bg-info' : 
                                           ($order['status'] == 'shipped' ? 'bg-primary' : 
                                           ($order['status'] == 'delivered' ? 'bg-success' : 'bg-danger'))); ?>">
                                    <?php echo ucfirst($order['status']); ?>
                                </span>
                            </td>
                            <td>
                                <span class="badge 
                                    <?php echo $order['payment_status'] == 'paid' ? 'bg-success' : 
                                           ($order['payment_status'] == 'pending' ? 'bg-warning' : 'bg-danger'); ?>">
                                    <?php echo ucfirst($order['payment_status']); ?>
                                </span>
                            </td>
                            <td>
                                <a href="view.php?id=<?php echo $order['id']; ?>" class="btn btn-sm btn-outline-primary">
                                    View Details
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>

<?php include '../../includes/footer.php'; ?>