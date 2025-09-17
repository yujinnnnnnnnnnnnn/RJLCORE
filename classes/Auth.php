<?php
/**
 * Authentication Class for Appliances Management System
 * Handles login, registration, password reset, and role-based access
 */

require_once __DIR__ . '/../config/config.php';

class Auth {
    private $db;
    
    public function __construct() {
        $database = new Database();
        $this->db = $database->getConnection();
    }
    
    /**
     * Login user with username/email and password
     */
    public function login($username, $password, $remember_me = false) {
        try {
            // Check if login attempt is blocked due to too many failed attempts
            if ($this->isLoginBlocked($username)) {
                return [
                    'success' => false,
                    'message' => 'Too many failed login attempts. Please try again in 15 minutes.'
                ];
            }
            
            // Find user by username or email
            $sql = "SELECT * FROM users WHERE (username = ? OR email = ?) AND status = 'active'";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$username, $username]);
            $user = $stmt->fetch();
            
            if (!$user) {
                $this->recordFailedLogin($username);
                return [
                    'success' => false,
                    'message' => 'Invalid username/email or password.'
                ];
            }
            
            // Verify password
            if (!password_verify($password, $user['password_hash'])) {
                $this->recordFailedLogin($username);
                return [
                    'success' => false,
                    'message' => 'Invalid username/email or password.'
                ];
            }
            
            // Clear failed login attempts
            $this->clearFailedLogins($username);
            
            // Create session
            $this->createSession($user);
            
            // Update last login
            $this->updateLastLogin($user['user_id']);
            
            // Log successful login
            logAudit('LOGIN', 'users', $user['user_id']);
            
            // Handle remember me
            if ($remember_me) {
                $this->createRememberToken($user['user_id']);
            }
            
            return [
                'success' => true,
                'message' => 'Login successful.',
                'user' => $user,
                'redirect' => $this->getRedirectUrl($user['role'])
            ];
            
        } catch (Exception $e) {
            error_log("Login error: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'An error occurred during login. Please try again.'
            ];
        }
    }
    
    /**
     * Register new customer
     */
    public function register($data) {
        try {
            // Validate input
            $validation = $this->validateRegistrationData($data);
            if (!$validation['valid']) {
                return [
                    'success' => false,
                    'message' => implode(' ', $validation['errors'])
                ];
            }
            
            // Check if username or email already exists
            if ($this->userExists($data['username'], $data['email'])) {
                return [
                    'success' => false,
                    'message' => 'Username or email already exists.'
                ];
            }
            
            // Hash password
            $password_hash = password_hash($data['password'], PASSWORD_DEFAULT);
            
            // Insert user
            $sql = "INSERT INTO users (username, email, password_hash, first_name, last_name, phone, address, role) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, 'customer')";
            
            $stmt = $this->db->prepare($sql);
            $result = $stmt->execute([
                $data['username'],
                $data['email'],
                $password_hash,
                $data['first_name'],
                $data['last_name'],
                $data['phone'] ?? null,
                $data['address'] ?? null
            ]);
            
            if ($result) {
                $user_id = $this->db->lastInsertId();
                logAudit('REGISTER', 'users', $user_id);
                
                return [
                    'success' => true,
                    'message' => 'Registration successful. You can now log in.',
                    'user_id' => $user_id
                ];
            } else {
                return [
                    'success' => false,
                    'message' => 'Registration failed. Please try again.'
                ];
            }
            
        } catch (Exception $e) {
            error_log("Registration error: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'An error occurred during registration. Please try again.'
            ];
        }
    }
    
    /**
     * Create admin or staff user (admin only)
     */
    public function createUser($data, $created_by_role) {
        try {
            // Only admin can create users
            if ($created_by_role !== 'admin') {
                return [
                    'success' => false,
                    'message' => 'Unauthorized access.'
                ];
            }
            
            // Validate input
            $validation = $this->validateUserData($data);
            if (!$validation['valid']) {
                return [
                    'success' => false,
                    'message' => implode(' ', $validation['errors'])
                ];
            }
            
            // Check if username or email already exists
            if ($this->userExists($data['username'], $data['email'])) {
                return [
                    'success' => false,
                    'message' => 'Username or email already exists.'
                ];
            }
            
            // Generate temporary password
            $temp_password = $this->generateTemporaryPassword();
            $password_hash = password_hash($temp_password, PASSWORD_DEFAULT);
            
            // Insert user
            $sql = "INSERT INTO users (username, email, password_hash, first_name, last_name, phone, address, role) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
            
            $stmt = $this->db->prepare($sql);
            $result = $stmt->execute([
                $data['username'],
                $data['email'],
                $password_hash,
                $data['first_name'],
                $data['last_name'],
                $data['phone'] ?? null,
                $data['address'] ?? null,
                $data['role']
            ]);
            
            if ($result) {
                $user_id = $this->db->lastInsertId();
                logAudit('CREATE_USER', 'users', $user_id);
                
                // TODO: Send email with temporary password
                
                return [
                    'success' => true,
                    'message' => 'User created successfully.',
                    'user_id' => $user_id,
                    'temp_password' => $temp_password
                ];
            } else {
                return [
                    'success' => false,
                    'message' => 'Failed to create user. Please try again.'
                ];
            }
            
        } catch (Exception $e) {
            error_log("Create user error: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'An error occurred while creating user. Please try again.'
            ];
        }
    }
    
    /**
     * Change password
     */
    public function changePassword($user_id, $current_password, $new_password) {
        try {
            // Get current user
            $sql = "SELECT password_hash FROM users WHERE user_id = ?";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$user_id]);
            $user = $stmt->fetch();
            
            if (!$user) {
                return [
                    'success' => false,
                    'message' => 'User not found.'
                ];
            }
            
            // Verify current password
            if (!password_verify($current_password, $user['password_hash'])) {
                return [
                    'success' => false,
                    'message' => 'Current password is incorrect.'
                ];
            }
            
            // Validate new password
            if (!$this->validatePassword($new_password)) {
                return [
                    'success' => false,
                    'message' => 'New password does not meet requirements.'
                ];
            }
            
            // Update password
            $new_password_hash = password_hash($new_password, PASSWORD_DEFAULT);
            $sql = "UPDATE users SET password_hash = ?, updated_at = CURRENT_TIMESTAMP WHERE user_id = ?";
            $stmt = $this->db->prepare($sql);
            $result = $stmt->execute([$new_password_hash, $user_id]);
            
            if ($result) {
                logAudit('CHANGE_PASSWORD', 'users', $user_id);
                return [
                    'success' => true,
                    'message' => 'Password changed successfully.'
                ];
            } else {
                return [
                    'success' => false,
                    'message' => 'Failed to change password. Please try again.'
                ];
            }
            
        } catch (Exception $e) {
            error_log("Change password error: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'An error occurred while changing password. Please try again.'
            ];
        }
    }
    
    /**
     * Request password reset
     */
    public function requestPasswordReset($email) {
        try {
            // Find user by email
            $sql = "SELECT user_id, email, first_name FROM users WHERE email = ? AND status = 'active'";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$email]);
            $user = $stmt->fetch();
            
            if (!$user) {
                // Don't reveal if email exists or not
                return [
                    'success' => true,
                    'message' => 'If the email exists, a reset link has been sent.'
                ];
            }
            
            // Generate reset token
            $token = generateToken();
            $expires = date('Y-m-d H:i:s', strtotime('+1 hour'));
            
            // Save reset token
            $sql = "UPDATE users SET reset_token = ?, reset_token_expires = ? WHERE user_id = ?";
            $stmt = $this->db->prepare($sql);
            $result = $stmt->execute([$token, $expires, $user['user_id']]);
            
            if ($result) {
                // TODO: Send reset email
                logAudit('REQUEST_PASSWORD_RESET', 'users', $user['user_id']);
                
                return [
                    'success' => true,
                    'message' => 'If the email exists, a reset link has been sent.',
                    'reset_token' => $token // For development/testing only
                ];
            } else {
                return [
                    'success' => false,
                    'message' => 'Failed to process password reset request.'
                ];
            }
            
        } catch (Exception $e) {
            error_log("Password reset request error: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'An error occurred while processing your request.'
            ];
        }
    }
    
    /**
     * Reset password with token
     */
    public function resetPassword($token, $new_password) {
        try {
            // Find user with valid token
            $sql = "SELECT user_id FROM users WHERE reset_token = ? AND reset_token_expires > NOW() AND status = 'active'";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$token]);
            $user = $stmt->fetch();
            
            if (!$user) {
                return [
                    'success' => false,
                    'message' => 'Invalid or expired reset token.'
                ];
            }
            
            // Validate new password
            if (!$this->validatePassword($new_password)) {
                return [
                    'success' => false,
                    'message' => 'Password does not meet requirements.'
                ];
            }
            
            // Update password and clear reset token
            $password_hash = password_hash($new_password, PASSWORD_DEFAULT);
            $sql = "UPDATE users SET password_hash = ?, reset_token = NULL, reset_token_expires = NULL, updated_at = CURRENT_TIMESTAMP WHERE user_id = ?";
            $stmt = $this->db->prepare($sql);
            $result = $stmt->execute([$password_hash, $user['user_id']]);
            
            if ($result) {
                logAudit('RESET_PASSWORD', 'users', $user['user_id']);
                return [
                    'success' => true,
                    'message' => 'Password reset successfully. You can now log in.'
                ];
            } else {
                return [
                    'success' => false,
                    'message' => 'Failed to reset password. Please try again.'
                ];
            }
            
        } catch (Exception $e) {
            error_log("Password reset error: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'An error occurred while resetting password.'
            ];
        }
    }
    
    /**
     * Logout user
     */
    public function logout() {
        if (isLoggedIn()) {
            $user_id = $_SESSION['user_id'];
            logAudit('LOGOUT', 'users', $user_id);
        }
        
        // Clear remember me cookie if exists
        if (isset($_COOKIE['remember_token'])) {
            setcookie('remember_token', '', time() - 3600, '/');
            // TODO: Remove remember token from database
        }
        
        // Destroy session
        session_destroy();
        return true;
    }
    
    /**
     * Check if user has required role
     */
    public function hasRole($required_role, $user_role = null) {
        if (!$user_role) {
            $user = getCurrentUser();
            if (!$user) return false;
            $user_role = $user['role'];
        }
        
        // Define role hierarchy
        $roles = ['customer' => 1, 'staff' => 2, 'admin' => 3];
        
        return isset($roles[$user_role]) && 
               isset($roles[$required_role]) && 
               $roles[$user_role] >= $roles[$required_role];
    }
    
    /**
     * Check if user has any of the specified roles
     */
    public function hasAnyRole($required_roles, $user_role = null) {
        foreach ($required_roles as $role) {
            if ($this->hasRole($role, $user_role)) {
                return true;
            }
        }
        return false;
    }
    
    // Private helper methods
    
    private function createSession($user) {
        $_SESSION['user_id'] = $user['user_id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['email'] = $user['email'];
        $_SESSION['first_name'] = $user['first_name'];
        $_SESSION['last_name'] = $user['last_name'];
        $_SESSION['role'] = $user['role'];
        $_SESSION['last_activity'] = time();
        
        // Regenerate session ID for security
        session_regenerate_id(true);
    }
    
    private function validateRegistrationData($data) {
        $errors = [];
        
        if (empty($data['username']) || strlen($data['username']) < 3) {
            $errors[] = 'Username must be at least 3 characters long.';
        }
        
        if (empty($data['email']) || !filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Valid email address is required.';
        }
        
        if (empty($data['password']) || !$this->validatePassword($data['password'])) {
            $errors[] = 'Password must be at least 8 characters long with uppercase, lowercase, number, and special character.';
        }
        
        if (empty($data['first_name']) || strlen($data['first_name']) < 2) {
            $errors[] = 'First name must be at least 2 characters long.';
        }
        
        if (empty($data['last_name']) || strlen($data['last_name']) < 2) {
            $errors[] = 'Last name must be at least 2 characters long.';
        }
        
        return ['valid' => empty($errors), 'errors' => $errors];
    }
    
    private function validateUserData($data) {
        $errors = [];
        
        if (empty($data['username']) || strlen($data['username']) < 3) {
            $errors[] = 'Username must be at least 3 characters long.';
        }
        
        if (empty($data['email']) || !filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Valid email address is required.';
        }
        
        if (empty($data['first_name']) || strlen($data['first_name']) < 2) {
            $errors[] = 'First name must be at least 2 characters long.';
        }
        
        if (empty($data['last_name']) || strlen($data['last_name']) < 2) {
            $errors[] = 'Last name must be at least 2 characters long.';
        }
        
        if (!in_array($data['role'], ['admin', 'staff'])) {
            $errors[] = 'Invalid role specified.';
        }
        
        return ['valid' => empty($errors), 'errors' => $errors];
    }
    
    private function validatePassword($password) {
        if (strlen($password) < PASSWORD_MIN_LENGTH) return false;
        if (!preg_match('/[a-z]/', $password)) return false; // lowercase
        if (!preg_match('/[A-Z]/', $password)) return false; // uppercase
        if (!preg_match('/\d/', $password)) return false; // number
        if (!preg_match('/[^A-Za-z0-9]/', $password)) return false; // special char
        
        return true;
    }
    
    private function userExists($username, $email) {
        $sql = "SELECT COUNT(*) FROM users WHERE username = ? OR email = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$username, $email]);
        return $stmt->fetchColumn() > 0;
    }
    
    private function isLoginBlocked($username) {
        // TODO: Implement login attempt tracking
        return false;
    }
    
    private function recordFailedLogin($username) {
        // TODO: Implement failed login tracking
    }
    
    private function clearFailedLogins($username) {
        // TODO: Clear failed login attempts
    }
    
    private function updateLastLogin($user_id) {
        $sql = "UPDATE users SET last_login = CURRENT_TIMESTAMP WHERE user_id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$user_id]);
    }
    
    private function createRememberToken($user_id) {
        // TODO: Implement remember me functionality
    }
    
    private function getRedirectUrl($role) {
        switch ($role) {
            case 'admin':
            case 'staff':
                return '/admin/dashboard.php';
            case 'customer':
                return '/customer/dashboard.php';
            default:
                return '/index.php';
        }
    }
    
    private function generateTemporaryPassword($length = 12) {
        $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%^&*';
        return substr(str_shuffle($chars), 0, $length);
    }
}
?>