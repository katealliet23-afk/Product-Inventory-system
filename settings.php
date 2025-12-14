<?php
// Include the necessary components
require_once "includes/header.php"; 
require_once "includes/sidebar.php"; 
require_once "config.php";

// We use the same CSS for the back-button as defined in includes/header.php

// Account update logic would go here (e.g., handling POST requests for password change)
$message = '';

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['action']) && $_POST['action'] == 'change_password') {
    // Basic password change logic placeholder
    $new_password = trim($_POST['new_password']);
    $confirm_password = trim($_POST['confirm_password']);

    if (empty($new_password) || strlen($new_password) < 6) {
        $message = '<div class="alert alert-danger">Password must be at least 6 characters long.</div>';
    } elseif ($new_password !== $confirm_password) {
        $message = '<div class="alert alert-danger">New password and confirmation do not match.</div>';
    } else {
        // Hashing the password for database update
        $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
        $user_id = $_SESSION['id'];
        
        $sql = "UPDATE users SET password = ? WHERE id = ?";
        
        if ($stmt = $pdo->prepare($sql)) {
            if ($stmt->execute([$hashed_password, $user_id])) {
                $message = '<div class="alert alert-success">Password updated successfully!</div>';
            } else {
                $message = '<div class="alert alert-danger">Error updating password. Please try again.</div>';
            }
            unset($stmt);
        }
    }
}
?>

<div class="content-card">
    
    <div class="back-button-container">
        <a href="dashboard.php" class="btn back-btn-style">
            <i class="fas fa-arrow-left"></i> Back to Dashboard
        </a>
    </div>

    <div style="background-color: #6c757d; color: white; padding: 20px; border-radius: 5px; margin-bottom: 30px;">
        <h2>Account Settings <i class="fas fa-cog"></i></h2>
        <p style="color: #dee2e6; margin-top: 0;">Change your password and manage account security.</p>
    </div>

    <?php echo $message; // Display success/error messages ?>

    <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
        <input type="hidden" name="action" value="change_password">
        
        <div class="form-group">
            <label for="new_password">New Password</label>
            <input type="password" name="new_password" id="new_password" class="form-control" required minlength="6">
            <small class="form-text text-muted">Password must be at least 6 characters long.</small>
        </div>

        <div class="form-group">
            <label for="confirm_password">Confirm New Password</label>
            <input type="password" name="confirm_password" id="confirm_password" class="form-control" required minlength="6">
        </div>
        
        <div class="form-group mt-4">
            <button type="submit" class="btn btn-primary">Update Password</button>
        </div>
    </form>
</div>


<?php 
// Include the reusable footer
require_once "includes/footer.php"; 
?>