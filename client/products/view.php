<?php
require_once '../../includes/config.php';
require_once '../../includes/auth.php';

if (!isset($_GET['id'])) {
    header("Location: index.php");
    exit();
}

$productId = (int)$_GET['id'];
$stmt = $pdo->prepare("SELECT * FROM products WHERE id = ?");
$stmt->execute([$productId]);
$product = $stmt->fetch();

if (!$product) {
    header("Location: index.php");
    exit();
}

$pageTitle = $product['name'];
include '../../includes/header.php';
?>

<div class="container py-5">
    <div class="row">
        <div class="col-md-6">
            <img src="<?php echo APP_URL; ?>/assets/images/products/<?php echo $product['image'] ?? 'default.jpg'; ?>" 
                 class="img-fluid rounded" alt="<?php echo htmlspecialchars($product['name']); ?>">
        </div>
        <div class="col-md-6">
            <h2><?php echo htmlspecialchars($product['name']); ?></h2>
            <p class="text-muted">Product ID: <?php echo $product['id']; ?></p>
            <h4 class="my-3">KES <?php echo number_format($product['price'], 2); ?></h4>
            
            <?php if ($product['stock'] > 0): ?>
                <p class="text-success">In Stock (<?php echo $product['stock']; ?> available)</p>
            <?php else: ?>
                <p class="text-danger">Out of Stock</p>
            <?php endif; ?>
            
            <div class="mb-4">
                <h5>Description</h5>
                <p><?php echo nl2br(htmlspecialchars($product['description'])); ?></p>
            </div>
            
            <?php if (isLoggedIn()): ?>
                <form class="add-to-cart-form">
                    <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
                    <div class="row mb-3">
                        <div class="col-md-3">
                            <label for="quantity" class="form-label">Quantity</label>
                            <input type="number" class="form-control" id="quantity" name="quantity" value="1" min="1" 
                                   max="<?php echo $product['stock']; ?>" <?php echo $product['stock'] == 0 ? 'disabled' : ''; ?>>
                        </div>
                    </div>
                    
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary btn-lg flex-grow-1 <?php echo $product['stock'] == 0 ? 'disabled' : ''; ?>">
                            <i class="fas fa-cart-plus"></i> Add to Cart
                        </button>
                        
                        <?php if ($product['stock'] > 0): ?>
                            <button type="button" class="btn btn-success btn-lg buy-now" data-product-id="<?php echo $product['id']; ?>">
                                Buy Now
                            </button>
                        <?php endif; ?>
                    </div>
                </form>
            <?php else: ?>
                <div class="alert alert-info">
                    Please <a href="../login.php">login</a> to purchase this product.
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include '../../includes/footer.php'; ?>