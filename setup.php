<?php
/**
 * Setup Script for Appliance Management System
 * This script helps set up the application for first use
 */

// Check if already set up
if (file_exists('config/.setup_complete')) {
    die('Application is already set up. Delete config/.setup_complete to run setup again.');
}

$error = '';
$success = '';
$step = $_GET['step'] ?? 1;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($step == 1) {
        // Database connection test
        $host = $_POST['host'];
        $dbname = $_POST['dbname'];
        $username = $_POST['username'];
        $password = $_POST['password'];
        
        try {
            $pdo = new PDO("mysql:host=$host", $username, $password);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            
            // Create database if it doesn't exist
            $pdo->exec("CREATE DATABASE IF NOT EXISTS `$dbname`");
            $pdo->exec("USE `$dbname`");
            
            // Update database config
            $config = file_get_contents('config/database.php');
            $config = str_replace('localhost', $host, $config);
            $config = str_replace('appliance_store', $dbname, $config);
            $config = str_replace('"root"', '"' . $username . '"', $config);
            $config = str_replace('""', '"' . $password . '"', $config);
            file_put_contents('config/database.php', $config);
            
            $success = 'Database connection successful!';
            $step = 2;
        } catch (PDOException $e) {
            $error = 'Database connection failed: ' . $e->getMessage();
        }
    } elseif ($step == 2) {
        // Import database schema
        try {
            require_once 'config/database.php';
            $database = new Database();
            $db = $database->getConnection();
            
            $sql = file_get_contents('database/appliance_store.sql');
            $db->exec($sql);
            
            $success = 'Database schema imported successfully!';
            $step = 3;
        } catch (Exception $e) {
            $error = 'Failed to import database schema: ' . $e->getMessage();
        }
    } elseif ($step == 3) {
        // Create admin account
        try {
            require_once 'config/config.php';
            require_once 'classes/Auth.php';
            
            $auth = new Auth();
            $result = $auth->register([
                'username' => $_POST['admin_username'],
                'email' => $_POST['admin_email'],
                'password' => $_POST['admin_password'],
                'first_name' => $_POST['admin_first_name'],
                'last_name' => $_POST['admin_last_name'],
                'role_id' => 1 // Admin role
            ]);
            
            if ($result['success']) {
                // Mark setup as complete
                file_put_contents('config/.setup_complete', date('Y-m-d H:i:s'));
                
                // Create uploads directory
                if (!file_exists('uploads')) {
                    mkdir('uploads', 0755, true);
                }
                if (!file_exists('uploads/products')) {
                    mkdir('uploads/products', 0755, true);
                }
                
                $success = 'Setup completed successfully! You can now login with your admin account.';
                $step = 4;
            } else {
                $error = $result['message'];
            }
        } catch (Exception $e) {
            $error = 'Failed to create admin account: ' . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Setup - Appliance Management System</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body style="background: linear-gradient(135deg, var(--navy) 0%, var(--green) 100%); min-height: 100vh; padding: 2rem 0;">
    
    <div class="container" style="max-width: 600px;">
        <div class="card">
            <div class="text-center mb-4">
                <div style="background: linear-gradient(135deg, var(--green), var(--navy)); color: white; width: 80px; height: 80px; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 1rem; font-size: 2rem;">
                    <i class="fas fa-cogs"></i>
                </div>
                <h1>Appliance Management System</h1>
                <h2>Setup Wizard</h2>
            </div>

            <!-- Progress Bar -->
            <div style="margin-bottom: 2rem;">
                <div style="display: flex; justify-content: space-between; margin-bottom: 0.5rem;">
                    <span class="<?php echo $step >= 1 ? 'text-success' : 'text-gray'; ?>">Database</span>
                    <span class="<?php echo $step >= 2 ? 'text-success' : 'text-gray'; ?>">Schema</span>
                    <span class="<?php echo $step >= 3 ? 'text-success' : 'text-gray'; ?>">Admin</span>
                    <span class="<?php echo $step >= 4 ? 'text-success' : 'text-gray'; ?>">Complete</span>
                </div>
                <div style="background: #eee; height: 4px; border-radius: 2px;">
                    <div style="background: var(--green); height: 100%; width: <?php echo ($step / 4) * 100; ?>%; border-radius: 2px; transition: width 0.3s ease;"></div>
                </div>
            </div>

            <?php if ($error): ?>
                <div class="notification error">
                    <i class="fas fa-exclamation-circle"></i>
                    <?php echo $error; ?>
                </div>
            <?php endif; ?>

            <?php if ($success): ?>
                <div class="notification success">
                    <i class="fas fa-check-circle"></i>
                    <?php echo $success; ?>
                </div>
            <?php endif; ?>

            <?php if ($step == 1): ?>
                <h3>Step 1: Database Configuration</h3>
                <p>Please enter your MySQL database connection details.</p>
                
                <form method="POST">
                    <div class="form-group">
                        <label class="form-label">Database Host</label>
                        <input type="text" name="host" class="form-control" value="localhost" required>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Database Name</label>
                        <input type="text" name="dbname" class="form-control" value="appliance_store" required>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Username</label>
                        <input type="text" name="username" class="form-control" value="root" required>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Password</label>
                        <input type="password" name="password" class="form-control">
                    </div>
                    
                    <button type="submit" class="btn btn-primary">Test Connection & Continue</button>
                </form>

            <?php elseif ($step == 2): ?>
                <h3>Step 2: Database Schema</h3>
                <p>The database connection was successful. Now let's import the database schema.</p>
                
                <form method="POST">
                    <input type="hidden" name="step" value="2">
                    <button type="submit" class="btn btn-primary">Import Database Schema</button>
                </form>

            <?php elseif ($step == 3): ?>
                <h3>Step 3: Create Admin Account</h3>
                <p>Create your administrator account to manage the system.</p>
                
                <form method="POST">
                    <input type="hidden" name="step" value="3">
                    
                    <div class="row">
                        <div class="col-6">
                            <div class="form-group">
                                <label class="form-label">First Name</label>
                                <input type="text" name="admin_first_name" class="form-control" required>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="form-group">
                                <label class="form-label">Last Name</label>
                                <input type="text" name="admin_last_name" class="form-control" required>
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Username</label>
                        <input type="text" name="admin_username" class="form-control" value="admin" required>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Email</label>
                        <input type="email" name="admin_email" class="form-control" required>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Password</label>
                        <input type="password" name="admin_password" class="form-control" minlength="6" required>
                    </div>
                    
                    <button type="submit" class="btn btn-primary">Create Admin Account</button>
                </form>

            <?php elseif ($step == 4): ?>
                <h3>Setup Complete!</h3>
                <p>Your Appliance Management System has been set up successfully.</p>
                
                <div style="background: var(--light-gray); padding: 1.5rem; border-radius: 8px; margin: 2rem 0;">
                    <h4>What's Next?</h4>
                    <ul>
                        <li>Login to the admin panel to add products</li>
                        <li>Configure your store settings</li>
                        <li>Add staff accounts if needed</li>
                        <li>Start managing your appliance business!</li>
                    </ul>
                </div>
                
                <div class="text-center">
                    <a href="login.php" class="btn btn-primary btn-lg" style="margin-right: 1rem;">
                        <i class="fas fa-sign-in-alt"></i> Admin Login
                    </a>
                    <a href="index.php" class="btn btn-secondary btn-lg">
                        <i class="fas fa-home"></i> Home Page
                    </a>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <style>
        .text-success { color: var(--success) !important; }
        .text-gray { color: var(--gray) !important; }
    </style>
</body>
</html>