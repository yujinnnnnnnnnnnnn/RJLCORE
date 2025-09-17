<?php
/**
 * Login Page for Appliances Management System
 */

require_once 'config/config.php';

// Redirect if already logged in
if (isLoggedIn()) {
    $user = getCurrentUser();
    $auth = new Auth();
    redirectTo($auth->getRedirectUrl($user['role']));
}

$error = '';
$success = '';

// Handle login form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = sanitizeInput($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $remember_me = isset($_POST['remember_me']);
    
    if (empty($username) || empty($password)) {
        $error = 'Please enter both username/email and password.';
    } else {
        $auth = new Auth();
        $result = $auth->login($username, $password, $remember_me);
        
        if ($result['success']) {
            flashMessage('success', $result['message']);
            redirectTo($result['redirect']);
        } else {
            $error = $result['message'];
        }
    }
}

// Handle session timeout
if (isset($_GET['timeout'])) {
    $error = 'Your session has expired. Please log in again.';
}

// Handle logout message
if (isset($_GET['logout'])) {
    $success = 'You have been successfully logged out.';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - <?php echo APP_NAME; ?></title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body class="login-page">
    <div class="login-container">
        <div class="login-card">
            <div class="login-header">
                <div class="logo">
                    <i class="fas fa-plug"></i>
                    <span><?php echo APP_NAME; ?></span>
                </div>
                <h2>Welcome Back</h2>
                <p>Please sign in to your account</p>
            </div>
            
            <div class="login-body">
                <?php if ($error): ?>
                    <div class="alert alert-danger">
                        <i class="fas fa-exclamation-circle"></i>
                        <?php echo htmlspecialchars($error); ?>
                    </div>
                <?php endif; ?>
                
                <?php if ($success): ?>
                    <div class="alert alert-success">
                        <i class="fas fa-check-circle"></i>
                        <?php echo htmlspecialchars($success); ?>
                    </div>
                <?php endif; ?>
                
                <form method="POST" class="login-form">
                    <div class="form-group">
                        <label for="username" class="form-label">
                            <i class="fas fa-user"></i>
                            Username or Email
                        </label>
                        <input type="text" 
                               id="username" 
                               name="username" 
                               class="form-control" 
                               value="<?php echo htmlspecialchars($username ?? ''); ?>"
                               required
                               autocomplete="username">
                    </div>
                    
                    <div class="form-group">
                        <label for="password" class="form-label">
                            <i class="fas fa-lock"></i>
                            Password
                        </label>
                        <div class="password-input-group">
                            <input type="password" 
                                   id="password" 
                                   name="password" 
                                   class="form-control" 
                                   required
                                   autocomplete="current-password">
                            <button type="button" class="password-toggle" onclick="togglePassword('password')">
                                <i class="fas fa-eye"></i>
                            </button>
                        </div>
                    </div>
                    
                    <div class="form-group form-check">
                        <input type="checkbox" id="remember_me" name="remember_me" class="form-check-input">
                        <label for="remember_me" class="form-check-label">Remember me</label>
                    </div>
                    
                    <button type="submit" class="btn btn-primary btn-lg w-100">
                        <i class="fas fa-sign-in-alt"></i>
                        Sign In
                    </button>
                </form>
                
                <div class="login-links">
                    <a href="forgot-password.php" class="forgot-password-link">
                        <i class="fas fa-key"></i>
                        Forgot your password?
                    </a>
                </div>
            </div>
            
            <div class="login-footer">
                <div class="signup-prompt">
                    <span>Don't have an account?</span>
                    <a href="register.php" class="signup-link">Sign up here</a>
                </div>
                
                <div class="demo-accounts">
                    <h4>Demo Accounts</h4>
                    <div class="demo-account-list">
                        <div class="demo-account">
                            <strong>Admin:</strong> admin / admin123
                        </div>
                        <div class="demo-account">
                            <strong>Staff:</strong> staff / staff123
                        </div>
                        <div class="demo-account">
                            <strong>Customer:</strong> customer / customer123
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="login-background">
            <div class="appliance-icons">
                <i class="fas fa-tv"></i>
                <i class="fas fa-blender"></i>
                <i class="fas fa-microwave"></i>
                <i class="fas fa-wind"></i>
                <i class="fas fa-temperature-low"></i>
                <i class="fas fa-washing-machine"></i>
            </div>
        </div>
    </div>

    <script src="assets/js/main.js"></script>
    <script>
        function togglePassword(fieldId) {
            const field = document.getElementById(fieldId);
            const toggle = field.nextElementSibling;
            const icon = toggle.querySelector('i');
            
            if (field.type === 'password') {
                field.type = 'text';
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            } else {
                field.type = 'password';
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            }
        }
        
        // Add login page specific styles
        document.head.insertAdjacentHTML('beforeend', `
            <style>
                .login-page {
                    background: linear-gradient(135deg, var(--navy-blue), var(--primary-green));
                    min-height: 100vh;
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    padding: 20px;
                    position: relative;
                    overflow: hidden;
                }
                
                .login-container {
                    display: flex;
                    max-width: 1200px;
                    width: 100%;
                    position: relative;
                    z-index: 2;
                }
                
                .login-card {
                    background: var(--white);
                    border-radius: 20px;
                    box-shadow: var(--shadow-lg);
                    overflow: hidden;
                    width: 100%;
                    max-width: 400px;
                    margin: 0 auto;
                    animation: fadeInUp 0.8s ease-out;
                }
                
                .login-header {
                    background: var(--gradient-primary);
                    color: var(--navy-blue);
                    padding: 2rem;
                    text-align: center;
                }
                
                .login-header .logo {
                    font-size: 2rem;
                    font-weight: bold;
                    margin-bottom: 1rem;
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    gap: 10px;
                }
                
                .login-header h2 {
                    margin-bottom: 0.5rem;
                    color: var(--navy-blue);
                }
                
                .login-header p {
                    opacity: 0.8;
                    margin: 0;
                }
                
                .login-body {
                    padding: 2rem;
                }
                
                .login-form .form-group {
                    margin-bottom: 1.5rem;
                }
                
                .login-form .form-label {
                    display: flex;
                    align-items: center;
                    gap: 8px;
                    margin-bottom: 0.5rem;
                    font-weight: 500;
                    color: var(--navy-blue);
                }
                
                .password-input-group {
                    position: relative;
                }
                
                .password-toggle {
                    position: absolute;
                    right: 10px;
                    top: 50%;
                    transform: translateY(-50%);
                    background: none;
                    border: none;
                    color: var(--medium-gray);
                    cursor: pointer;
                    padding: 5px;
                    transition: color var(--transition-fast);
                }
                
                .password-toggle:hover {
                    color: var(--navy-blue);
                }
                
                .form-check {
                    display: flex;
                    align-items: center;
                    gap: 8px;
                }
                
                .form-check-input {
                    width: auto;
                    margin: 0;
                }
                
                .login-links {
                    text-align: center;
                    margin-top: 1.5rem;
                }
                
                .forgot-password-link {
                    color: var(--navy-blue);
                    text-decoration: none;
                    display: inline-flex;
                    align-items: center;
                    gap: 5px;
                    font-size: 0.9rem;
                    transition: color var(--transition-fast);
                }
                
                .forgot-password-link:hover {
                    color: var(--primary-green);
                }
                
                .login-footer {
                    background: var(--light-gray);
                    padding: 1.5rem 2rem;
                    text-align: center;
                    border-top: 1px solid #e9ecef;
                }
                
                .signup-prompt {
                    margin-bottom: 1.5rem;
                    color: var(--medium-gray);
                }
                
                .signup-link {
                    color: var(--navy-blue);
                    text-decoration: none;
                    font-weight: 500;
                    margin-left: 5px;
                }
                
                .signup-link:hover {
                    color: var(--primary-green);
                }
                
                .demo-accounts {
                    border-top: 1px solid #e9ecef;
                    padding-top: 1rem;
                }
                
                .demo-accounts h4 {
                    font-size: 0.9rem;
                    color: var(--navy-blue);
                    margin-bottom: 0.5rem;
                }
                
                .demo-account {
                    font-size: 0.8rem;
                    color: var(--medium-gray);
                    margin: 0.25rem 0;
                }
                
                .login-background {
                    position: absolute;
                    top: 0;
                    left: 0;
                    right: 0;
                    bottom: 0;
                    z-index: 1;
                    opacity: 0.1;
                }
                
                .appliance-icons {
                    position: absolute;
                    top: 0;
                    left: 0;
                    right: 0;
                    bottom: 0;
                    display: flex;
                    flex-wrap: wrap;
                    align-items: center;
                    justify-content: center;
                    gap: 3rem;
                    font-size: 4rem;
                    color: var(--white);
                    animation: float 6s ease-in-out infinite;
                }
                
                .appliance-icons i {
                    animation: float 6s ease-in-out infinite;
                    animation-delay: calc(var(--i) * 0.5s);
                }
                
                @keyframes float {
                    0%, 100% { transform: translateY(0px) rotate(0deg); }
                    50% { transform: translateY(-20px) rotate(5deg); }
                }
                
                @media (max-width: 768px) {
                    .login-container {
                        max-width: 100%;
                    }
                    
                    .login-card {
                        margin: 0;
                        border-radius: 0;
                        min-height: 100vh;
                        display: flex;
                        flex-direction: column;
                        justify-content: center;
                    }
                    
                    .login-header,
                    .login-body,
                    .login-footer {
                        padding: 1.5rem;
                    }
                    
                    .appliance-icons {
                        font-size: 2rem;
                        gap: 2rem;
                    }
                }
            </style>
        `);
    </script>
</body>
</html>