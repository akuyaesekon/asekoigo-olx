<?php
require_once '../../includes/config.php';
require_once '../../includes/auth.php';

$pageTitle = 'Products';
include '../../includes/header.php';

// Pagination
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$perPage = 12;
$offset = ($page - 1) * $perPage;

// Search functionality
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$where = '';
$params = [];

if ($search) {
    $where = "WHERE name LIKE :search OR description LIKE :search";
    $params[':search'] = "%$search%";
}

// Get total products for pagination
$stmt = $pdo->prepare("SELECT COUNT(*) FROM products $where");
$stmt->execute($params);
$totalProducts = $stmt->fetchColumn();
$totalPages = ceil($totalProducts / $perPage);

// Get products for current page
$stmt = $pdo->prepare("SELECT * FROM products $where ORDER BY created_at DESC LIMIT :offset, :perPage");
$params[':offset'] = $offset;
$params[':perPage'] = $perPage;

foreach ($params as $key => $value) {
    $stmt->bindValue($key, $value, is_int($value) ? PDO::PARAM_INT : PDO::PARAM_STR);
}
$stmt->execute();
$products = $stmt->fetchAll();
?>

<div class="container">
    <div class="row mb-4">
        <div class="col-md-6">
            <h2>Our Products</h2>
        </div>
        <div class="col-md-6">
            <form class="d-flex" method="GET">
                <input class="form-control me-2" type="search" name="search" placeholder="Search products..." 
                       value="<?php echo htmlspecialchars($search); ?>">
                <button class="btn btn-outline-primary" type="submit">Search</button>
            </form>
        </div>
    </div>
    
    <?php if (empty($products) && $search): ?>
        <div class="alert alert-info">No products found matching your search.</div>
    <?php elseif (empty($products)): ?>
        <div class="alert alert-info">No products available at the moment.</div>
    <?php else: ?>
        <div class="row">
            <?php foreach ($products as $product): ?>
                <div class="col-md-4 col-lg-3 mb-4">
                    <div class="card h-100">
                        <img src="<?php echo APP_URL; ?>/assets/images/products/<?php echo $product['image'] ?? 'default.jpg'; ?>" 
                             class="card-img-top" alt="<?php echo htmlspecialchars($product['name']); ?>">
                        <div class="card-body">
                            <h5 class="card-title"><?php echo htmlspecialchars($product['name']); ?></h5>
                            <p class="card-text text-muted">KES <?php echo number_format($product['price'], 2); ?></p>
                            <div class="d-flex justify-content-between">
                                <a href="view.php?id=<?php echo $product['id']; ?>" class="btn btn-sm btn-outline-primary">View</a>
                                <?php if (isLoggedIn()): ?>
                                    <button class="btn btn-sm btn-primary add-to-cart" data-product-id="<?php echo $product['id']; ?>">
                                        <i class="fas fa-cart-plus"></i> Add to Cart
                                    </button>
                                <?php else: ?>
                                    <a href="../login.php" class="btn btn-sm btn-primary">Login to Buy</a>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
        
        <!-- Pagination -->
        <?php if ($totalPages > 1): ?>
            <nav aria-label="Page navigation">
                <ul class="pagination justify-content-center">
                    <?php if ($page > 1): ?>
                        <li class="page-item">
                            <a class="page-link" href="?page=<?php echo $page - 1; ?>&search=<?php echo urlencode($search); ?>">Previous</a>
                        </li>
                    <?php endif; ?>
                    
                    <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                        <li class="page-item <?php echo $i == $page ? 'active' : ''; ?>">
                            <a class="page-link" href="?page=<?php echo $i; ?>&search=<?php echo urlencode($search); ?>"><?php echo $i; ?></a>
                        </li>
                    <?php endfor; ?>
                    
                    <?php if ($page < $totalPages): ?>
                        <li class="page-item">
                            <a class="page-link" href="?page=<?php echo $page + 1; ?>&search=<?php echo urlencode($search); ?>">Next</a>
                        </li>
                    <?php endif; ?>
                </ul>
            </nav>
        <?php endif; ?>
    <?php endif; ?>
</div>

<?php include '../../includes/footer.php'; ?>