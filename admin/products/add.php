<?php
require_once '../../../includes/config.php';
require_once '../../../includes/auth.php';
requireAdmin();

$pageTitle = 'Add New Product';
include '../../../includes/header.php';

$errors = [];
$success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = trim($_POST['name']);
    $description = trim($_POST['description']);
    $price = trim($_POST['price']);
    $stock = trim($_POST['stock']);
    
    // Validate inputs
    if (empty($name)) {
        $errors['name'] = 'Product name is required';
    }
    
    if (empty($description)) {
        $errors['description'] = 'Description is required';
    }
    
    if (empty($price) || !is_numeric($price) || $price <= 0) {
        $errors['price'] = 'Valid price is required';
    }
    
    if (empty($stock) || !is_numeric($stock) || $stock < 0) {
        $errors['stock'] = 'Valid stock quantity is required';
    }
    
    // Handle file upload
    $image = '';
    if (isset($_FILES['image']) && $_FILES['image']['error'] == UPLOAD_ERR_OK) {
        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
        $fileType = $_FILES['image']['type'];
        
        if (!in_array($fileType, $allowedTypes)) {
            $errors['image'] = 'Only JPG, PNG, and GIF images are allowed';
        } else {
            $uploadDir = '../../assets/images/products/';
            $fileName = uniqid() . '_' . basename($_FILES['image']['name']);
            $targetPath = $uploadDir . $fileName;
            
            if (move_uploaded_file($_FILES['image']['tmp_name'], $targetPath)) {
                $image = $fileName;
            } else {
                $errors['image'] = 'Failed to upload image';
            }
        }
    } else {
        $errors['image'] = 'Product image is required';
    }
    
    // If no errors, insert product
    if (empty($errors)) {
        $stmt = $pdo->prepare("
            INSERT INTO products (name, description, price, image, stock)
            VALUES (?, ?, ?, ?, ?)
        ");
        
        if ($stmt->execute([$name, $description, $price, $image, $stock])) {
            $success = 'Product added successfully!';
            // Clear form
            $name = $description = $price = $stock = '';
        } else {
            $errors['database'] = 'Failed to add product. Please try again.';
        }
    }
}
?>

<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Add New Product</h2>
        <a href="index.php" class="btn btn-outline-secondary">Back to Products</a>
    </div>
    
    <?php if ($success): ?>
        <div class="alert alert-success"><?php echo $success; ?></div>
    <?php endif; ?>
    
    <?php if (isset($errors['database'])): ?>
        <div class="alert alert-danger"><?php echo $errors['database']; ?></div>
    <?php endif; ?>
    
    <form method="POST" enctype="multipart/form-data">
        <div class="row">
            <div class="col-md-6">
                <div class="mb-3">
                    <label for="name" class="form-label">Product Name</label>
                    <input type="text" class="form-control <?php echo isset($errors['name']) ? 'is-invalid' : ''; ?>" 
                           id="name" name="name" value="<?php echo htmlspecialchars($name ?? ''); ?>" required>
                    <?php if (isset($errors['name'])): ?>
                        <div class="invalid-feedback"><?php echo $errors['name']; ?></div>
                    <?php endif; ?>
                </div>
                
                <div class="mb-3">
                    <label for="description" class="form-label">Description</label>
                    <textarea class="form-control <?php echo isset($errors['description']) ? 'is-invalid' : ''; ?>" 
                              id="description" name="description" rows="5" required><?php echo htmlspecialchars($description ?? ''); ?></textarea>
                    <?php if (isset($errors['description'])): ?>
                        <div class="invalid-feedback"><?php echo $errors['description']; ?></div>
                    <?php endif; ?>
                </div>
            </div>
            
            <div class="col-md-6">
                <div class="mb-3">
                    <label for="price" class="form-label">Price (KES)</label>
                    <input type="number" step="0.01" class="form-control <?php echo isset($errors['price']) ? 'is-invalid' : ''; ?>" 
                           id="price" name="price" value="<?php echo htmlspecialchars($price ?? ''); ?>" required>
                    <?php if (isset($errors['price'])): ?>
                        <div class="invalid-feedback"><?php echo $errors['price']; ?></div>
                    <?php endif; ?>
                </div>
                
                <div class="mb-3">
                    <label for="stock" class="form-label">Stock Quantity</label>
                    <input type="number" class="form-control <?php echo isset($errors['stock']) ? 'is-invalid' : ''; ?>" 
                           id="stock" name="stock" value="<?php echo htmlspecialchars($stock ?? ''); ?>" required>
                    <?php if (isset($errors['stock'])): ?>
                        <div class="invalid-feedback"><?php echo $errors['stock']; ?></div>
                    <?php endif; ?>
                </div>
                
                <div class="mb-3">
                    <label for="image" class="form-label">Product Image</label>
                    <input type="file" class="form-control <?php echo isset($errors['image']) ? 'is-invalid' : ''; ?>" 
                           id="image" name="image" required accept="image/*">
                    <?php if (isset($errors['image'])): ?>
                        <div class="invalid-feedback"><?php echo $errors['image']; ?></div>
                    <?php endif; ?>
                    <div class="form-text">Upload a high-quality image of the product (JPEG, PNG, GIF)</div>
                </div>
            </div>
        </div>
        
        <div class="d-grid gap-2">
            <button type="submit" class="btn btn-primary btn-lg">Add Product</button>
        </div>
    </form>
</div>

<?php include '../../../includes/footer.php'; ?>