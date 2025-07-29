<?php
require_once '../../../includes/config.php';
require_once '../../../includes/auth.php';
requireAdmin();

$pageTitle = 'Manage Orders';
include '../../../includes/header.php';

// Get all orders with user information
$stmt = $pdo->query("
    SELECT o.id, o.total_amount, o.status, o.payment_status, o.created_at, u.username 
    FROM orders o
    JOIN users u ON o.user_id = u.id
    ORDER BY o.created_at DESC
");
$orders = $stmt->fetchAll();
?>

<div class="container py-4">
    <h2 class="mb-4">Manage Orders</h2>
    
    <?php if (empty($orders)): ?>
        <div class="alert alert-info">No orders found.</div>
    <?php else: ?>
        <div class="table-responsive">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>Order ID</th>
                        <th>Customer</th>
                        <th>Date</th>
                        <th>Amount</th>
                        <th>Status</th>
                        <th>Payment</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($orders as $order): ?>
                        <tr>
                            <td>#<?php echo $order['id']; ?></td>
                            <td><?php echo htmlspecialchars($order['username']); ?></td>
                            <td><?php echo date('M d, Y', strtotime($order['created_at'])); ?></td>
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
                                    <i class="fas fa-eye"></i> View
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>

<?php include '../../../includes/footer.php'; ?>