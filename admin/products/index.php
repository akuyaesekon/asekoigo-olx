<?php
require_once '../../../includes/config.php';
require_once '../../../includes/auth.php';
requireAdmin();

$pageTitle = 'Manage Products';
include '../../../includes/header.php';

// Handle product deletion
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $productId = (int)$_GET['delete'];
    
    // Check if product exists in any order
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM order_items WHERE product_id = ?");
    $stmt->execute([$productId]);
    $orderCount = $stmt->fetchColumn();
    
    if ($orderCount > 0) {
        $_SESSION['error'] = 'Cannot delete product as it exists in orders.';
    } else {
        // Delete product from cart
        $stmt = $pdo->prepare("DELETE FROM cart WHERE product_id = ?");
        $stmt->execute([$productId]);
        
        // Delete product
        $stmt = $pdo->prepare("DELETE FROM products WHERE id = ?");
        if ($stmt->execute([$productId])) {
            $_SESSION['success'] = 'Product deleted successfully.';
        } else {
            $_SESSION['error'] = 'Failed to delete product.';
        }
    }
    
    header("Location: index.php");
    exit();
}

// Display success/error messages
if (isset($_SESSION['success'])) {
    echo '<div class="alert alert-success">' . $_SESSION['success'] . '</div>';
    unset($_SESSION['success']);
}

if (isset($_SESSION['error'])) {
    echo '<div class="alert alert-danger">' . $_SESSION['error'] . '</div>';
    unset($_SESSION['error']);
}

// Get all products
$stmt = $pdo->query("SELECT * FROM products ORDER BY created_at DESC");
$products = $stmt->fetchAll();
?>

<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Manage Products</h2>
        <a href="add.php" class="btn btn-primary">Add New Product</a>
    </div>
    
    <?php if (empty($products)): ?>
        <div class="alert alert-info">No products found.</div>
    <?php else: ?>
        <div class="table-responsive">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Image</th>
                        <th>Name</th>
                        <th>Price</th>
                        <th>Stock</th>
                        <th>Created</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($products as $product): ?>
                        <tr>
                            <td><?php echo $product['id']; ?></td>
                            <td>
                                <img src="<?php echo APP_URL; ?>/assets/images/products/<?php echo $product['image'] ?? 'default.jpg'; ?>" 
                                     alt="<?php echo htmlspecialchars($product['name']); ?>" width="50">
                            </td>
                            <td><?php echo htmlspecialchars($product['name']); ?></td>
                            <td>KES <?php echo number_format($product['price'], 2); ?></td>
                            <td><?php echo $product['stock']; ?></td>
                            <td><?php echo date('M d, Y', strtotime($product['created_at'])); ?></td>
                            <td>
                                <a href="edit.php?id=<?php echo $product['id']; ?>" class="btn btn-sm btn-outline-primary">
                                    <i class="fas fa-edit"></i> Edit
                                </a>
                                <a href="index.php?delete=<?php echo $product['id']; ?>" class="btn btn-sm btn-outline-danger" 
                                   onclick="return confirm('Are you sure you want to delete this product?')">
                                    <i class="fas fa-trash"></i> Delete
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