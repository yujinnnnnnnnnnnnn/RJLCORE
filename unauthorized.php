<?php
require_once 'config/config.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Unauthorized Access - <?php echo APP_NAME; ?></title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body style="background: linear-gradient(135deg, var(--navy) 0%, var(--green) 100%); min-height: 100vh; display: flex; align-items: center; justify-content: center;">
    
    <div class="container" style="max-width: 500px; text-align: center;">
        <div class="card">
            <div style="background: linear-gradient(135deg, var(--danger), #ff6b6b); color: white; width: 100px; height: 100px; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 2rem; font-size: 3rem;">
                <i class="fas fa-ban"></i>
            </div>
            
            <h1 style="color: var(--danger); margin-bottom: 1rem;">Access Denied</h1>
            <h2 style="color: var(--navy); font-size: 1.5rem; margin-bottom: 2rem;">Unauthorized Access</h2>
            
            <p style="color: var(--gray); margin-bottom: 2rem; font-size: 1.1rem;">
                You don't have permission to access this page. Please contact your administrator if you believe this is an error.
            </p>
            
            <div style="margin-bottom: 2rem;">
                <p><strong>Possible reasons:</strong></p>
                <ul style="text-align: left; display: inline-block; color: var(--gray);">
                    <li>You're not logged in</li>
                    <li>Your account doesn't have the required permissions</li>
                    <li>Your session has expired</li>
                    <li>You're trying to access a restricted area</li>
                </ul>
            </div>
            
            <div class="action-buttons">
                <a href="javascript:history.back()" class="btn btn-secondary" style="margin-right: 1rem;">
                    <i class="fas fa-arrow-left"></i> Go Back
                </a>
                <a href="index.php" class="btn btn-primary">
                    <i class="fas fa-home"></i> Home Page
                </a>
            </div>
            
            <hr style="margin: 2rem 0; border: none; height: 1px; background: #eee;">
            
            <div style="text-align: center;">
                <p style="color: var(--gray); margin-bottom: 1rem;">Need to log in?</p>
                <a href="login.php" class="btn btn-success" style="margin-right: 1rem;">
                    <i class="fas fa-user-tie"></i> Staff Login
                </a>
                <a href="customer_login.php" class="btn btn-info">
                    <i class="fas fa-user"></i> Customer Login
                </a>
            </div>
        </div>
    </div>

    <script src="assets/js/main.js"></script>
</body>
</html>