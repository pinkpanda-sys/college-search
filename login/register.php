<?php
session_start();
$error_message = '';
$success_message = '';

if (isset($_SESSION['error'])) {
    $error_message = $_SESSION['error'];
    unset($_SESSION['error']);
}

if (isset($_SESSION['success'])) {
    $success_message = $_SESSION['success'];
    unset($_SESSION['success']);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <!-- Keep your existing head content -->
</head>
<body>
    <!-- Keep your existing preloader and navigation -->

    <!-- Register Section -->
    <div class="neo-register-container">
        <!-- Keep your existing animated background -->
        
        <div class="glass-effect-container">
            <div class="glass-register-card" data-tilt data-tilt-max="5" data-tilt-glare data-tilt-max-glare="0.1">
                <div class="glass-card-content">
                    <!-- Add message display section -->
                    <?php if (!empty($error_message)): ?>
                    <div class="alert alert-danger">
                        <i class="fas fa-exclamation-circle"></i> <?php echo $error_message; ?>
                    </div>
                    <?php endif; ?>
                    
                    <?php if (!empty($success_message)): ?>
                    <div class="alert alert-success">
                        <i class="fas fa-check-circle"></i> <?php echo $success_message; ?>
                    </div>
                    <?php endif; ?>
                    
                    <div class="register-header">
                        <!-- Keep your existing header content -->
                    </div>
                    
                    <!-- Your existing form with minor modifications -->
                    <form class="register-form" action="../handlers/register_handler.php" method="POST" id="registerForm">
                        <!-- Keep all your existing form fields -->
                    </form>
                    
                    <!-- Keep the rest of your content -->
                </div>
            </div>
        </div>
        
        <!-- Keep your journey section -->
    </div>

    <!-- Keep floating education items, footer, and scripts -->
</body>
</html> 