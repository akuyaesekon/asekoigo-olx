<?php
require_once '../includes/config.php';
require_once '../includes/auth.php';
requireLogin();

$pageTitle = 'My Profile';
include '../includes/header.php';

// Get user details
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch();

$errors = [];
$success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    $address = trim($_POST['address']);
    $currentPassword = trim($_POST['current_password']);
    $newPassword = trim($_POST['new_password']);
    $confirmPassword = trim($_POST['confirm_password']);
    
    // Validate inputs
    if (empty($username)) {
        $errors['username'] = 'Username is required';
    }
    
    if (empty($email)) {
        $errors['email'] = 'Email is required';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = 'Invalid email format';
    }
    
    if (empty($phone)) {
        $errors['phone'] = 'Phone number is required';
    }
    
    if (empty($address)) {
        $errors['address'] = 'Address is required';
    }
    
    // Check if password is being changed
    $passwordChanged = false;
    if (!empty($currentPassword) || !empty($newPassword) || !empty($confirmPassword)) {
        if (empty($currentPassword)) {
            $errors['current_password'] = 'Current password is required to change password';
        } elseif ($currentPassword !== $user['password']) { // Changed from password_verify to direct comparison
            $errors['current_password'] = 'Current password is incorrect';
        }
        
        if (empty($newPassword)) {
            $errors['new_password'] = 'New password is required';
        } elseif (strlen($newPassword) < 6) {
            $errors['new_password'] = 'Password must be at least 6 characters';
        }
        
        if ($newPassword !== $confirmPassword) {
            $errors['confirm_password'] = 'Passwords do not match';
        }
        
        $passwordChanged = true;
    }
    
    // Check if username or email already exists (excluding current user)
    $stmt = $pdo->prepare("SELECT id FROM users WHERE (username = ? OR email = ?) AND id != ?");
    $stmt->execute([$username, $email, $_SESSION['user_id']]);
    if ($stmt->fetch()) {
        $errors['general'] = 'Username or email already exists';
    }
    
    // If no errors, update profile
    if (empty($errors)) {
        try {
            if ($passwordChanged) {
                // Store new password in plain text
                $stmt = $pdo->prepare("
                    UPDATE users 
                    SET username = ?, email = ?, phone = ?, address = ?, password = ?
                    WHERE id = ?
                ");
                $stmt->execute([$username, $email, $phone, $address, $newPassword, $_SESSION['user_id']]);
            } else {
                $stmt = $pdo->prepare("
                    UPDATE users 
                    SET username = ?, email = ?, phone = ?, address = ?
                    WHERE id = ?
                ");
                $stmt->execute([$username, $email, $phone, $address, $_SESSION['user_id']]);
            }
            
            // Update session data
            $_SESSION['username'] = $username;
            $_SESSION['email'] = $email;
            
            $success = 'Profile updated successfully!';
            
            // Refresh user data
            $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
            $stmt->execute([$_SESSION['user_id']]);
            $user = $stmt->fetch();
        } catch (PDOException $e) {
            $errors['database'] = 'Failed to update profile. Please try again.';
        }
    }
}
?>

<!-- The rest of your HTML remains exactly the same -->
<div class="container py-5">
    <!-- ... existing HTML ... -->
</div>

<?php include '../includes/footer.php'; ?>