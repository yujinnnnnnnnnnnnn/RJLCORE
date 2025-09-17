<?php
require_once '../config/config.php';

class Auth {
    private $db;
    
    public function __construct() {
        $database = new Database();
        $this->db = $database->getConnection();
    }
    
    public function login($username, $password) {
        try {
            $query = "SELECT u.*, r.role_name FROM users u 
                     JOIN roles r ON u.role_id = r.id 
                     WHERE (u.username = :username OR u.email = :username) 
                     AND u.is_active = 1";
            
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':username', $username);
            $stmt->execute();
            
            $user = $stmt->fetch();
            
            if ($user && password_verify($password, $user['password_hash'])) {
                // Set session variables
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['email'] = $user['email'];
                $_SESSION['first_name'] = $user['first_name'];
                $_SESSION['last_name'] = $user['last_name'];
                $_SESSION['role_id'] = $user['role_id'];
                $_SESSION['role_name'] = $user['role_name'];
                $_SESSION['login_time'] = time();
                
                // Update last login time (optional)
                $this->updateLastLogin($user['id']);
                
                return [
                    'success' => true,
                    'message' => 'Login successful',
                    'user' => $user,
                    'redirect' => $this->getRedirectUrl($user['role_name'])
                ];
            } else {
                return [
                    'success' => false,
                    'message' => 'Invalid username or password'
                ];
            }
        } catch (PDOException $e) {
            return [
                'success' => false,
                'message' => 'Database error: ' . $e->getMessage()
            ];
        }
    }
    
    public function register($data) {
        try {
            // Validate required fields
            $required_fields = ['username', 'email', 'password', 'first_name', 'last_name'];
            foreach ($required_fields as $field) {
                if (empty($data[$field])) {
                    return [
                        'success' => false,
                        'message' => 'All required fields must be filled'
                    ];
                }
            }
            
            // Check if username or email already exists
            $query = "SELECT id FROM users WHERE username = :username OR email = :email";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':username', $data['username']);
            $stmt->bindParam(':email', $data['email']);
            $stmt->execute();
            
            if ($stmt->fetch()) {
                return [
                    'success' => false,
                    'message' => 'Username or email already exists'
                ];
            }
            
            // Validate password strength
            if (strlen($data['password']) < PASSWORD_MIN_LENGTH) {
                return [
                    'success' => false,
                    'message' => 'Password must be at least ' . PASSWORD_MIN_LENGTH . ' characters long'
                ];
            }
            
            // Hash password
            $password_hash = password_hash($data['password'], PASSWORD_DEFAULT);
            
            // Set default role (customer for self-registration)
            $role_id = isset($data['role_id']) ? $data['role_id'] : 3; // Customer role
            
            // Insert new user
            $query = "INSERT INTO users (username, email, password_hash, first_name, last_name, phone, address, role_id) 
                     VALUES (:username, :email, :password_hash, :first_name, :last_name, :phone, :address, :role_id)";
            
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':username', $data['username']);
            $stmt->bindParam(':email', $data['email']);
            $stmt->bindParam(':password_hash', $password_hash);
            $stmt->bindParam(':first_name', $data['first_name']);
            $stmt->bindParam(':last_name', $data['last_name']);
            $stmt->bindParam(':phone', $data['phone'] ?? null);
            $stmt->bindParam(':address', $data['address'] ?? null);
            $stmt->bindParam(':role_id', $role_id);
            
            if ($stmt->execute()) {
                return [
                    'success' => true,
                    'message' => 'Registration successful'
                ];
            } else {
                return [
                    'success' => false,
                    'message' => 'Registration failed'
                ];
            }
        } catch (PDOException $e) {
            return [
                'success' => false,
                'message' => 'Database error: ' . $e->getMessage()
            ];
        }
    }
    
    public function logout() {
        session_unset();
        session_destroy();
        return [
            'success' => true,
            'message' => 'Logged out successfully'
        ];
    }
    
    public function resetPasswordRequest($email) {
        try {
            // Check if email exists
            $query = "SELECT id FROM users WHERE email = :email AND is_active = 1";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':email', $email);
            $stmt->execute();
            
            $user = $stmt->fetch();
            
            if (!$user) {
                return [
                    'success' => false,
                    'message' => 'Email address not found'
                ];
            }
            
            // Generate reset token
            $reset_token = generate_token();
            $reset_expires = date('Y-m-d H:i:s', strtotime('+1 hour'));
            
            // Update user with reset token
            $query = "UPDATE users SET reset_token = :token, reset_expires = :expires WHERE id = :id";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':token', $reset_token);
            $stmt->bindParam(':expires', $reset_expires);
            $stmt->bindParam(':id', $user['id']);
            
            if ($stmt->execute()) {
                // Send email (implement your email sending logic here)
                $reset_link = BASE_URL . "reset_password.php?token=" . $reset_token;
                
                // For now, we'll just return the link (in production, send via email)
                return [
                    'success' => true,
                    'message' => 'Password reset instructions have been sent to your email',
                    'reset_link' => $reset_link // Remove this in production
                ];
            } else {
                return [
                    'success' => false,
                    'message' => 'Failed to generate reset token'
                ];
            }
        } catch (PDOException $e) {
            return [
                'success' => false,
                'message' => 'Database error: ' . $e->getMessage()
            ];
        }
    }
    
    public function resetPassword($token, $new_password) {
        try {
            // Validate token
            $query = "SELECT id FROM users WHERE reset_token = :token AND reset_expires > NOW() AND is_active = 1";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':token', $token);
            $stmt->execute();
            
            $user = $stmt->fetch();
            
            if (!$user) {
                return [
                    'success' => false,
                    'message' => 'Invalid or expired reset token'
                ];
            }
            
            // Validate new password
            if (strlen($new_password) < PASSWORD_MIN_LENGTH) {
                return [
                    'success' => false,
                    'message' => 'Password must be at least ' . PASSWORD_MIN_LENGTH . ' characters long'
                ];
            }
            
            // Hash new password
            $password_hash = password_hash($new_password, PASSWORD_DEFAULT);
            
            // Update password and clear reset token
            $query = "UPDATE users SET password_hash = :password, reset_token = NULL, reset_expires = NULL WHERE id = :id";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':password', $password_hash);
            $stmt->bindParam(':id', $user['id']);
            
            if ($stmt->execute()) {
                return [
                    'success' => true,
                    'message' => 'Password reset successfully'
                ];
            } else {
                return [
                    'success' => false,
                    'message' => 'Failed to reset password'
                ];
            }
        } catch (PDOException $e) {
            return [
                'success' => false,
                'message' => 'Database error: ' . $e->getMessage()
            ];
        }
    }
    
    public function changePassword($user_id, $current_password, $new_password) {
        try {
            // Get current password hash
            $query = "SELECT password_hash FROM users WHERE id = :id";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':id', $user_id);
            $stmt->execute();
            
            $user = $stmt->fetch();
            
            if (!$user || !password_verify($current_password, $user['password_hash'])) {
                return [
                    'success' => false,
                    'message' => 'Current password is incorrect'
                ];
            }
            
            // Validate new password
            if (strlen($new_password) < PASSWORD_MIN_LENGTH) {
                return [
                    'success' => false,
                    'message' => 'New password must be at least ' . PASSWORD_MIN_LENGTH . ' characters long'
                ];
            }
            
            // Hash new password
            $password_hash = password_hash($new_password, PASSWORD_DEFAULT);
            
            // Update password
            $query = "UPDATE users SET password_hash = :password WHERE id = :id";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':password', $password_hash);
            $stmt->bindParam(':id', $user_id);
            
            if ($stmt->execute()) {
                return [
                    'success' => true,
                    'message' => 'Password changed successfully'
                ];
            } else {
                return [
                    'success' => false,
                    'message' => 'Failed to change password'
                ];
            }
        } catch (PDOException $e) {
            return [
                'success' => false,
                'message' => 'Database error: ' . $e->getMessage()
            ];
        }
    }
    
    private function updateLastLogin($user_id) {
        try {
            $query = "UPDATE users SET updated_at = CURRENT_TIMESTAMP WHERE id = :id";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':id', $user_id);
            $stmt->execute();
        } catch (PDOException $e) {
            // Log error but don't fail the login process
            error_log("Failed to update last login: " . $e->getMessage());
        }
    }
    
    private function getRedirectUrl($role_name) {
        switch ($role_name) {
            case 'admin':
            case 'staff':
                return 'admin/dashboard.php';
            case 'customer':
                return 'customer/dashboard.php';
            default:
                return 'index.php';
        }
    }
    
    public function checkSessionTimeout() {
        if (isset($_SESSION['login_time'])) {
            if (time() - $_SESSION['login_time'] > SESSION_TIMEOUT) {
                $this->logout();
                return false;
            }
            // Update session time
            $_SESSION['login_time'] = time();
        }
        return true;
    }
}
?>