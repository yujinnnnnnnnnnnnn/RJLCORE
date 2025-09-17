<?php
require_once 'config/config.php';
require_once 'classes/Auth.php';

// Redirect if already logged in
if (is_logged_in()) {
    $role = $_SESSION['role_name'];
    if ($role === 'customer') {
        redirect('customer/dashboard.php');
    }
}

$error_message = '';
$success_message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action']) && $_POST['action'] === 'register') {
        $auth = new Auth();
        
        $data = [
            'username' => sanitize_input($_POST['username']),
            'email' => sanitize_input($_POST['email']),
            'password' => $_POST['password'],
            'first_name' => sanitize_input($_POST['first_name']),
            'last_name' => sanitize_input($_POST['last_name']),
            'phone' => sanitize_input($_POST['phone'] ?? ''),
            'address' => sanitize_input($_POST['address'] ?? ''),
            'role_id' => 3 // Customer role
        ];
        
        // Validate password confirmation
        if ($_POST['password'] !== $_POST['confirm_password']) {
            $result = [
                'success' => false,
                'message' => 'Passwords do not match'
            ];
        } else {
            $result = $auth->register($data);
        }
        
        if ($_POST['ajax'] ?? false) {
            header('Content-Type: application/json');
            echo json_encode($result);
            exit;
        }
        
        if ($result['success']) {
            $success_message = $result['message'] . ' You can now login to your account.';
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
    <title>Create Account - <?php echo APP_NAME; ?></title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body style="background: linear-gradient(135deg, var(--green) 0%, var(--beige) 100%); min-height: 100vh; padding: 2rem 0;">
    
    <div class="container" style="max-width: 500px;">
        <div class="card" style="animation: slideInUp 0.6s ease-out;">
            <div class="text-center mb-4">
                <div style="background: linear-gradient(135deg, var(--green), var(--navy)); color: white; width: 80px; height: 80px; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 1rem; font-size: 2rem;">
                    <i class="fas fa-user-plus"></i>
                </div>
                <h2>Create Customer Account</h2>
                <p class="text-gray">Join ApplianceStore and start shopping</p>
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
                    <div class="mt-3">
                        <a href="customer_login.php" class="btn btn-primary">Login Now</a>
                    </div>
                </div>
            <?php else: ?>

            <form method="POST" class="ajax-form" action="customer_register.php">
                <input type="hidden" name="action" value="register">
                <input type="hidden" name="ajax" value="1">

                <div class="row">
                    <div class="col-6">
                        <div class="form-group">
                            <label for="first_name" class="form-label">
                                <i class="fas fa-user"></i> First Name *
                            </label>
                            <input type="text" id="first_name" name="first_name" class="form-control" required 
                                   placeholder="Enter first name">
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="form-group">
                            <label for="last_name" class="form-label">
                                <i class="fas fa-user"></i> Last Name *
                            </label>
                            <input type="text" id="last_name" name="last_name" class="form-control" required 
                                   placeholder="Enter last name">
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label for="username" class="form-label">
                        <i class="fas fa-at"></i> Username *
                    </label>
                    <input type="text" id="username" name="username" class="form-control" required 
                           placeholder="Choose a username">
                </div>

                <div class="form-group">
                    <label for="email" class="form-label">
                        <i class="fas fa-envelope"></i> Email Address *
                    </label>
                    <input type="email" id="email" name="email" class="form-control" required 
                           placeholder="Enter email address">
                </div>

                <div class="form-group">
                    <label for="phone" class="form-label">
                        <i class="fas fa-phone"></i> Phone Number
                    </label>
                    <input type="tel" id="phone" name="phone" class="form-control" 
                           placeholder="Enter phone number">
                </div>

                <div class="form-group">
                    <label for="address" class="form-label">
                        <i class="fas fa-map-marker-alt"></i> Address
                    </label>
                    <textarea id="address" name="address" class="form-control" rows="3" 
                              placeholder="Enter your address"></textarea>
                </div>

                <div class="row">
                    <div class="col-6">
                        <div class="form-group">
                            <label for="password" class="form-label">
                                <i class="fas fa-lock"></i> Password *
                            </label>
                            <div style="position: relative;">
                                <input type="password" id="password" name="password" class="form-control" required 
                                       placeholder="Create password" minlength="6">
                                <button type="button" id="togglePassword" 
                                        style="position: absolute; right: 15px; top: 50%; transform: translateY(-50%); background: none; border: none; color: var(--gray); cursor: pointer;">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>
                            <small class="text-gray">Minimum 6 characters</small>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="form-group">
                            <label for="confirm_password" class="form-label">
                                <i class="fas fa-lock"></i> Confirm Password *
                            </label>
                            <div style="position: relative;">
                                <input type="password" id="confirm_password" name="confirm_password" class="form-control" required 
                                       placeholder="Confirm password">
                                <button type="button" id="toggleConfirmPassword" 
                                        style="position: absolute; right: 15px; top: 50%; transform: translateY(-50%); background: none; border: none; color: var(--gray); cursor: pointer;">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label style="display: flex; align-items: center; cursor: pointer;">
                        <input type="checkbox" required style="margin-right: 0.5rem;">
                        <span>I agree to the <a href="#" style="color: var(--navy);">Terms of Service</a> and <a href="#" style="color: var(--navy);">Privacy Policy</a></span>
                    </label>
                </div>

                <div class="form-group">
                    <button type="submit" class="btn btn-primary" style="width: 100%;">
                        <i class="fas fa-user-plus"></i> Create Account
                    </button>
                </div>
            </form>

            <?php endif; ?>

            <hr style="margin: 2rem 0; border: none; height: 1px; background: #eee;">

            <div class="text-center">
                <p style="margin-bottom: 1rem; color: var(--gray);">Already have an account?</p>
                <a href="customer_login.php" class="btn btn-secondary" style="width: 100%; margin-bottom: 1rem;">
                    <i class="fas fa-sign-in-alt"></i> Login to Portal
                </a>
                <div style="margin-top: 1rem;">
                    <a href="login.php" style="color: var(--navy); text-decoration: none; margin-right: 1rem;">
                        <i class="fas fa-user-tie"></i> Staff Login
                    </a>
                    <a href="index.php" style="color: var(--navy); text-decoration: none;">
                        <i class="fas fa-home"></i> Home
                    </a>
                </div>
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

        document.getElementById('toggleConfirmPassword').addEventListener('click', function() {
            const password = document.getElementById('confirm_password');
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

        // Password confirmation validation
        document.getElementById('confirm_password').addEventListener('input', function() {
            const password = document.getElementById('password').value;
            const confirmPassword = this.value;
            
            if (password !== confirmPassword && confirmPassword.length > 0) {
                this.style.borderColor = '#dc3545';
            } else {
                this.style.borderColor = '';
            }
        });
    </script>
</body>
</html>