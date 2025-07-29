<?php
require_once '../includes/config.php';
require_once '../includes/auth.php';

$pageTitle = 'Home';
include '../includes/header.php';
?>

<div class="hero-section bg-light py-5 mb-5">
    <div class="container text-center">
        <h1 class="display-4">Welcome to <?php echo APP_NAME; ?></h1>
        <p class="lead">Discover amazing products at affordable prices</p>
        <a href="products/index.php" class="btn btn-primary btn-lg">Browse Products</a>
    </div>
</div>

<div class="container">
    <h2 class="mb-4">Featured Products</h2>
    <div class="row">
        <?php
        // Fetch featured products
        $stmt = $pdo->query("SELECT * FROM products ORDER BY RAND() LIMIT 4");
        while ($product = $stmt->fetch()):
        ?>
        <div class="col-md-3 mb-4">
            <div class="card h-100">
                <img src="<?php echo APP_URL; ?>/assets/images/products/<?php echo $product['image'] ?? 'default.jpg'; ?>" 
                     class="card-img-top" alt="<?php echo htmlspecialchars($product['name']); ?>">
                <div class="card-body">
                    <h5 class="card-title"><?php echo htmlspecialchars($product['name']); ?></h5>
                    <p class="card-text">KES <?php echo number_format($product['price'], 2); ?></p>
                    <a href="products/view.php?id=<?php echo $product['id']; ?>" class="btn btn-primary">View Details</a>
                </div>
            </div>
        </div>
        <?php endwhile; ?>
    </div>
</div>

<?php include '../includes/footer.php'; ?>