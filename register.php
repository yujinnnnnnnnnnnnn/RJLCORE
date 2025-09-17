<?php
/**
 * Customer Registration Page for Appliances Management System
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
$form_data = [];

// Handle registration form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $form_data = [
        'username' => sanitizeInput($_POST['username'] ?? ''),
        'email' => sanitizeInput($_POST['email'] ?? ''),
        'password' => $_POST['password'] ?? '',
        'confirm_password' => $_POST['confirm_password'] ?? '',
        'first_name' => sanitizeInput($_POST['first_name'] ?? ''),
        'last_name' => sanitizeInput($_POST['last_name'] ?? ''),
        'phone' => sanitizeInput($_POST['phone'] ?? ''),
        'address' => sanitizeInput($_POST['address'] ?? ''),
    ];
    
    // Basic validation
    if (empty($form_data['username']) || empty($form_data['email']) || 
        empty($form_data['password']) || empty($form_data['first_name']) || 
        empty($form_data['last_name'])) {
        $error = 'Please fill in all required fields.';
    } elseif ($form_data['password'] !== $form_data['confirm_password']) {
        $error = 'Passwords do not match.';
    } else {
        $auth = new Auth();
        $result = $auth->register($form_data);
        
        if ($result['success']) {
            $success = $result['message'];
            $form_data = []; // Clear form data on success
        } else {
            $error = $result['message'];
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - <?php echo APP_NAME; ?></title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body class="register-page">
    <div class="register-container">
        <div class="register-card">
            <div class="register-header">
                <div class="logo">
                    <i class="fas fa-plug"></i>
                    <span><?php echo APP_NAME; ?></span>
                </div>
                <h2>Create Account</h2>
                <p>Join us to manage your appliance purchases</p>
            </div>
            
            <div class="register-body">
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
                        <div class="mt-3">
                            <a href="login.php" class="btn btn-primary">
                                <i class="fas fa-sign-in-alt"></i>
                                Sign In Now
                            </a>
                        </div>
                    </div>
                <?php else: ?>
                    <form method="POST" class="register-form">
                        <div class="row">
                            <div class="col-6">
                                <div class="form-group">
                                    <label for="first_name" class="form-label">
                                        <i class="fas fa-user"></i>
                                        First Name *
                                    </label>
                                    <input type="text" 
                                           id="first_name" 
                                           name="first_name" 
                                           class="form-control" 
                                           value="<?php echo htmlspecialchars($form_data['first_name'] ?? ''); ?>"
                                           required>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="form-group">
                                    <label for="last_name" class="form-label">
                                        <i class="fas fa-user"></i>
                                        Last Name *
                                    </label>
                                    <input type="text" 
                                           id="last_name" 
                                           name="last_name" 
                                           class="form-control" 
                                           value="<?php echo htmlspecialchars($form_data['last_name'] ?? ''); ?>"
                                           required>
                                </div>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label for="username" class="form-label">
                                <i class="fas fa-at"></i>
                                Username *
                            </label>
                            <input type="text" 
                                   id="username" 
                                   name="username" 
                                   class="form-control" 
                                   value="<?php echo htmlspecialchars($form_data['username'] ?? ''); ?>"
                                   required
                                   autocomplete="username">
                            <small class="form-text">At least 3 characters, letters and numbers only</small>
                        </div>
                        
                        <div class="form-group">
                            <label for="email" class="form-label">
                                <i class="fas fa-envelope"></i>
                                Email Address *
                            </label>
                            <input type="email" 
                                   id="email" 
                                   name="email" 
                                   class="form-control" 
                                   value="<?php echo htmlspecialchars($form_data['email'] ?? ''); ?>"
                                   required
                                   autocomplete="email">
                        </div>
                        
                        <div class="form-group">
                            <label for="phone" class="form-label">
                                <i class="fas fa-phone"></i>
                                Phone Number
                            </label>
                            <input type="tel" 
                                   id="phone" 
                                   name="phone" 
                                   class="form-control" 
                                   value="<?php echo htmlspecialchars($form_data['phone'] ?? ''); ?>"
                                   autocomplete="tel">
                        </div>
                        
                        <div class="form-group">
                            <label for="address" class="form-label">
                                <i class="fas fa-map-marker-alt"></i>
                                Address
                            </label>
                            <textarea id="address" 
                                      name="address" 
                                      class="form-control" 
                                      rows="3"
                                      autocomplete="street-address"><?php echo htmlspecialchars($form_data['address'] ?? ''); ?></textarea>
                        </div>
                        
                        <div class="row">
                            <div class="col-6">
                                <div class="form-group">
                                    <label for="password" class="form-label">
                                        <i class="fas fa-lock"></i>
                                        Password *
                                    </label>
                                    <div class="password-input-group">
                                        <input type="password" 
                                               id="password" 
                                               name="password" 
                                               class="form-control" 
                                               required
                                               autocomplete="new-password">
                                        <button type="button" class="password-toggle" onclick="togglePassword('password')">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                    </div>
                                    <div id="password-strength" class="password-strength"></div>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="form-group">
                                    <label for="confirm_password" class="form-label">
                                        <i class="fas fa-lock"></i>
                                        Confirm Password *
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
                        
                        <div class="form-group form-check">
                            <input type="checkbox" id="terms" name="terms" class="form-check-input" required>
                            <label for="terms" class="form-check-label">
                                I agree to the <a href="#" class="terms-link">Terms of Service</a> and 
                                <a href="#" class="privacy-link">Privacy Policy</a>
                            </label>
                        </div>
                        
                        <button type="submit" class="btn btn-primary btn-lg w-100">
                            <i class="fas fa-user-plus"></i>
                            Create Account
                        </button>
                    </form>
                <?php endif; ?>
            </div>
            
            <div class="register-footer">
                <div class="signin-prompt">
                    <span>Already have an account?</span>
                    <a href="login.php" class="signin-link">Sign in here</a>
                </div>
            </div>
        </div>
        
        <div class="register-background">
            <div class="appliance-showcase">
                <div class="appliance-item">
                    <i class="fas fa-tv"></i>
                    <span>Smart TVs</span>
                </div>
                <div class="appliance-item">
                    <i class="fas fa-blender"></i>
                    <span>Kitchen Appliances</span>
                </div>
                <div class="appliance-item">
                    <i class="fas fa-wind"></i>
                    <span>Air Conditioners</span>
                </div>
                <div class="appliance-item">
                    <i class="fas fa-temperature-low"></i>
                    <span>Refrigerators</span>
                </div>
                <div class="appliance-item">
                    <i class="fas fa-washing-machine"></i>
                    <span>Washing Machines</span>
                </div>
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
        
        // Add register page specific styles
        document.head.insertAdjacentHTML('beforeend', `
            <style>
                .register-page {
                    background: linear-gradient(135deg, var(--navy-blue), var(--primary-green));
                    min-height: 100vh;
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    padding: 20px;
                    position: relative;
                    overflow: hidden;
                }
                
                .register-container {
                    display: flex;
                    max-width: 1200px;
                    width: 100%;
                    position: relative;
                    z-index: 2;
                }
                
                .register-card {
                    background: var(--white);
                    border-radius: 20px;
                    box-shadow: var(--shadow-lg);
                    overflow: hidden;
                    width: 100%;
                    max-width: 600px;
                    margin: 0 auto;
                    animation: fadeInUp 0.8s ease-out;
                    max-height: 90vh;
                    overflow-y: auto;
                }
                
                .register-header {
                    background: var(--gradient-primary);
                    color: var(--navy-blue);
                    padding: 2rem;
                    text-align: center;
                }
                
                .register-header .logo {
                    font-size: 2rem;
                    font-weight: bold;
                    margin-bottom: 1rem;
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    gap: 10px;
                }
                
                .register-header h2 {
                    margin-bottom: 0.5rem;
                    color: var(--navy-blue);
                }
                
                .register-header p {
                    opacity: 0.8;
                    margin: 0;
                }
                
                .register-body {
                    padding: 2rem;
                }
                
                .register-form .form-group {
                    margin-bottom: 1.5rem;
                }
                
                .register-form .form-label {
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
                
                .form-check {
                    display: flex;
                    align-items: flex-start;
                    gap: 8px;
                }
                
                .form-check-input {
                    width: auto;
                    margin: 0;
                    margin-top: 3px;
                }
                
                .form-check-label {
                    font-size: 0.9rem;
                    line-height: 1.4;
                }
                
                .terms-link, .privacy-link {
                    color: var(--navy-blue);
                    text-decoration: none;
                }
                
                .terms-link:hover, .privacy-link:hover {
                    color: var(--primary-green);
                }
                
                .form-text {
                    font-size: 0.8rem;
                    color: var(--medium-gray);
                    margin-top: 0.25rem;
                }
                
                .register-footer {
                    background: var(--light-gray);
                    padding: 1.5rem 2rem;
                    text-align: center;
                    border-top: 1px solid #e9ecef;
                }
                
                .signin-prompt {
                    color: var(--medium-gray);
                }
                
                .signin-link {
                    color: var(--navy-blue);
                    text-decoration: none;
                    font-weight: 500;
                    margin-left: 5px;
                }
                
                .signin-link:hover {
                    color: var(--primary-green);
                }
                
                .register-background {
                    position: absolute;
                    top: 0;
                    left: 0;
                    right: 0;
                    bottom: 0;
                    z-index: 1;
                    opacity: 0.1;
                }
                
                .appliance-showcase {
                    position: absolute;
                    top: 50%;
                    left: 50%;
                    transform: translate(-50%, -50%);
                    display: flex;
                    flex-direction: column;
                    gap: 2rem;
                    color: var(--white);
                }
                
                .appliance-item {
                    display: flex;
                    align-items: center;
                    gap: 1rem;
                    font-size: 1.5rem;
                    animation: float 6s ease-in-out infinite;
                    animation-delay: calc(var(--i) * 0.3s);
                }
                
                .appliance-item i {
                    font-size: 3rem;
                }
                
                @media (max-width: 768px) {
                    .register-container {
                        max-width: 100%;
                    }
                    
                    .register-card {
                        margin: 0;
                        border-radius: 0;
                        min-height: 100vh;
                        max-height: none;
                    }
                    
                    .register-header,
                    .register-body,
                    .register-footer {
                        padding: 1.5rem;
                    }
                    
                    .col-6 {
                        flex: 0 0 100%;
                        max-width: 100%;
                    }
                    
                    .appliance-showcase {
                        display: none;
                    }
                }
            </style>
        `);
    </script>
</body>
</html>