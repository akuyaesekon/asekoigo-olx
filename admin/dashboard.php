<?php
require_once '../includes/config.php';
require_once '../includes/auth.php';
requireAdmin();

try {
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $productsCount = $pdo->query("SELECT COUNT(*) FROM products")->fetchColumn();
    $usersCount = $pdo->query("SELECT COUNT(*) FROM users WHERE is_admin = 0")->fetchColumn();
    $ordersCount = $pdo->query("SELECT COUNT(*) FROM orders")->fetchColumn();
    $revenue = $pdo->query("SELECT COALESCE(SUM(total_amount), 0) FROM orders WHERE payment_status = 'paid'")->fetchColumn();

    $recentOrders = $pdo->query("SELECT o.id, o.total_amount, o.status, o.created_at, u.username FROM orders o JOIN users u ON o.user_id = u.id ORDER BY o.created_at DESC LIMIT 5")->fetchAll();

} catch (PDOException $e) {
    die("Database error: " . $e->getMessage());
}

$pageTitle = 'Admin Dashboard';
include '../includes/header.php';
?>

<div class="container py-4">
    <h2 class="mb-4">Dashboard</h2>

    <div class="row mb-4">
        <!-- Cards -->
        <?php
        $stats = [
            ['title' => 'Products', 'value' => $productsCount, 'link' => 'products/index.php', 'color' => 'primary'],
            ['title' => 'Customers', 'value' => $usersCount, 'link' => '#', 'color' => 'success'],
            ['title' => 'Orders', 'value' => $ordersCount, 'link' => 'orders/index.php', 'color' => 'info'],
            ['title' => 'Revenue', 'value' => 'KES ' . number_format($revenue, 2), 'link' => '', 'color' => 'warning text-dark']
        ];

        foreach ($stats as $stat): ?>
            <div class="col-md-3">
                <div class="card bg-<?= $stat['color'] ?> text-white">
                    <div class="card-body">
                        <h5 class="card-title"><?= $stat['title'] ?></h5>
                        <h2 class="card-text"><?= $stat['value'] ?></h2>
                        <?php if ($stat['link']): ?>
                            <a href="<?= $stat['link'] ?>" class="text-white">View</a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <!-- Orders Table -->
    <div class="row">
        <div class="col-md-8">
            <div class="card mb-4">
                <div class="card-header"><h5>Recent Orders</h5></div>
                <div class="card-body">
                    <?php if (empty($recentOrders)): ?>
                        <div class="alert alert-info">No recent orders.</div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-sm">
                                <thead>
                                    <tr>
                                        <th>Order ID</th><th>Customer</th><th>Date</th><th>Amount</th><th>Status</th><th></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($recentOrders as $order): ?>
                                        <tr>
                                            <td>#<?= $order['id'] ?></td>
                                            <td><?= htmlspecialchars($order['username']) ?></td>
                                            <td><?= date('M d, Y', strtotime($order['created_at'])) ?></td>
                                            <td>KES <?= number_format($order['total_amount'], 2) ?></td>
                                            <td>
                                                <span class="badge 
                                                    <?= match($order['status']) {
                                                        'pending' => 'bg-warning',
                                                        'processing' => 'bg-info',
                                                        'shipped' => 'bg-primary',
                                                        'delivered' => 'bg-success',
                                                        default => 'bg-secondary'
                                                    } ?>">
                                                    <?= ucfirst($order['status']) ?>
                                                </span>
                                            </td>
                                            <td><a href="orders/view.php?id=<?= $order['id'] ?>" class="btn btn-sm btn-outline-primary">View</a></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Actions -->
        <div class="col-md-4">
            <div class="card">
                <div class="card-header"><h5>Quick Actions</h5></div>
                <div class="card-body d-grid gap-2">
                    <a href="products/add.php" class="btn btn-primary">Add Product</a>
                    <a href="products/index.php" class="btn btn-outline-primary">Manage Products</a>
                    <a href="orders/index.php" class="btn btn-outline-primary">View Orders</a>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
