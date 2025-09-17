# Installation Guide - Appliance Management System

## Quick Start Guide

Follow these steps to get your Appliance Management System up and running quickly.

### Prerequisites

1. **XAMPP** (or similar LAMP/WAMP stack)
   - Download from: https://www.apachefriends.org/
   - Includes Apache, MySQL, PHP 7.4+

2. **Web Browser** (Chrome, Firefox, Safari, Edge)

### Step 1: Install XAMPP

1. Download and install XAMPP for your operating system
2. Start XAMPP Control Panel
3. Start **Apache** and **MySQL** services
4. Verify installation by visiting `http://localhost` in your browser

### Step 2: Deploy Application Files

1. **Copy project files** to your XAMPP htdocs directory:
   ```
   Windows: C:\xampp\htdocs\appliance_store\
   Mac: /Applications/XAMPP/htdocs/appliance_store/
   Linux: /opt/lampp/htdocs/appliance_store/
   ```

2. **Set folder permissions** (Linux/Mac only):
   ```bash
   chmod 755 appliance_store/
   chmod 777 appliance_store/uploads/
   chmod 666 appliance_store/config/
   ```

### Step 3: Run Setup Wizard

1. Open your web browser
2. Navigate to: `http://localhost/appliance_store/setup.php`
3. Follow the setup wizard:

#### Database Configuration
- **Host**: `localhost` (default)
- **Database Name**: `appliance_store` (or your preferred name)
- **Username**: `root` (XAMPP default)
- **Password**: Leave empty (XAMPP default)

#### Admin Account Creation
- Create your administrator account
- Use strong credentials for security

### Step 4: Access Your Application

After setup completion:

1. **Home Page**: `http://localhost/appliance_store/`
2. **Admin Portal**: `http://localhost/appliance_store/login.php`
3. **Customer Portal**: `http://localhost/appliance_store/customer_login.php`

## Manual Installation (Alternative)

If you prefer manual installation:

### 1. Database Setup

1. Open phpMyAdmin: `http://localhost/phpmyadmin`
2. Create new database named `appliance_store`
3. Import the SQL file: `database/appliance_store.sql`
4. Verify tables are created successfully

### 2. Configuration

1. Update `config/database.php` with your database credentials:
   ```php
   private $host = "localhost";
   private $db_name = "appliance_store";
   private $username = "root";
   private $password = "";
   ```

2. Update `config/config.php` for your environment:
   ```php
   define('BASE_URL', 'http://localhost/appliance_store/');
   ```

### 3. Default Admin Account

The SQL file includes a default admin account:
- **Username**: `admin`
- **Password**: `admin123`
- **Email**: `admin@appliancestore.com`

**⚠️ Important**: Change the default password after first login!

## Directory Structure

After installation, your directory should look like this:

```
appliance_store/
├── admin/                  # Admin/Staff portal
├── customer/              # Customer portal  
├── assets/               # CSS, JS, images
├── classes/              # PHP classes
├── config/               # Configuration files
├── database/             # SQL schema
├── uploads/              # File uploads (auto-created)
├── index.php            # Home page
├── setup.php            # Setup wizard
└── README.md            # Documentation
```

## Troubleshooting

### Common Issues

1. **"Access Denied" Database Error**
   - Check MySQL service is running in XAMPP
   - Verify database credentials in `config/database.php`
   - Ensure database exists and is accessible

2. **"Page Not Found" Errors**
   - Verify Apache service is running
   - Check file paths are correct
   - Ensure .htaccess file is present

3. **Permission Errors**
   - Set proper folder permissions (Linux/Mac)
   - Ensure uploads directory is writable
   - Check PHP file execution permissions

4. **PHP Errors**
   - Verify PHP 7.4+ is installed
   - Check PHP extensions: PDO, MySQL
   - Review error logs in XAMPP control panel

### Verification Steps

1. **Test Database Connection**:
   ```
   Visit: http://localhost/appliance_store/setup.php
   ```

2. **Check Apache/PHP**:
   ```
   Visit: http://localhost/appliance_store/
   ```

3. **Verify File Permissions**:
   - Uploads directory should be writable
   - Config files should be readable
   - PHP files should be executable

## Security Considerations

### Production Deployment

When deploying to production:

1. **Change Default Credentials**
   - Update admin password
   - Use strong, unique passwords

2. **Database Security**
   - Create dedicated database user
   - Use strong database password
   - Limit database user permissions

3. **File Permissions**
   - Set restrictive file permissions
   - Protect config files from web access
   - Secure uploads directory

4. **HTTPS Configuration**
   - Use SSL certificate
   - Update BASE_URL to https://
   - Force HTTPS redirects

### Recommended Settings

```php
// config/config.php - Production Settings
define('BASE_URL', 'https://yourdomain.com/');
define('PASSWORD_MIN_LENGTH', 8);
define('SESSION_TIMEOUT', 1800); // 30 minutes

// Enable error logging, disable display
ini_set('display_errors', 0);
ini_set('log_errors', 1);
```

## Performance Optimization

### Recommended PHP Settings

```ini
; php.ini optimizations
memory_limit = 256M
upload_max_filesize = 10M
post_max_size = 10M
max_execution_time = 300
```

### Database Optimization

1. **Regular Maintenance**
   - Backup database regularly
   - Optimize tables monthly
   - Monitor slow queries

2. **Indexing**
   - Database includes proper indexes
   - Monitor query performance
   - Add indexes as needed

## Support

### Getting Help

1. **Check Documentation**
   - Read README.md for features
   - Review code comments
   - Check configuration files

2. **Common Solutions**
   - Restart XAMPP services
   - Clear browser cache
   - Check error logs

3. **System Requirements**
   - PHP 7.4 or higher
   - MySQL 5.7 or higher
   - Apache 2.4 or higher
   - Modern web browser

### Maintenance Tasks

1. **Regular Backups**
   - Database backup weekly
   - File system backup monthly
   - Test restore procedures

2. **Updates**
   - Monitor for security updates
   - Keep XAMPP updated
   - Review application logs

3. **Monitoring**
   - Check disk space
   - Monitor database size
   - Review error logs

---

**Need Help?** 
- Check the troubleshooting section above
- Review error logs in XAMPP control panel
- Ensure all prerequisites are met

**Version**: 1.0.0  
**Last Updated**: 2024