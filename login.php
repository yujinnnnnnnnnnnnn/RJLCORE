<?php
require_once 'config/config.php';
require_once 'classes/Auth.php';

// Redirect if already logged in
if (is_logged_in()) {
    $role = $_SESSION['role_name'];
    if ($role === 'admin' || $role === 'staff') {
        redirect('admin/dashboard.php');
    }
}

$error_message = '';
$success_message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action']) && $_POST['action'] === 'login') {
        $auth = new Auth();
        $username = sanitize_input($_POST['username']);
        $password = $_POST['password'];
        
        $result = $auth->login($username, $password);
        
        if ($_POST['ajax'] ?? false) {
            header('Content-Type: application/json');
            echo json_encode($result);
            exit;
        }
        
        if ($result['success']) {
            redirect($result['redirect']);
        } else {
            $error_message = $result['message'];
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Staff Login - <?php echo APP_NAME; ?></title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body style="background: linear-gradient(135deg, var(--navy) 0%, var(--green) 100%); min-height: 100vh; display: flex; align-items: center; justify-content: center;">
    
    <div class="container" style="max-width: 400px;">
        <div class="card" style="animation: slideInUp 0.6s ease-out;">
            <div class="text-center mb-4">
                <div style="background: linear-gradient(135deg, var(--navy), var(--green)); color: white; width: 80px; height: 80px; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 1rem; font-size: 2rem;">
                    <i class="fas fa-user-tie"></i>
                </div>
                <h2>Staff Login</h2>
                <p class="text-gray">Access the Admin/Staff Portal</p>
            </div>

            <?php if ($error_message): ?>
                <div class="notification error">
                    <i class="fas fa-exclamation-circle"></i>
                    <?php echo $error_message; ?>
                </div>
            <?php endif; ?>

            <?php if ($success_message): ?>
                <div class="notification success">
                    <i class="fas fa-check-circle"></i>
                    <?php echo $success_message; ?>
                </div>
            <?php endif; ?>

            <form method="POST" class="ajax-form" action="login.php">
                <input type="hidden" name="action" value="login">
                <input type="hidden" name="ajax" value="1">

                <div class="form-group">
                    <label for="username" class="form-label">
                        <i class="fas fa-user"></i> Username or Email
                    </label>
                    <input type="text" id="username" name="username" class="form-control" required 
                           placeholder="Enter your username or email">
                </div>

                <div class="form-group">
                    <label for="password" class="form-label">
                        <i class="fas fa-lock"></i> Password
                    </label>
                    <div style="position: relative;">
                        <input type="password" id="password" name="password" class="form-control" required 
                               placeholder="Enter your password">
                        <button type="button" id="togglePassword" 
                                style="position: absolute; right: 15px; top: 50%; transform: translateY(-50%); background: none; border: none; color: var(--gray); cursor: pointer;">
                            <i class="fas fa-eye"></i>
                        </button>
                    </div>
                </div>

                <div class="form-group">
                    <button type="submit" class="btn btn-primary" style="width: 100%;">
                        <i class="fas fa-sign-in-alt"></i> Login
                    </button>
                </div>
            </form>

            <div class="text-center">
                <a href="forgot_password.php" style="color: var(--navy); text-decoration: none;">
                    <i class="fas fa-key"></i> Forgot Password?
                </a>
            </div>

            <hr style="margin: 2rem 0; border: none; height: 1px; background: #eee;">

            <div class="text-center">
                <p style="margin-bottom: 1rem; color: var(--gray);">Not a staff member?</p>
                <a href="customer_login.php" class="btn btn-secondary" style="width: 100%; margin-bottom: 1rem;">
                    <i class="fas fa-user"></i> Customer Portal
                </a>
                <a href="index.php" style="color: var(--navy); text-decoration: none;">
                    <i class="fas fa-arrow-left"></i> Back to Home
                </a>
            </div>
        </div>
    </div>

    <script src="assets/js/main.js"></script>
    <script>
        // Toggle password visibility
        document.getElementById('togglePassword').addEventListener('click', function() {
            const password = document.getElementById('password');
            const icon = this.querySelector('i');
            
            if (password.type === 'password') {
                password.type = 'text';
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            } else {
                password.type = 'password';
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            }
        });

        // Demo credentials info
        setTimeout(() => {
            ApplianceStore.showNotification('Demo: Use "admin" / "admin123" to login as admin', 'info');
        }, 2000);
    </script>
</body>
</html>