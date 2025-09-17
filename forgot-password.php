<?php
/**
 * Forgot Password Page for Appliances Management System
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
$step = 'request'; // request, reset

// Handle password reset token
if (isset($_GET['token'])) {
    $step = 'reset';
    $token = sanitizeInput($_GET['token']);
}

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $auth = new Auth();
    
    if ($step === 'request') {
        $email = sanitizeInput($_POST['email'] ?? '');
        
        if (empty($email)) {
            $error = 'Please enter your email address.';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error = 'Please enter a valid email address.';
        } else {
            $result = $auth->requestPasswordReset($email);
            if ($result['success']) {
                $success = $result['message'];
            } else {
                $error = $result['message'];
            }
        }
    } elseif ($step === 'reset') {
        $new_password = $_POST['new_password'] ?? '';
        $confirm_password = $_POST['confirm_password'] ?? '';
        $token = $_POST['token'] ?? '';
        
        if (empty($new_password) || empty($confirm_password)) {
            $error = 'Please enter and confirm your new password.';
        } elseif ($new_password !== $confirm_password) {
            $error = 'Passwords do not match.';
        } else {
            $result = $auth->resetPassword($token, $new_password);
            if ($result['success']) {
                $success = $result['message'];
                $step = 'complete';
            } else {
                $error = $result['message'];
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password - <?php echo APP_NAME; ?></title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body class="forgot-password-page">
    <div class="forgot-password-container">
        <div class="forgot-password-card">
            <div class="forgot-password-header">
                <div class="logo">
                    <i class="fas fa-plug"></i>
                    <span><?php echo APP_NAME; ?></span>
                </div>
                <h2>
                    <?php if ($step === 'request'): ?>
                        Forgot Password?
                    <?php elseif ($step === 'reset'): ?>
                        Reset Password
                    <?php else: ?>
                        Password Reset Complete
                    <?php endif; ?>
                </h2>
                <p>
                    <?php if ($step === 'request'): ?>
                        Enter your email address and we'll send you a reset link
                    <?php elseif ($step === 'reset'): ?>
                        Enter your new password below
                    <?php else: ?>
                        Your password has been successfully reset
                    <?php endif; ?>
                </p>
            </div>
            
            <div class="forgot-password-body">
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
                
                <?php if ($step === 'request'): ?>
                    <form method="POST" class="forgot-password-form">
                        <div class="form-group">
                            <label for="email" class="form-label">
                                <i class="fas fa-envelope"></i>
                                Email Address
                            </label>
                            <input type="email" 
                                   id="email" 
                                   name="email" 
                                   class="form-control" 
                                   value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>"
                                   required
                                   autocomplete="email"
                                   placeholder="Enter your registered email address">
                        </div>
                        
                        <button type="submit" class="btn btn-primary btn-lg w-100">
                            <i class="fas fa-paper-plane"></i>
                            Send Reset Link
                        </button>
                    </form>
                <?php elseif ($step === 'reset'): ?>
                    <form method="POST" class="forgot-password-form">
                        <input type="hidden" name="token" value="<?php echo htmlspecialchars($token); ?>">
                        
                        <div class="form-group">
                            <label for="new_password" class="form-label">
                                <i class="fas fa-lock"></i>
                                New Password
                            </label>
                            <div class="password-input-group">
                                <input type="password" 
                                       id="new_password" 
                                       name="new_password" 
                                       class="form-control" 
                                       required
                                       autocomplete="new-password">
                                <button type="button" class="password-toggle" onclick="togglePassword('new_password')">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>
                            <div id="password-strength" class="password-strength"></div>
                        </div>
                        
                        <div class="form-group">
                            <label for="confirm_password" class="form-label">
                                <i class="fas fa-lock"></i>
                                Confirm New Password
                            </label>
                            <div class="password-input-group">
                                <input type="password" 
                                       id="confirm_password" 
                                       name="confirm_password" 
                                       class="form-control" 
                                       required
                                       autocomplete="new-password">
                                <button type="button" class="password-toggle" onclick="togglePassword('confirm_password')">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>
                        </div>
                        
                        <div class="password-requirements">
                            <h6>Password Requirements:</h6>
                            <ul>
                                <li>At least 8 characters long</li>
                                <li>Contains uppercase and lowercase letters</li>
                                <li>Contains at least one number</li>
                                <li>Contains at least one special character</li>
                            </ul>
                        </div>
                        
                        <button type="submit" class="btn btn-primary btn-lg w-100">
                            <i class="fas fa-key"></i>
                            Reset Password
                        </button>
                    </form>
                <?php else: ?>
                    <div class="success-actions">
                        <a href="login.php" class="btn btn-primary btn-lg w-100">
                            <i class="fas fa-sign-in-alt"></i>
                            Sign In Now
                        </a>
                    </div>
                <?php endif; ?>
            </div>
            
            <div class="forgot-password-footer">
                <div class="back-to-login">
                    <a href="login.php" class="back-link">
                        <i class="fas fa-arrow-left"></i>
                        Back to Sign In
                    </a>
                </div>
                
                <?php if ($step === 'request'): ?>
                    <div class="help-text">
                        <p>Don't have an account? <a href="register.php">Create one here</a></p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        
        <div class="forgot-password-background">
            <div class="security-icons">
                <i class="fas fa-shield-alt"></i>
                <i class="fas fa-lock"></i>
                <i class="fas fa-key"></i>
                <i class="fas fa-user-shield"></i>
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
        
        // Add page specific styles
        document.head.insertAdjacentHTML('beforeend', `
            <style>
                .forgot-password-page {
                    background: linear-gradient(135deg, var(--navy-blue), var(--primary-green));
                    min-height: 100vh;
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    padding: 20px;
                    position: relative;
                    overflow: hidden;
                }
                
                .forgot-password-container {
                    display: flex;
                    max-width: 1200px;
                    width: 100%;
                    position: relative;
                    z-index: 2;
                }
                
                .forgot-password-card {
                    background: var(--white);
                    border-radius: 20px;
                    box-shadow: var(--shadow-lg);
                    overflow: hidden;
                    width: 100%;
                    max-width: 500px;
                    margin: 0 auto;
                    animation: fadeInUp 0.8s ease-out;
                }
                
                .forgot-password-header {
                    background: var(--gradient-primary);
                    color: var(--navy-blue);
                    padding: 2rem;
                    text-align: center;
                }
                
                .forgot-password-header .logo {
                    font-size: 2rem;
                    font-weight: bold;
                    margin-bottom: 1rem;
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    gap: 10px;
                }
                
                .forgot-password-header h2 {
                    margin-bottom: 0.5rem;
                    color: var(--navy-blue);
                }
                
                .forgot-password-header p {
                    opacity: 0.8;
                    margin: 0;
                }
                
                .forgot-password-body {
                    padding: 2rem;
                }
                
                .forgot-password-form .form-group {
                    margin-bottom: 1.5rem;
                }
                
                .forgot-password-form .form-label {
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
                
                .password-strength {
                    font-size: 0.8rem;
                    margin-top: 0.25rem;
                    font-weight: 500;
                }
                
                .password-requirements {
                    background: var(--light-gray);
                    padding: 1rem;
                    border-radius: 5px;
                    margin-bottom: 1.5rem;
                }
                
                .password-requirements h6 {
                    margin-bottom: 0.5rem;
                    color: var(--navy-blue);
                    font-size: 0.9rem;
                }
                
                .password-requirements ul {
                    margin: 0;
                    padding-left: 1.2rem;
                    font-size: 0.8rem;
                    color: var(--medium-gray);
                }
                
                .password-requirements li {
                    margin-bottom: 0.25rem;
                }
                
                .success-actions {
                    text-align: center;
                }
                
                .forgot-password-footer {
                    background: var(--light-gray);
                    padding: 1.5rem 2rem;
                    text-align: center;
                    border-top: 1px solid #e9ecef;
                }
                
                .back-to-login {
                    margin-bottom: 1rem;
                }
                
                .back-link {
                    color: var(--navy-blue);
                    text-decoration: none;
                    display: inline-flex;
                    align-items: center;
                    gap: 8px;
                    font-weight: 500;
                    transition: color var(--transition-fast);
                }
                
                .back-link:hover {
                    color: var(--primary-green);
                }
                
                .help-text {
                    color: var(--medium-gray);
                    font-size: 0.9rem;
                }
                
                .help-text a {
                    color: var(--navy-blue);
                    text-decoration: none;
                }
                
                .help-text a:hover {
                    color: var(--primary-green);
                }
                
                .forgot-password-background {
                    position: absolute;
                    top: 0;
                    left: 0;
                    right: 0;
                    bottom: 0;
                    z-index: 1;
                    opacity: 0.1;
                }
                
                .security-icons {
                    position: absolute;
                    top: 50%;
                    left: 50%;
                    transform: translate(-50%, -50%);
                    display: flex;
                    gap: 3rem;
                    font-size: 4rem;
                    color: var(--white);
                    animation: float 6s ease-in-out infinite;
                }
                
                .security-icons i {
                    animation: float 6s ease-in-out infinite;
                    animation-delay: calc(var(--i) * 0.5s);
                }
                
                @media (max-width: 768px) {
                    .forgot-password-container {
                        max-width: 100%;
                    }
                    
                    .forgot-password-card {
                        margin: 0;
                        border-radius: 0;
                        min-height: 100vh;
                        display: flex;
                        flex-direction: column;
                        justify-content: center;
                    }
                    
                    .forgot-password-header,
                    .forgot-password-body,
                    .forgot-password-footer {
                        padding: 1.5rem;
                    }
                    
                    .security-icons {
                        font-size: 2rem;
                        gap: 2rem;
                    }
                }
            </style>
        `);
    </script>
</body>
</html>